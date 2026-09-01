<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/CIWSScoreCalculator.php';
require_once __DIR__ . '/../includes/CIWSScoringMarkupParser.php';

const CIWS_BACKUP_TABLE = 'quiz_ciws_formula_backup';

function usage(): void
{
    echo <<<TEXT
Usage:
  php scripts/ciws_score_backfill.php [--report=/path/report.json]
  php scripts/ciws_score_backfill.php --apply --batch-id=YYYYMMDD-description [--report=/path/report.json]
  php scripts/ciws_score_backfill.php --rollback-batch=BATCH_ID --apply

The default mode is read-only. --apply is required for either a backfill or rollback.
Backfill apply aborts and rolls back if any scored row cannot be reconstructed safely.

TEXT;
}

function fail(string $message, int $code = 1): void
{
    fwrite(STDERR, $message . PHP_EOL);
    exit($code);
}

function validBatchId(string $batchId): bool
{
    return preg_match('/^[A-Za-z0-9][A-Za-z0-9._-]{0,63}$/', $batchId) === 1;
}

/** @param array<string,mixed> $row */
function tokenCountsFromRow(array $row): ?array
{
    $columns = [
        'correct' => 'Q_TOKEN_CORRECT',
        'word' => 'Q_TOKEN_WORD',
        'inaccurate' => 'Q_TOKEN_SEN_INACC',
        'overlap' => 'Q_TOKEN_SEN_OVERLAP',
        'nmae' => 'Q_TOKEN_SEN_NMAE',
    ];
    $present = array_filter($columns, static fn(string $column): bool => $row[$column] !== null);

    if (count($present) === 0) {
        return null;
    }
    if (count($present) !== count($columns)) {
        throw new UnexpectedValueException('Token columns are only partially populated.');
    }

    $counts = [];
    foreach ($columns as $name => $column) {
        if (!is_numeric($row[$column]) || (int) $row[$column] < 0) {
            throw new UnexpectedValueException($column . ' is not a non-negative integer.');
        }
        $counts[$name] = (int) $row[$column];
    }

    return $counts;
}

/** @param mixed $old @param int|float|null $new */
function scoreChanged($old, $new): bool
{
    if ($old === null || $new === null) {
        return $old !== $new;
    }
    return abs((float) $old - (float) $new) > 0.0000001;
}

function ensureBackupTable(PDO $db): void
{
    $db->exec(
        'CREATE TABLE IF NOT EXISTS ' . CIWS_BACKUP_TABLE . ' (
            BACKUP_BATCH_ID varchar(64) NOT NULL,
            Q_ID int(11) NOT NULL,
            Q_SENTENCE_ERROR int(11) DEFAULT NULL,
            Q_SENTENCE_ACCURACY decimal(10,3) DEFAULT NULL,
            Q_CIWS int(11) DEFAULT NULL,
            Q_TOKEN_CORRECT int(11) UNSIGNED DEFAULT NULL,
            Q_TOKEN_WORD int(11) UNSIGNED DEFAULT NULL,
            Q_TOKEN_SEN_INACC int(11) UNSIGNED DEFAULT NULL,
            Q_TOKEN_SEN_OVERLAP int(11) UNSIGNED DEFAULT NULL,
            Q_TOKEN_SEN_NMAE int(11) UNSIGNED DEFAULT NULL,
            APPLIED_Q_SENTENCE_ERROR int(11) DEFAULT NULL,
            APPLIED_Q_SENTENCE_ACCURACY decimal(10,3) DEFAULT NULL,
            APPLIED_Q_CIWS int(11) DEFAULT NULL,
            APPLIED_Q_TOKEN_CORRECT int(11) UNSIGNED DEFAULT NULL,
            APPLIED_Q_TOKEN_WORD int(11) UNSIGNED DEFAULT NULL,
            APPLIED_Q_TOKEN_SEN_INACC int(11) UNSIGNED DEFAULT NULL,
            APPLIED_Q_TOKEN_SEN_OVERLAP int(11) UNSIGNED DEFAULT NULL,
            APPLIED_Q_TOKEN_SEN_NMAE int(11) UNSIGNED DEFAULT NULL,
            BACKFILL_SOURCE varchar(16) NOT NULL,
            BACKED_UP_AT datetime NOT NULL,
            PRIMARY KEY (BACKUP_BATCH_ID, Q_ID)
        ) ENGINE=InnoDB'
    );
}

/** @return array{report:array<string,mixed>,updates:array<int,array<string,mixed>>} */
function assessRows(PDO $db, bool $lockRows): array
{
    $sql = 'SELECT
                Q_ID, Q_WORD_ERROR, Q_SENTENCE_ERROR, Q_SENTENCE_ACCURACY, Q_CIWS, Q_SCORING,
                Q_TOKEN_CORRECT, Q_TOKEN_WORD, Q_TOKEN_SEN_INACC,
                Q_TOKEN_SEN_OVERLAP, Q_TOKEN_SEN_NMAE
            FROM quiz
            WHERE Q_ID > :last_id
              AND (Q_SENTENCE_ERROR IS NOT NULL
               OR Q_SENTENCE_ACCURACY IS NOT NULL
               OR Q_CIWS IS NOT NULL)
            ORDER BY Q_ID
            LIMIT 100';
    if ($lockRows) {
        $sql .= ' FOR UPDATE';
    }

    $report = [
        'mode' => $lockRows ? 'apply-assessment' : 'dry-run',
        'rows_scanned' => 0,
        'token_rows' => 0,
        'markup_rows' => 0,
        'unchanged_rows' => 0,
        'rows_to_update' => 0,
        'blocked_rows' => [],
        'warning_rows' => [],
        'changes' => [
            'Q_SENTENCE_ERROR' => 0,
            'Q_SENTENCE_ACCURACY' => 0,
            'Q_CIWS' => 0,
        ],
    ];
    $updates = [];
    $lastId = 0;
    $select = $db->prepare($sql);

    do {
        $select->execute(['last_id' => $lastId]);
        $rows = $select->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $row) {
            $lastId = (int) $row['Q_ID'];
            $report['rows_scanned']++;
            $source = 'token';
            try {
                $tokenCounts = tokenCountsFromRow($row);
                $markup = (string) ($row['Q_SCORING'] ?? '');

                if ($tokenCounts === null) {
                    $source = 'markup';
                    $counts = CIWSScoringMarkupParser::parse($markup);

                    // This migration changes sentence scoring, not historical
                    // word scoring. Preserve the stored W aggregate when it
                    // exists, while reporting any markup discrepancy for
                    // researcher follow-up.
                    if ($row['Q_WORD_ERROR'] !== null) {
                        $storedWordCount = (int) $row['Q_WORD_ERROR'];
                        if ($storedWordCount !== $counts['word']) {
                            $report['warning_rows'][] = [
                                'Q_ID' => (int) $row['Q_ID'],
                                'reason' => 'Stored Q_WORD_ERROR was used instead of the reconstructed markup W count.',
                                'stored_word_count' => $storedWordCount,
                                'markup_word_count' => $counts['word'],
                            ];
                        }
                        $counts['word'] = $storedWordCount;
                    }
                } else {
                    // Complete token counters came from the live scorer and are
                    // canonical. Edited old quizzes can retain legacy markup
                    // that the live scorer intentionally ignored.
                    $counts = $tokenCounts;
                }

                $derived = CIWSScoreCalculator::calculate(
                    $counts['correct'],
                    $counts['inaccurate'],
                    $counts['overlap'],
                    $counts['nmae'],
                    $counts['word']
                );
                $newValues = [
                    'Q_SENTENCE_ERROR' => $row['Q_SENTENCE_ERROR'] === null
                        ? null
                        : $derived['sentence_error'],
                    'Q_SENTENCE_ACCURACY' => $row['Q_SENTENCE_ACCURACY'] === null
                        ? null
                        : $derived['sentence_accuracy'],
                    'Q_CIWS' => $row['Q_CIWS'] === null ? null : $derived['ciws'],
                ];

                $changed = false;
                foreach ($newValues as $column => $value) {
                    if (scoreChanged($row[$column], $value)) {
                        $report['changes'][$column]++;
                        $changed = true;
                    }
                }

                $report[$source . '_rows']++;
                if (!$changed && $source === 'token') {
                    $report['unchanged_rows']++;
                    continue;
                }

                $updates[] = [
                    'row' => array_intersect_key($row, array_flip([
                        'Q_ID', 'Q_SENTENCE_ERROR', 'Q_SENTENCE_ACCURACY', 'Q_CIWS',
                        'Q_TOKEN_CORRECT', 'Q_TOKEN_WORD', 'Q_TOKEN_SEN_INACC',
                        'Q_TOKEN_SEN_OVERLAP', 'Q_TOKEN_SEN_NMAE',
                    ])),
                    'counts' => $counts,
                    'scores' => $newValues,
                    'source' => $source,
                ];
                $report['rows_to_update']++;
            } catch (Throwable $exception) {
                $report['blocked_rows'][] = [
                    'Q_ID' => (int) $row['Q_ID'],
                    'reason' => $exception->getMessage(),
                ];
            }
        }
    } while (count($rows) === 100);

    return ['report' => $report, 'updates' => $updates];
}

/** @param array<int,array<string,mixed>> $updates */
function applyUpdates(PDO $db, string $batchId, array $updates): void
{
    $backup = $db->prepare(
        'INSERT INTO ' . CIWS_BACKUP_TABLE . ' (
            BACKUP_BATCH_ID, Q_ID, Q_SENTENCE_ERROR, Q_SENTENCE_ACCURACY, Q_CIWS,
            Q_TOKEN_CORRECT, Q_TOKEN_WORD, Q_TOKEN_SEN_INACC,
            Q_TOKEN_SEN_OVERLAP, Q_TOKEN_SEN_NMAE,
            APPLIED_Q_SENTENCE_ERROR, APPLIED_Q_SENTENCE_ACCURACY, APPLIED_Q_CIWS,
            APPLIED_Q_TOKEN_CORRECT, APPLIED_Q_TOKEN_WORD, APPLIED_Q_TOKEN_SEN_INACC,
            APPLIED_Q_TOKEN_SEN_OVERLAP, APPLIED_Q_TOKEN_SEN_NMAE,
            BACKFILL_SOURCE, BACKED_UP_AT
        ) VALUES (
            :batch_id, :q_id, :sentence_error, :sentence_accuracy, :ciws,
            :token_correct, :token_word, :token_inaccurate,
            :token_overlap, :token_nmae,
            :applied_sentence_error, :applied_sentence_accuracy, :applied_ciws,
            :applied_token_correct, :applied_token_word, :applied_token_inaccurate,
            :applied_token_overlap, :applied_token_nmae,
            :source, NOW()
        )'
    );
    $update = $db->prepare(
        'UPDATE quiz SET
            Q_SENTENCE_ERROR = :sentence_error,
            Q_SENTENCE_ACCURACY = :sentence_accuracy,
            Q_CIWS = :ciws,
            Q_TOKEN_CORRECT = :token_correct,
            Q_TOKEN_WORD = :token_word,
            Q_TOKEN_SEN_INACC = :token_inaccurate,
            Q_TOKEN_SEN_OVERLAP = :token_overlap,
            Q_TOKEN_SEN_NMAE = :token_nmae
         WHERE Q_ID = :q_id'
    );

    foreach ($updates as $candidate) {
        $row = $candidate['row'];
        $counts = $candidate['counts'];
        $scores = $candidate['scores'];
        $backup->execute([
            ':batch_id' => $batchId,
            ':q_id' => $row['Q_ID'],
            ':sentence_error' => $row['Q_SENTENCE_ERROR'],
            ':sentence_accuracy' => $row['Q_SENTENCE_ACCURACY'],
            ':ciws' => $row['Q_CIWS'],
            ':token_correct' => $row['Q_TOKEN_CORRECT'],
            ':token_word' => $row['Q_TOKEN_WORD'],
            ':token_inaccurate' => $row['Q_TOKEN_SEN_INACC'],
            ':token_overlap' => $row['Q_TOKEN_SEN_OVERLAP'],
            ':token_nmae' => $row['Q_TOKEN_SEN_NMAE'],
            ':applied_sentence_error' => $scores['Q_SENTENCE_ERROR'],
            ':applied_sentence_accuracy' => $scores['Q_SENTENCE_ACCURACY'],
            ':applied_ciws' => $scores['Q_CIWS'],
            ':applied_token_correct' => $counts['correct'],
            ':applied_token_word' => $counts['word'],
            ':applied_token_inaccurate' => $counts['inaccurate'],
            ':applied_token_overlap' => $counts['overlap'],
            ':applied_token_nmae' => $counts['nmae'],
            ':source' => $candidate['source'],
        ]);
        $update->execute([
            ':q_id' => $row['Q_ID'],
            ':sentence_error' => $scores['Q_SENTENCE_ERROR'],
            ':sentence_accuracy' => $scores['Q_SENTENCE_ACCURACY'],
            ':ciws' => $scores['Q_CIWS'],
            ':token_correct' => $counts['correct'],
            ':token_word' => $counts['word'],
            ':token_inaccurate' => $counts['inaccurate'],
            ':token_overlap' => $counts['overlap'],
            ':token_nmae' => $counts['nmae'],
        ]);
    }
}

function rollbackBatch(PDO $db, string $batchId): int
{
    ensureBackupTable($db);
    $db->beginTransaction();
    try {
        $select = $db->prepare(
            'SELECT Q_ID FROM ' . CIWS_BACKUP_TABLE . ' WHERE BACKUP_BATCH_ID = :batch_id FOR UPDATE'
        );
        $select->execute([':batch_id' => $batchId]);
        $rowCount = count($select->fetchAll(PDO::FETCH_COLUMN));
        if ($rowCount === 0) {
            throw new RuntimeException('No backup rows exist for batch ' . $batchId . '.');
        }

        $restore = $db->prepare(
            'UPDATE quiz q
             INNER JOIN ' . CIWS_BACKUP_TABLE . ' b ON b.Q_ID = q.Q_ID
             SET q.Q_SENTENCE_ERROR = b.Q_SENTENCE_ERROR,
                 q.Q_SENTENCE_ACCURACY = b.Q_SENTENCE_ACCURACY,
                 q.Q_CIWS = b.Q_CIWS,
                 q.Q_TOKEN_CORRECT = b.Q_TOKEN_CORRECT,
                 q.Q_TOKEN_WORD = b.Q_TOKEN_WORD,
                 q.Q_TOKEN_SEN_INACC = b.Q_TOKEN_SEN_INACC,
                 q.Q_TOKEN_SEN_OVERLAP = b.Q_TOKEN_SEN_OVERLAP,
                 q.Q_TOKEN_SEN_NMAE = b.Q_TOKEN_SEN_NMAE
             WHERE b.BACKUP_BATCH_ID = :batch_id
               AND q.Q_SENTENCE_ERROR <=> b.APPLIED_Q_SENTENCE_ERROR
               AND q.Q_SENTENCE_ACCURACY <=> b.APPLIED_Q_SENTENCE_ACCURACY
               AND q.Q_CIWS <=> b.APPLIED_Q_CIWS
               AND q.Q_TOKEN_CORRECT <=> b.APPLIED_Q_TOKEN_CORRECT
               AND q.Q_TOKEN_WORD <=> b.APPLIED_Q_TOKEN_WORD
               AND q.Q_TOKEN_SEN_INACC <=> b.APPLIED_Q_TOKEN_SEN_INACC
               AND q.Q_TOKEN_SEN_OVERLAP <=> b.APPLIED_Q_TOKEN_SEN_OVERLAP
               AND q.Q_TOKEN_SEN_NMAE <=> b.APPLIED_Q_TOKEN_SEN_NMAE'
        );
        $restore->execute([':batch_id' => $batchId]);
        if ($restore->rowCount() !== $rowCount) {
            throw new RuntimeException(
                'Rollback refused because one or more quiz scores changed after the backfill.'
            );
        }
        $db->commit();
        return $rowCount;
    } catch (Throwable $exception) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        throw $exception;
    }
}

$options = getopt('', ['help', 'apply', 'batch-id:', 'rollback-batch:', 'report:']);
if (array_key_exists('help', $options)) {
    usage();
    exit(0);
}

$apply = array_key_exists('apply', $options);
$batchId = isset($options['batch-id']) ? (string) $options['batch-id'] : '';
$rollbackBatchId = isset($options['rollback-batch']) ? (string) $options['rollback-batch'] : '';
if ($rollbackBatchId !== '' && !$apply) {
    fail('Rollback is write-protected; pass --apply explicitly.');
}
if ($rollbackBatchId !== '' && !validBatchId($rollbackBatchId)) {
    fail('Invalid rollback batch ID.');
}
if ($apply && $rollbackBatchId === '' && !validBatchId($batchId)) {
    fail('Apply requires --batch-id with 1-64 letters, numbers, dots, underscores, or hyphens.');
}

$database = new Database();
$db = $database->connect();
if (!$db instanceof PDO) {
    fail('Database connection failed.');
}
$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

if ($rollbackBatchId !== '') {
    try {
        $restored = rollbackBatch($db, $rollbackBatchId);
        echo json_encode(
            ['mode' => 'rollback', 'batch_id' => $rollbackBatchId, 'rows_restored' => $restored],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        ) . PHP_EOL;
        exit(0);
    } catch (Throwable $exception) {
        fail('Rollback failed: ' . $exception->getMessage(), 2);
    }
}

try {
    if ($apply) {
        ensureBackupTable($db);
        $db->beginTransaction();
    }

    $assessment = assessRows($db, $apply);
    $report = $assessment['report'];

    if (count($report['blocked_rows']) > 0) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        $report['result'] = 'blocked';
    } elseif ($apply) {
        applyUpdates($db, $batchId, $assessment['updates']);
        $db->commit();
        $report['result'] = 'applied';
        $report['batch_id'] = $batchId;
    } else {
        $report['result'] = 'dry-run-complete';
    }

    $json = json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
    echo $json;
    if (isset($options['report'])) {
        $written = file_put_contents((string) $options['report'], $json, LOCK_EX);
        if ($written === false) {
            fail('Could not write the requested report file.');
        }
    }

    exit($report['result'] === 'blocked' ? 2 : 0);
} catch (Throwable $exception) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    fail('Backfill failed: ' . $exception->getMessage(), 2);
}
