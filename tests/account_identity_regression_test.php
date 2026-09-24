<?php

$failures = [];
$testsRun = 0;

function identity_test(string $name, callable $test): void
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

function identity_assert(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

final class IdentityStatement
{
    private IdentityDatabase $database;
    private string $query;
    private array $parameters = [];
    private array $results = [];
    private int $cursor = 0;

    public function __construct(IdentityDatabase $database, string $query)
    {
        $this->database = $database;
        $this->query = $query;
    }

    public function bindValue(string $name, $value, ?int $type = null): bool
    {
        $this->parameters[$name] = $value;
        return true;
    }

    public function execute(?array $parameters = null): bool
    {
        if ($parameters !== null) {
            $this->parameters = $parameters;
        }

        $normalized = strtolower(preg_replace('/\s+/', ' ', trim($this->query)));
        $this->database->executions[] = [
            'query' => $normalized,
            'parameters' => $this->parameters,
        ];

        if (str_contains($normalized, 'from config_users where user_level=')) {
            $this->results = $this->database->students;
        } elseif (str_contains($normalized, 'select user_code from config_users where user_id=')) {
            $target = $this->parameters[':acct_id'] ?? null;
            $this->results = array_values(array_filter(
                $this->database->students,
                static fn(array $student): bool => (string) $student['USER_ID'] === (string) $target
            ));
        } elseif (str_contains($normalized, 'user_id<>')) {
            $this->results = [];
        } elseif (str_contains($normalized, 'select user_id from config_users where user_code=')) {
            // Deliberately emulate a duplicate/racing lookup returning the wrong row.
            $this->results = [['USER_ID' => 999]];
        } elseif (str_contains($normalized, 'select pupil_id from config_pupils')) {
            $this->results = [];
        } elseif (str_contains($normalized, 'select class_name from config_classes')) {
            $this->results = [];
        } elseif (str_starts_with($normalized, 'delete from config_users')) {
            $target = $this->parameters[':value'] ?? $this->parameters[':acct_id'] ?? null;
            if (str_contains($normalized, 'user_id')) {
                $this->database->students = array_values(array_filter(
                    $this->database->students,
                    static fn(array $student): bool => (string) $student['USER_ID'] !== (string) $target
                ));
            } elseif (str_contains($normalized, 'user_code')) {
                $this->database->students = array_values(array_filter(
                    $this->database->students,
                    static fn(array $student): bool => $student['USER_CODE'] !== $target
                ));
            }
        }

        return true;
    }

    public function rowCount(): int
    {
        return count($this->results);
    }

    public function fetch($mode = null)
    {
        if (!array_key_exists($this->cursor, $this->results)) {
            return false;
        }

        return $this->results[$this->cursor++];
    }
}

final class IdentityDatabase
{
    public array $students;
    public array $executions = [];
    public bool $lastInsertIdCalled = false;

    public function __construct(array $students = [])
    {
        $this->students = $students;
    }

    public function prepare(string $query): IdentityStatement
    {
        return new IdentityStatement($this, $query);
    }

    public function lastInsertId(): string
    {
        $this->lastInsertIdCalled = true;
        return '42';
    }
}

$originalDirectory = getcwd();
chdir(__DIR__ . '/../includes');
require_once __DIR__ . '/../includes/WA_Accounts.php';
chdir($originalDirectory);

function duplicate_students(): array
{
    return [
        [
            'USER_ID' => 41,
            'USER_CODE' => 'synthetic-duplicate',
            'USER_LEVEL' => 'STUDENT',
            'USER_STATUS' => 'ACTIVE',
            'USER_ORGANIZATION' => 'Synthetic School',
            'USER_LAST_NAME' => 'FirstRecord',
            'USER_FIRST_NAME' => 'Student',
        ],
        [
            'USER_ID' => 42,
            'USER_CODE' => 'synthetic-duplicate',
            'USER_LEVEL' => 'STUDENT',
            'USER_STATUS' => 'ACTIVE',
            'USER_ORGANIZATION' => 'Synthetic School',
            'USER_LAST_NAME' => 'SecondRecord',
            'USER_FIRST_NAME' => 'Student',
        ],
    ];
}

identity_test('student delete actions carry distinct USER_ID values', function (): void {
    $GLOBALS['USER_LEVEL'] = 'ADMIN';
    $GLOBALS['SESSION_ID'] = 'synthetic-session';
    $database = new IdentityDatabase(duplicate_students());

    $html = list_students($database, 'Synthetic School');
    preg_match_all("/delete_record\\('([^']+)'\\)/", $html, $matches);

    identity_assert(
        $matches[1] === ['41', '42'],
        'Expected delete actions [41, 42]; USER_CODE-based actions can delete both duplicate records.'
    );
});

identity_test('student account fields are escaped in list HTML', function (): void {
    $GLOBALS['USER_LEVEL'] = 'ADMIN';
    $GLOBALS['SESSION_ID'] = 'synthetic-session';
    $students = duplicate_students();
    $students[0]['USER_FIRST_NAME'] = '<img src=x onerror=alert(1)>';
    $database = new IdentityDatabase($students);

    $html = list_students($database, 'Synthetic School');

    identity_assert(
        strpos($html, '<img src=x onerror=alert(1)>') === false,
        'Expected imported account fields to be escaped at the HTML sink.'
    );
    identity_assert(
        strpos($html, '&lt;img src=x onerror=alert(1)&gt;') !== false,
        'Expected escaped account text to be displayed.'
    );
});

identity_test('edit and roster sinks escape imported account fields', function (): void {
    $editorSource = file_get_contents(__DIR__ . '/../main/edit_user.php');
    $classSource = file_get_contents(__DIR__ . '/../includes/WA_Classes.php');
    $ajaxSource = file_get_contents(__DIR__ . '/../ajax/load_class.php');

    identity_assert(
        strpos($editorSource, 'account_html($ed_acct->USER_FIRST_NAME)') !== false &&
        strpos($editorSource, 'account_html($ed_acct->USER_LAST_NAME)') !== false,
        'Expected edit-form attribute values to use HTML escaping.'
    );
    identity_assert(
        strpos($classSource, "ENT_QUOTES | ENT_SUBSTITUTE") !== false,
        'Expected roster rendering to escape student names.'
    );
    identity_assert(
        strpos($ajaxSource, "ENT_QUOTES | ENT_SUBSTITUTE") !== false,
        'Expected AJAX roster rendering to escape student names.'
    );
});

identity_test('quiz and scoring sinks escape imported account fields', function (): void {
    $quizSource = file_get_contents(__DIR__ . '/../includes/WA_Quiz.php');
    $tideQuizSource = file_get_contents(__DIR__ . '/../includes/WA_Quiz_TIDE.php');
    $scoreReportSource = file_get_contents(__DIR__ . '/../main/rpt_scores.php');

    identity_assert(
        substr_count($quizSource, 'ENT_QUOTES | ENT_SUBSTITUTE') >= 4,
        'Expected standard quiz lists to escape student first and last names.'
    );
    identity_assert(
        substr_count($tideQuizSource, 'ENT_QUOTES | ENT_SUBSTITUTE') >= 4,
        'Expected TIDE quiz lists to escape student first and last names.'
    );
    identity_assert(
        strpos($scoreReportSource, 'account_html($x_value)') !== false,
        'Expected the score report to escape student display names.'
    );
});

identity_test('spreadsheet exports neutralize formula-leading account fields', function (): void {
    $implementation = __DIR__ . '/../includes/Spreadsheet.php';
    if (is_file($implementation)) {
        require_once $implementation;
    }

    identity_assert(
        function_exists('spreadsheet_safe_value'),
        'Expected a shared spreadsheet formula-injection guard.'
    );

    foreach (['=1+1', '+1+1', '-1+1', '@SUM(A1:A2)'] as $value) {
        identity_assert(
            spreadsheet_safe_value($value) === "'" . $value,
            "Expected {$value} to be neutralized for spreadsheet export."
        );
    }

    identity_assert(
        spreadsheet_safe_value('Synthetic Student') === 'Synthetic Student',
        'Expected ordinary account text to remain unchanged.'
    );

    $scoreExportSource = file_get_contents(__DIR__ . '/../main/rpt_scores_xcl.php');
    identity_assert(
        strpos($scoreExportSource, 'spreadsheet_safe_value($str)') !== false,
        'Expected every score-export cell to use the spreadsheet guard.'
    );
});

identity_test('new accounts use the connection lastInsertId', function (): void {
    $GLOBALS['USER_CODE'] = 'synthetic-operator';
    $GLOBALS['USER_LEVEL'] = 'ADMIN';
    $database = new IdentityDatabase();
    $account = new Account($database);
    $account->USER_CODE = 'new-student';
    $account->USER_STATUS = 'ACTIVE';
    $account->USER_ORGANIZATION = 'Synthetic School';
    $account->USER_LAST_NAME = 'Student';
    $account->USER_FIRST_NAME = 'New';
    $account->USER_EMAIL = 'new-student@example.test';
    $account->USER_PASSWORD = '';
    $account->USER_CLASSID = 101;

    $savedId = $account->save_account($database, '0', 'STUDENT');

    identity_assert($database->lastInsertIdCalled, 'Expected save_account() to call lastInsertId().');
    identity_assert((string) $savedId === '42', 'Expected the exact inserted USER_ID, not a USER_CODE lookup.');
});

identity_test('deleting one account by USER_ID preserves a duplicate-code account', function (): void {
    identity_assert(
        method_exists('Account', 'delete_account'),
        'Account::delete_account($db, $userId) is missing.'
    );

    $database = new IdentityDatabase(duplicate_students());
    $account = new Account($database);
    $account->delete_account($database, 41);

    identity_assert(count($database->students) === 1, 'Expected exactly one account to remain.');
    identity_assert($database->students[0]['USER_ID'] === 42, 'Expected USER_ID 42 to be preserved.');
});

identity_test('the deletion endpoint delegates user deletion by USER_ID', function (): void {
    $source = file_get_contents(__DIR__ . '/../ajax/universal_delete.php');

    identity_assert(
        strpos($source, '->delete_account($db, $value)') !== false,
        'Expected universal_delete.php to delegate user deletion to Account::delete_account by USER_ID.'
    );
    identity_assert(
        strpos($source, 'loadAccountByUserCode') === false,
        'User authorization must load the selected account by USER_ID, not an ambiguous USER_CODE.'
    );
});

if ($failures !== []) {
    fwrite(STDERR, "\nAccount identity regression failures ({$testsRun} tests):\n");
    foreach ($failures as $failure) {
        fwrite(STDERR, "- {$failure}\n");
    }
    exit(1);
}

echo "Account identity regressions: {$testsRun} passed.\n";
