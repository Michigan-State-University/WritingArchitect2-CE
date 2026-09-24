<?php

$dsn = getenv('WA_TEST_DB_DSN') ?: 'mysql:host=db;dbname=wa2;charset=utf8mb4';
$username = getenv('WA_TEST_DB_USERNAME') ?: 'waAdmin1';
$password = getenv('WA_TEST_DB_PASSWORD') ?: 'changeme';

$db = new PDO($dsn, $username, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

$originalDirectory = getcwd();
chdir(__DIR__ . '/../includes');
require_once __DIR__ . '/../includes/WA_Accounts.php';
require_once __DIR__ . '/../includes/StudentBatchImport.php';
chdir($originalDirectory);

$failures = [];
$testsRun = 0;
$prefix = 'it' . bin2hex(random_bytes(3));

function integration_test(string $name, callable $test): void
{
    global $failures, $testsRun;
    $testsRun++;
    try {
        $test();
        echo "PASS: {$name}\n";
    } catch (Throwable $exception) {
        $failures[] = "{$name}: {$exception->getMessage()}";
        echo "FAIL: {$name}\n";
    }
}

function integration_assert(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function synthetic_account(PDO $db, string $code): Account
{
    $account = new Account($db);
    $account->USER_CODE = $code;
    $account->USER_STATUS = 'ACTIVE';
    $account->USER_ORGANIZATION = 'Synthetic Integration School';
    $account->USER_LAST_NAME = 'Student';
    $account->USER_FIRST_NAME = 'Synthetic';
    $account->USER_EMAIL = $code . '@example.test';
    $account->USER_PASSWORD = '';
    $account->USER_CLASSID = 101;
    return $account;
}

function count_code(PDO $db, string $code): int
{
    $statement = $db->prepare('SELECT COUNT(*) FROM config_users WHERE USER_CODE=:code');
    $statement->execute([':code' => $code]);
    return (int) $statement->fetchColumn();
}

function cleanup_integration_rows(PDO $db, string $prefix): void
{
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    $select = $db->prepare('SELECT USER_ID FROM config_users WHERE USER_CODE LIKE :prefix');
    $select->execute([':prefix' => $prefix . '%']);
    $ids = $select->fetchAll(PDO::FETCH_COLUMN);
    if ($ids !== []) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $db->prepare("DELETE FROM config_pupils WHERE PUPIL_STUDENTID IN ({$placeholders})")->execute($ids);
    }
    $delete = $db->prepare('DELETE FROM config_users WHERE USER_CODE LIKE :prefix');
    $delete->execute([':prefix' => $prefix . '%']);
}

$GLOBALS['USER_CODE'] = 'admin';
$GLOBALS['USER_LEVEL'] = 'ADMIN';

try {
    integration_test('batch imports are blocked if database uniqueness is absent', function () use ($db): void {
        $db->exec('ALTER TABLE config_users DROP INDEX uq_config_users_user_code');
        try {
            StudentBatchImport::importRows(
                $db,
                [],
                'Synthetic Integration School',
                'ADMIN',
                180
            );
        } catch (RuntimeException $exception) {
            integration_assert(
                str_contains($exception->getMessage(), 'unique USER_CODE migration'),
                'Expected a migration-required error.'
            );
            return;
        } finally {
            $db->exec(
                'ALTER TABLE config_users ADD CONSTRAINT uq_config_users_user_code UNIQUE (USER_CODE)'
            );
        }
        throw new RuntimeException('Expected imports to be blocked without database uniqueness.');
    });

    integration_test('a batch larger than twenty is complete and repeatable', function () use ($db, $prefix): void {
        $stream = fopen('php://temp', 'w+');
        fputcsv($stream, [
            'USER_CODE', 'USER_STATUS', 'USER_FIRST_NAME', 'USER_LAST_NAME',
            'USER_EMAIL', 'USER_PASSWORD', 'USER_CLASSID',
        ]);
        for ($number = 1; $number <= 25; $number++) {
            $code = $prefix . sprintf('s%02d', $number);
            fputcsv($stream, [
                $code,
                'ACTIVE',
                'Synthetic',
                sprintf('Student%02d', $number),
                $code . '@example.test',
                'SyntheticPassword!',
                '101',
            ]);
        }
        rewind($stream);
        $rows = StudentBatchImport::parseCsvString(stream_get_contents($stream));
        fclose($stream);

        $firstStatuses = StudentBatchImport::importRows(
            $db,
            $rows,
            'Synthetic Integration School',
            'ADMIN',
            180
        );
        $repeatStatuses = StudentBatchImport::importRows(
            $db,
            $rows,
            'Synthetic Integration School',
            'ADMIN',
            180
        );

        integration_assert(count($firstStatuses) === 25, 'Expected a status for all 25 students.');
        integration_assert(count(array_filter($firstStatuses, static fn($status) => $status === 'SUCCESS')) === 25,
            'Expected all 25 students to be created.');
        integration_assert(count(array_filter($repeatStatuses, static fn($status) => $status === 'DUPLICATE')) === 25,
            'Expected the repeated batch to create no duplicates.');

        $statement = $db->prepare('SELECT COUNT(*) FROM config_users WHERE USER_CODE LIKE :prefix');
        $statement->execute([':prefix' => $prefix . 's%']);
        integration_assert((int) $statement->fetchColumn() === 25, 'Expected exactly 25 stored students.');
    });

    integration_test('account and pupil creation use the exact inserted USER_ID', function () use ($db, $prefix): void {
        $account = synthetic_account($db, $prefix . 'a');
        $userId = $account->save_account($db, '0', 'STUDENT');

        integration_assert(is_int($userId) && $userId > 0, 'Expected a numeric inserted USER_ID.');
        $statement = $db->prepare(
            'SELECT COUNT(*) FROM config_pupils WHERE PUPIL_STUDENTID=:user_id'
        );
        $statement->execute([':user_id' => $userId]);
        integration_assert((int) $statement->fetchColumn() === 1, 'Expected one pupil row for that USER_ID.');
    });

    integration_test('repeating account creation is idempotent', function () use ($db, $prefix): void {
        $code = $prefix . 'repeat';
        $first = synthetic_account($db, $code)->save_account($db, '0', 'STUDENT');
        $second = synthetic_account($db, $code)->save_account($db, '0', 'STUDENT');

        integration_assert(is_int($first), 'Expected the first creation to succeed.');
        integration_assert($second === 'NOT UNIQUE', 'Expected the repeated creation to be reported as duplicate.');
        integration_assert(count_code($db, $code) === 1, 'Expected exactly one stored account.');
    });

    integration_test('an outer batch transaction can roll back account and pupil rows', function () use ($db, $prefix): void {
        $code = $prefix . 'rollback';
        $db->beginTransaction();
        synthetic_account($db, $code)->save_account($db, '0', 'STUDENT');
        $db->rollBack();

        integration_assert(count_code($db, $code) === 0, 'Expected the account creation to be rolled back.');
    });

    integration_test('deletion targets one numeric USER_ID', function () use ($db, $prefix): void {
        $firstCode = $prefix . 'deletea';
        $secondCode = $prefix . 'deleteb';
        $firstId = synthetic_account($db, $firstCode)->save_account($db, '0', 'STUDENT');
        synthetic_account($db, $secondCode)->save_account($db, '0', 'STUDENT');
        $session = $db->prepare(
            'INSERT INTO man_sessions
                (session_public_id, session_start_time, session_last_updated, session_userid)
             VALUES (:public_id, UTC_TIMESTAMP(), UTC_TIMESTAMP(), :user_code)'
        );
        $session->execute([
            ':public_id' => 'integration-' . $firstId,
            ':user_code' => $firstCode,
        ]);

        $deleted = (new Account($db))->delete_account($db, $firstId);
        integration_assert($deleted === 1, 'Expected exactly one config_users row to be deleted.');
        integration_assert(count_code($db, $firstCode) === 0, 'Expected the selected account to be deleted.');
        integration_assert(count_code($db, $secondCode) === 1, 'Expected the other account to remain.');
        $activeSession = $db->prepare(
            'SELECT COUNT(*) FROM man_sessions
             WHERE session_userid=:user_code AND session_end_time IS NULL'
        );
        $activeSession->execute([':user_code' => $firstCode]);
        integration_assert(
            (int) $activeSession->fetchColumn() === 0,
            'Expected deletion to invalidate sessions for the ambiguous USER_CODE.'
        );
    });

    integration_test('the database unique constraint rejects a race-like duplicate insert', function () use ($db, $prefix): void {
        $code = $prefix . 'unique';
        synthetic_account($db, $code)->save_account($db, '0', 'STUDENT');
        $statement = $db->prepare('INSERT INTO config_users (USER_CODE) VALUES (:code)');

        try {
            $statement->execute([':code' => $code]);
        } catch (PDOException $exception) {
            integration_assert($exception->getCode() === '23000', 'Expected a duplicate-key SQLSTATE.');
            return;
        }
        throw new RuntimeException('Expected the unique USER_CODE constraint to reject the insert.');
    });
} finally {
    cleanup_integration_rows($db, $prefix);
}

if ($failures !== []) {
    fwrite(STDERR, "\nStudent account integration failures ({$testsRun} tests):\n");
    foreach ($failures as $failure) {
        fwrite(STDERR, "- {$failure}\n");
    }
    exit(1);
}

echo "Student account integration regressions: {$testsRun} passed.\n";
