<?php

final class StudentBatchImport
{
    public const MAX_ROWS = 75;
    public const MAX_FILE_BYTES = 2097152;

    private const REQUIRED_HEADERS = [
        'USER_CODE',
        'USER_STATUS',
        'USER_FIRST_NAME',
        'USER_LAST_NAME',
        'USER_EMAIL',
        'USER_PASSWORD',
        'USER_CLASSID',
    ];

    public static function parseCsvFile(string $path): array
    {
        $fileSize = filesize($path);
        if ($fileSize === false || $fileSize > self::MAX_FILE_BYTES) {
            throw new InvalidArgumentException('The uploaded CSV must be no larger than 2 MB.');
        }
        $stream = fopen($path, 'rb');
        if ($stream === false) {
            throw new InvalidArgumentException('The uploaded CSV file could not be opened.');
        }

        try {
            return self::parseCsvStream($stream);
        } finally {
            fclose($stream);
        }
    }

    public static function parseCsvString(string $csv): array
    {
        $stream = fopen('php://temp', 'w+b');
        if ($stream === false) {
            throw new RuntimeException('Unable to create a temporary CSV stream.');
        }

        try {
            fwrite($stream, $csv);
            rewind($stream);
            return self::parseCsvStream($stream);
        } finally {
            fclose($stream);
        }
    }

    public static function importRows(
        $db,
        array $rows,
        string $organization,
        string $actorLevel,
        int $timeBudgetSeconds = 180
    ): array {
        if (!class_exists('Account')) {
            throw new RuntimeException('Account support must be loaded before importing students.');
        }
        if (!in_array($actorLevel, ['ADMIN', 'TEACHER', 'SCORER'], true)) {
            throw new RuntimeException('Access denied.');
        }
        if ($timeBudgetSeconds <= 0) {
            throw new InvalidArgumentException('The import time budget must be greater than zero.');
        }
        self::requireUniqueUserCodeConstraint($db);

        $startedAt = microtime(true);
        $ownsTransaction = method_exists($db, 'inTransaction') && !$db->inTransaction();
        if ($ownsTransaction) {
            $db->beginTransaction();
        }

        try {
            $statuses = [];
            foreach ($rows as $data) {
                if (microtime(true) - $startedAt >= $timeBudgetSeconds) {
                    throw new RuntimeException('The batch exceeded the safe web-request time budget.');
                }

                $account = new Account($db);
                $account->USER_CODE = $data['USER_CODE'];
                $account->USER_STATUS = $data['USER_STATUS'];
                $account->USER_FIRST_NAME = $data['USER_FIRST_NAME'];
                $account->USER_LAST_NAME = $data['USER_LAST_NAME'];
                $account->USER_ORGANIZATION = $organization;
                $account->USER_EMAIL = $data['USER_EMAIL'];
                $account->USER_PASSWORD = $data['USER_PASSWORD'];
                $account->USER_CLASSID = $data['USER_CLASSID'];

                $result = $account->save_account($db, '0', 'STUDENT');
                if ($result === 'NOT UNIQUE') {
                    $statuses[] = 'DUPLICATE';
                } elseif (is_int($result) || ctype_digit((string) $result)) {
                    $statuses[] = 'SUCCESS';
                } else {
                    throw new RuntimeException('An account could not be created.');
                }
            }

            if ($ownsTransaction) {
                $db->commit();
            }
            return $statuses;
        } catch (Throwable $exception) {
            if ($ownsTransaction && $db->inTransaction()) {
                $db->rollBack();
            }
            throw $exception;
        }
    }

    private static function parseCsvStream($stream): array
    {
        $header = fgetcsv($stream, 0, ',', '"', '\\');
        if ($header === false) {
            throw new InvalidArgumentException('The uploaded CSV file is empty.');
        }

        $header = array_map(static fn($value): string => trim((string) $value), $header);
        if (isset($header[0])) {
            $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
        }

        if (count($header) !== count(array_unique($header))) {
            throw new InvalidArgumentException('The CSV header contains duplicate columns.');
        }

        $missing = array_diff(self::REQUIRED_HEADERS, $header);
        if ($missing !== []) {
            throw new InvalidArgumentException(
                'The CSV is missing required columns: ' . implode(', ', $missing) . '.'
            );
        }
        $unexpected = array_diff($header, self::REQUIRED_HEADERS);
        if ($unexpected !== []) {
            throw new InvalidArgumentException(
                'The CSV contains unexpected columns: ' . implode(', ', $unexpected) . '.'
            );
        }

        $rows = [];
        $seenCodes = [];
        $logicalRow = 1;

        while (($values = fgetcsv($stream, 0, ',', '"', '\\')) !== false) {
            $logicalRow++;

            if (count($values) === 1 && trim((string) $values[0]) === '') {
                continue;
            }

            if (count($values) !== count($header)) {
                throw new InvalidArgumentException(
                    "CSV row {$logicalRow} has " . count($values) .
                    ' columns; expected ' . count($header) . '.'
                );
            }

            $row = array_combine($header, $values);
            foreach ($row as $name => $value) {
                if ($name !== 'USER_PASSWORD') {
                    $row[$name] = trim((string) $value);
                }
            }

            $code = $row['USER_CODE'];
            if ($code === '') {
                throw new InvalidArgumentException("CSV row {$logicalRow} has an empty USER_CODE.");
            }
            if (self::stringLength($code) > 20) {
                throw new InvalidArgumentException(
                    "CSV row {$logicalRow} USER_CODE exceeds the 20-character limit."
                );
            }
            self::validateRequiredText($row, 'USER_STATUS', $logicalRow, 10);
            if (!in_array($row['USER_STATUS'], ['ACTIVE', 'INACTIVE', 'Draft'], true)) {
                throw new InvalidArgumentException(
                    "CSV row {$logicalRow} has an invalid USER_STATUS."
                );
            }
            self::validateRequiredText($row, 'USER_FIRST_NAME', $logicalRow, 50);
            self::validateRequiredText($row, 'USER_LAST_NAME', $logicalRow, 50);
            self::validateRequiredText($row, 'USER_EMAIL', $logicalRow, 100);
            if (filter_var($row['USER_EMAIL'], FILTER_VALIDATE_EMAIL) === false) {
                throw new InvalidArgumentException(
                    "CSV row {$logicalRow} has an invalid USER_EMAIL."
                );
            }
            if ($row['USER_PASSWORD'] === '' || self::stringLength($row['USER_PASSWORD']) > 128) {
                throw new InvalidArgumentException(
                    "CSV row {$logicalRow} has an invalid USER_PASSWORD."
                );
            }
            if (filter_var($row['USER_CLASSID'], FILTER_VALIDATE_INT) === false ||
                (int) $row['USER_CLASSID'] <= 0) {
                throw new InvalidArgumentException(
                    "CSV row {$logicalRow} has an invalid USER_CLASSID."
                );
            }

            $normalizedCode = strtolower($code);
            if (isset($seenCodes[$normalizedCode])) {
                throw new InvalidArgumentException(
                    "CSV row {$logicalRow} has duplicate USER_CODE '{$code}'."
                );
            }
            $seenCodes[$normalizedCode] = true;

            $rows[] = $row;
            if (count($rows) > self::MAX_ROWS) {
                throw new InvalidArgumentException(
                    'Too many rows: upload no more than ' . self::MAX_ROWS . ' student accounts at a time.'
                );
            }
        }

        return $rows;
    }

    private static function stringLength(string $value): int
    {
        return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
    }

    private static function validateRequiredText(
        array $row,
        string $field,
        int $logicalRow,
        int $maximumLength
    ): void {
        if ($row[$field] === '' || self::stringLength($row[$field]) > $maximumLength) {
            throw new InvalidArgumentException(
                "CSV row {$logicalRow} has an invalid {$field}."
            );
        }
    }

    private static function requireUniqueUserCodeConstraint($db): void
    {
        $statement = $db->prepare(
            "SELECT COUNT(*)
             FROM information_schema.statistics
             WHERE table_schema=DATABASE()
               AND table_name='config_users'
               AND column_name='USER_CODE'
               AND non_unique=0"
        );
        $statement->execute();
        if ((int) $statement->fetchColumn() < 1) {
            throw new RuntimeException(
                'Student imports are disabled until the unique USER_CODE migration is applied.'
            );
        }
    }
}
