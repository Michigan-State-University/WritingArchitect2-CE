<?php

$failures = [];
$testsRun = 0;

function security_test(string $name, callable $test): void
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

function security_assert(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

final class InvalidSessionStatement
{
    public function bindValue(string $name, $value, ?int $type = null): bool
    {
        return true;
    }

    public function bindParam(string $name, &$value, ?int $type = null): bool
    {
        return true;
    }

    public function execute(): bool
    {
        return true;
    }

    public function fetch(): bool
    {
        return false;
    }
}

final class InvalidSessionDatabase
{
    public function prepare(string $query): InvalidSessionStatement
    {
        return new InvalidSessionStatement();
    }
}

if (($argv[1] ?? '') === '--invalid-session-child') {
    $_GET['id'] = 'synthetic expired session&next=/admin';
    require_once __DIR__ . '/../includes/WA_Security.php';

    check_security(new InvalidSessionDatabase());
    echo 'SENTINEL_AFTER_SECURITY';
    exit(99);
}

security_test('scoring page emits nothing before authentication', function (): void {
    $source = file_get_contents(__DIR__ . '/../main/sch_scoring.php');
    security_assert(str_starts_with($source, '<?php'), 'Expected sch_scoring.php to begin in PHP mode.');

    $securityCheck = strpos($source, 'check_security($db)');
    $firstPhpClose = strpos($source, '?>');
    security_assert($securityCheck !== false, 'Expected sch_scoring.php to authenticate the request.');
    security_assert(
        $firstPhpClose === false || $securityCheck < $firstPhpClose,
        'Expected authentication before the page can emit markup.'
    );
});

security_test('scoring page does not display PHP errors to users', function (): void {
    $source = file_get_contents(__DIR__ . '/../main/sch_scoring.php');
    $securityCheck = strpos($source, 'check_security($db)');
    $displayErrorsOff = strpos($source, "ini_set('display_errors', '0')");

    security_assert($displayErrorsOff !== false, 'Expected display_errors to be disabled on sch_scoring.php.');
    security_assert(
        $displayErrorsOff < $securityCheck,
        'Expected display_errors to be disabled before authentication runs.'
    );
});

security_test('invalid sessions use an encoded logout redirect', function (): void {
    $source = file_get_contents(__DIR__ . '/../includes/WA_Security.php');
    security_assert(
        strpos($source, "header('Location: /main/logout.php?id=' . rawurlencode(\$sess_id2));") !== false,
        'Expected a root-qualified logout redirect that retains the session id using URL encoding.'
    );
});

security_test('invalid sessions terminate before protected code', function (): void {
    $command = [PHP_BINARY, __FILE__, '--invalid-session-child'];
    $pipes = [];
    $process = proc_open($command, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
    security_assert(is_resource($process), 'Unable to start invalid-session child process.');

    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $status = proc_close($process);

    security_assert($status === 0, "Expected redirect termination with status 0; got {$status}. {$stderr}");
    security_assert(
        strpos($stdout, 'SENTINEL_AFTER_SECURITY') === false,
        'Protected code continued after the invalid-session redirect.'
    );
});

if ($failures !== []) {
    fwrite(STDERR, "\nSecurity redirect regression failures ({$testsRun} tests):\n");
    foreach ($failures as $failure) {
        fwrite(STDERR, "- {$failure}\n");
    }
    exit(1);
}

echo "Security redirect regressions: {$testsRun} passed.\n";
