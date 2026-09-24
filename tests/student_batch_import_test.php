<?php

$failures = [];
$testsRun = 0;

function batch_test(string $name, callable $test): void
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

function batch_assert(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function batch_csv(array $rows): string
{
    $stream = fopen('php://temp', 'w+');
    foreach ($rows as $row) {
        fputcsv($stream, $row, ',', '"', '\\');
    }
    rewind($stream);
    $csv = stream_get_contents($stream);
    fclose($stream);

    return $csv;
}

function batch_headers(): array
{
    return [
        'USER_CODE',
        'USER_STATUS',
        'USER_FIRST_NAME',
        'USER_LAST_NAME',
        'USER_EMAIL',
        'USER_PASSWORD',
        'USER_CLASSID',
    ];
}

function synthetic_student(int $number, ?string $code = null): array
{
    return [
        $code ?? sprintf('student%03d', $number),
        'ACTIVE',
        'Student',
        sprintf('Number%03d', $number),
        sprintf('student%03d@example.test', $number),
        sprintf('SyntheticPass%03d!', $number),
        '101',
    ];
}

function parse_student_csv(string $csv): array
{
    $implementation = __DIR__ . '/../includes/StudentBatchImport.php';
    if (is_file($implementation)) {
        require_once $implementation;
    }

    if (!class_exists('StudentBatchImport')) {
        throw new RuntimeException(
            'StudentBatchImport is missing; add the pure preflight parser before wiring database writes.'
        );
    }
    if (!method_exists('StudentBatchImport', 'parseCsvString')) {
        throw new RuntimeException('StudentBatchImport::parseCsvString() is missing.');
    }

    return StudentBatchImport::parseCsvString($csv);
}

function expect_invalid_csv(string $csv, string $messageFragment): void
{
    try {
        parse_student_csv($csv);
    } catch (InvalidArgumentException $exception) {
        batch_assert(
            stripos($exception->getMessage(), $messageFragment) !== false,
            "Expected validation error to mention '{$messageFragment}', got '{$exception->getMessage()}'."
        );
        return;
    }

    throw new RuntimeException('Expected InvalidArgumentException before any account write.');
}

batch_test('parses every row in a batch larger than twenty students', function (): void {
    $rows = [batch_headers()];
    for ($number = 1; $number <= 25; $number++) {
        $rows[] = synthetic_student($number);
    }

    $parsed = parse_student_csv(batch_csv($rows));

    batch_assert(count($parsed) === 25, 'Expected all 25 synthetic students, with no truncation.');
    batch_assert($parsed[24]['USER_CODE'] === 'student025', 'Expected the final CSV row to be preserved.');
});

batch_test('rejects a malformed row width during preflight', function (): void {
    $malformed = synthetic_student(2);
    array_pop($malformed);

    expect_invalid_csv(
        batch_csv([batch_headers(), synthetic_student(1), $malformed]),
        'row 3'
    );
});

batch_test('rejects a USER_CODE longer than the database column during preflight', function (): void {
    expect_invalid_csv(
        batch_csv([batch_headers(), synthetic_student(1, str_repeat('x', 21))]),
        'USER_CODE'
    );
});

batch_test('rejects duplicate USER_CODE values in one upload during preflight', function (): void {
    expect_invalid_csv(
        batch_csv([
            batch_headers(),
            synthetic_student(1, 'duplicate-student'),
            synthetic_student(2, 'duplicate-student'),
        ]),
        'duplicate'
    );
});

batch_test('rejects an empty class assignment during preflight', function (): void {
    $student = synthetic_student(1);
    $student[6] = '';

    expect_invalid_csv(
        batch_csv([batch_headers(), $student]),
        'USER_CLASSID'
    );
});

batch_test('preserves quoted multiline fields and a UTF-8 header BOM', function (): void {
    $headers = batch_headers();
    $headers[0] = "\xEF\xBB\xBF" . $headers[0];
    $student = synthetic_student(1);
    $student[3] = "Synthetic\nStudent";

    $parsed = parse_student_csv(batch_csv([$headers, $student]));

    batch_assert(count($parsed) === 1, 'Expected one logical data row.');
    batch_assert($parsed[0]['USER_LAST_NAME'] === "Synthetic\nStudent", 'Expected the quoted newline to survive.');
});

batch_test('rejects a batch above the synchronous safety limit', function (): void {
    $rows = [batch_headers()];
    for ($number = 1; $number <= 76; $number++) {
        $rows[] = synthetic_student($number);
    }

    expect_invalid_csv(batch_csv($rows), '75');
});

batch_test('rejects invalid status, email, and password fields during preflight', function (): void {
    $invalidStatus = synthetic_student(1);
    $invalidStatus[1] = 'UNKNOWN';
    expect_invalid_csv(batch_csv([batch_headers(), $invalidStatus]), 'USER_STATUS');

    $invalidEmail = synthetic_student(2);
    $invalidEmail[4] = 'not-an-email';
    expect_invalid_csv(batch_csv([batch_headers(), $invalidEmail]), 'USER_EMAIL');

    $invalidPassword = synthetic_student(3);
    $invalidPassword[5] = '';
    expect_invalid_csv(batch_csv([batch_headers(), $invalidPassword]), 'USER_PASSWORD');
});

if ($failures !== []) {
    fwrite(STDERR, "\nStudent batch import regression failures ({$testsRun} tests):\n");
    foreach ($failures as $failure) {
        fwrite(STDERR, "- {$failure}\n");
    }
    exit(1);
}

echo "Student batch import regressions: {$testsRun} passed.\n";
