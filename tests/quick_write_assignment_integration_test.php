<?php

$dsn = getenv('WA_TEST_DB_DSN') ?: 'mysql:host=db;dbname=wa2;charset=utf8mb4';
$username = getenv('WA_TEST_DB_USERNAME') ?: 'waAdmin1';
$password = getenv('WA_TEST_DB_PASSWORD') ?: 'changeme';

$db = new PDO($dsn, $username, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

$failures = [];
$testsRun = 0;

function assignment_test(string $name, callable $test): void
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

function assignment_assert(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$implementation = __DIR__ . '/../includes/QuickWriteAssignment.php';
assignment_test('quick-write assignment implementation exists', function () use ($implementation): void {
    assignment_assert(is_file($implementation), 'Expected includes/QuickWriteAssignment.php to exist.');
});

if (!is_file($implementation)) {
    foreach ($failures as $failure) {
        fwrite(STDERR, $failure . "\n");
    }
    exit(1);
}

require_once $implementation;
require_once __DIR__ . '/../includes/WA_Classes.php';

$suffix = (string) random_int(100000, 999999);
$teacherCode = 'qwt' . $suffix;
$otherTeacherCode = 'qwo' . $suffix;
$schoolId = 'qw-school-' . $suffix;
$templateTitle = 'qws-' . $suffix;
$promptNames = ['qwa-' . $suffix, 'qwb-' . $suffix, 'qwc-' . $suffix];
$numericCodeBase = 800000000 + (int) $suffix;
$studentIds = [];
$studentCodes = [];
$classId = null;

function cleanup_assignment_fixture(PDO $db, string $teacherCode, string $otherTeacherCode, array $studentCodes, ?int $classId, string $templateTitle, array $promptNames): void
{
    if ($db->inTransaction()) {
        $db->rollBack();
    }

    if ($studentCodes !== []) {
        $placeholders = implode(',', array_fill(0, count($studentCodes), '?'));
        $db->prepare("DELETE FROM quiz WHERE Q_STUDENT_ID IN ({$placeholders})")->execute($studentCodes);
    }
    if ($classId !== null) {
        $db->prepare('DELETE FROM config_pupils WHERE PUPIL_CLASSID=?')->execute([$classId]);
        $db->prepare('DELETE FROM config_classes WHERE CLASS_ID=?')->execute([$classId]);
    }
    $db->prepare('DELETE FROM quiz_template WHERE QT_TITLE=?')->execute([$templateTitle]);
    $promptPlaceholders = implode(',', array_fill(0, count($promptNames), '?'));
    $db->prepare("DELETE FROM quiz_prompts WHERE PROMPT_SHORT_TITLE IN ({$promptPlaceholders})")->execute($promptNames);

    $codes = array_merge($studentCodes, [$teacherCode, $otherTeacherCode]);
    $userPlaceholders = implode(',', array_fill(0, count($codes), '?'));
    $db->prepare("DELETE FROM config_users WHERE USER_CODE IN ({$userPlaceholders})")->execute($codes);
}

try {
    cleanup_assignment_fixture($db, $teacherCode, $otherTeacherCode, $studentCodes, $classId, $templateTitle, $promptNames);

    $insertUser = $db->prepare(
        'INSERT INTO config_users '
        . '(USER_CODE, USER_LEVEL, USER_STATUS, USER_ORGANIZATION, USER_LAST_NAME, USER_FIRST_NAME) '
        . "VALUES (:code, :level, :status, 'Synthetic Quick Write School', :last_name, 'Synthetic')"
    );
    $insertUser->execute([':code' => $teacherCode, ':level' => 'TEACHER', ':status' => 'ACTIVE', ':last_name' => 'Teacher']);
    $insertUser->execute([':code' => $otherTeacherCode, ':level' => 'TEACHER', ':status' => 'ACTIVE', ':last_name' => 'OtherTeacher']);
    $otherTeacherId = (int) $db->lastInsertId();

    $insertClass = $db->prepare(
        'INSERT INTO config_classes (CLASS_NAME, CLASS_SCHOOL_ID, CLASS_TEACHER_ID) '
        . "VALUES ('Synthetic Quick Write Class', :school, :teacher)"
    );
    $insertClass->execute([':school' => $schoolId, ':teacher' => $teacherCode]);
    $classId = (int) $db->lastInsertId();

    $insertPupil = $db->prepare(
        'INSERT INTO config_pupils (PUPIL_CLASSID, PUPIL_STUDENTID) VALUES (:class_id, :student_id)'
    );
    // A malformed legacy membership must never expose an active non-student in the roster.
    $insertPupil->execute([':class_id' => $classId, ':student_id' => $otherTeacherId]);
    for ($number = 1; $number <= 27; $number++) {
        $code = (string) ($numericCodeBase + $number);
        $studentCodes[] = $code;
        $insertUser->execute([
            ':code' => $code,
            ':level' => 'STUDENT',
            ':status' => $number === 27 ? 'INACTIVE' : 'ACTIVE',
            ':last_name' => sprintf('Student%02d', $number),
        ]);
        $studentId = (int) $db->lastInsertId();
        $studentIds[] = $studentId;
        if ($number <= 25 || $number === 27) {
            $insertPupil->execute([':class_id' => $classId, ':student_id' => $studentId]);
        }
    }

    $insertPrompt = $db->prepare(
        "INSERT INTO quiz_prompts (PROMPT_SHORT_TITLE, PROMPT_TITLE, PROMPT_STATUS) VALUES (?, ?, 'ACTIVE')"
    );
    foreach ($promptNames as $index => $promptName) {
        $insertPrompt->execute([$promptName, 'Synthetic Prompt ' . ($index + 1)]);
    }
    $db->prepare(
        "INSERT INTO quiz_template (QT_TITLE, QT_PROMPT_1, QT_PROMPT_2, QT_PROMPT_3, QT_STATUS) "
        . "VALUES (?, ?, ?, ?, 'ACTIVE')"
    )->execute([$templateTitle, $promptNames[0], $promptNames[1], $promptNames[2]]);

    assignment_test('structured student_ids input is validated and deduplicated', function () use ($studentIds): void {
        $selected = QuickWriteAssignment::parseStudentIds([
            'student_ids' => [(string) $studentIds[0], (string) $studentIds[0], (string) $studentIds[1]],
        ]);
        assignment_assert($selected === [$studentIds[0], $studentIds[1]], 'Expected stable integer IDs in first-seen order.');

        foreach ([['student_ids' => '1'], ['student_ids' => ['0']], ['student_ids' => ['1x']]] as $invalid) {
            try {
                QuickWriteAssignment::parseStudentIds($invalid);
            } catch (InvalidArgumentException $exception) {
                continue;
            }
            throw new RuntimeException('Expected malformed student_ids input to be rejected.');
        }

        try {
            QuickWriteAssignment::parseStudentIds(['student_ids' => range(1, 501)]);
        } catch (InvalidArgumentException $exception) {
            return;
        }
        throw new RuntimeException('Expected an excessive student selection to be rejected.');
    });

    assignment_test('class IDs are positive signed database integers', function (): void {
        assignment_assert(QuickWriteAssignment::parseClassId('1') === 1, 'Expected a valid class ID to parse.');
        foreach ([null, 0, '-1', '1x', '2147483648'] as $invalid) {
            try {
                QuickWriteAssignment::parseClassId($invalid);
            } catch (InvalidArgumentException $exception) {
                continue;
            }
            throw new RuntimeException('Expected an invalid or out-of-range class ID to be rejected.');
        }
    });

    assignment_test('roster includes only active students and submits stable USER_ID values', function () use ($db, $classId, $studentIds, $teacherCode, $schoolId, $otherTeacherId): void {
        $GLOBALS['SESSION_ID'] = 'synthetic-session';
        $GLOBALS['USER_CODE'] = $teacherCode;
        $GLOBALS['USER_LEVEL'] = 'TEACHER';
        $GLOBALS['USER_AUTHORITY'] = '';
        $GLOBALS['USER_SCHOOL_SN'] = $schoolId;
        $html = list_roster($db, $classId);

        assignment_assert(substr_count($html, 'name="student_ids[]"') === 25, 'Expected all 25 roster students to use the structured field name.');
        assignment_assert(!str_contains($html, 'value="' . $otherTeacherId . '"'), 'Expected non-student class members to be omitted.');
        foreach ($studentIds as $index => $studentId) {
            if ($index === 25) {
                break;
            }
            assignment_assert(str_contains($html, 'value="' . $studentId . '"'), 'Expected each checkbox value to be its USER_ID.');
        }
    });

    assignment_test('teacher assigns three prompts to all 25 students exactly once', function () use ($db, $classId, $studentIds, $studentCodes, $teacherCode, $schoolId, $templateTitle): void {
        $service = new QuickWriteAssignment($db);
        $inClassStudentIds = array_slice($studentIds, 0, 25);
        $first = $service->assign($classId, $inClassStudentIds, $templateTitle, [
            'user_code' => $teacherCode,
            'user_level' => 'TEACHER',
            'authority' => '',
            'school_id' => $schoolId,
        ]);
        assignment_assert($first === 75, 'Expected 75 new pending assignments for 25 students and 3 prompts.');

        $second = $service->assign($classId, array_merge($inClassStudentIds, [$studentIds[0]]), $templateTitle, [
            'user_code' => $teacherCode,
            'user_level' => 'TEACHER',
            'authority' => '',
            'school_id' => $schoolId,
        ]);
        assignment_assert($second === 0, 'Expected a repeated submission to be idempotent.');

        $placeholders = implode(',', array_fill(0, count($inClassStudentIds), '?'));
        $count = $db->prepare("SELECT COUNT(*) FROM quiz WHERE Q_STUDENT_ID IN ({$placeholders}) AND Q_COMPLETED IS NULL");
        $count->execute(array_slice($studentCodes, 0, 25));
        assignment_assert((int) $count->fetchColumn() === 75, 'Expected exactly 75 pending quiz rows after repeat submission.');
    });

    assignment_test('out-of-class selection is rejected without partial assignment', function () use ($db, $classId, $studentIds, $teacherCode, $schoolId, $templateTitle, $studentCodes): void {
        $service = new QuickWriteAssignment($db);
        try {
            $service->assign($classId, [$studentIds[0], $studentIds[25]], $templateTitle, [
                'user_code' => $teacherCode,
                'user_level' => 'TEACHER',
                'authority' => '',
                'school_id' => $schoolId,
            ]);
        } catch (InvalidArgumentException $exception) {
            assignment_assert(!$db->inTransaction(), 'Expected a rejected batch to roll back its transaction.');
            $count = $db->prepare('SELECT COUNT(*) FROM quiz WHERE Q_STUDENT_ID=?');
            $count->execute([$studentCodes[25]]);
            assignment_assert((int) $count->fetchColumn() === 0, 'Expected no assignment for the out-of-class student.');
            return;
        }
        throw new RuntimeException('Expected out-of-class student selection to fail.');
    });

    assignment_test('inactive roster members are hidden and rejected', function () use ($db, $classId, $studentIds, $teacherCode, $schoolId, $templateTitle): void {
        $GLOBALS['SESSION_ID'] = 'synthetic-session';
        $html = list_roster($db, $classId);
        assignment_assert(!str_contains($html, 'value="' . $studentIds[26] . '"'), 'Expected inactive students to be omitted from the roster form.');

        $service = new QuickWriteAssignment($db);
        try {
            $service->assign($classId, [$studentIds[26]], $templateTitle, [
                'user_code' => $teacherCode,
                'user_level' => 'TEACHER',
                'school_id' => $schoolId,
            ]);
        } catch (InvalidArgumentException $exception) {
            return;
        }
        throw new RuntimeException('Expected inactive students to be rejected server-side.');
    });

    assignment_test('a blank-authority teacher cannot assign to another teacher same-school class', function () use ($db, $classId, $studentIds, $otherTeacherCode, $schoolId, $templateTitle): void {
        $service = new QuickWriteAssignment($db);
        try {
            $service->assign($classId, [$studentIds[0]], $templateTitle, [
                'user_code' => $otherTeacherCode,
                'user_level' => 'TEACHER',
                'authority' => '',
                'school_id' => $schoolId,
            ]);
        } catch (RuntimeException $exception) {
            return;
        }
        throw new RuntimeException('Expected class/teacher scope authorization to fail.');
    });

    assignment_test('roster GET is authorized before student data is rendered', function (): void {
        $source = file_get_contents(__DIR__ . '/../main/sch_roster.php');
        $ajaxSource = file_get_contents(__DIR__ . '/../ajax/load_class.php');
        $authorization = strpos($source, 'assertCanAccessClass');
        $render = strpos($source, 'list_roster($db, $cid)');
        assignment_assert($authorization !== false, 'Expected roster GET to enforce class scope.');
        assignment_assert($render !== false && $authorization < $render, 'Expected authorization before roster rendering.');
        assignment_assert(str_contains($ajaxSource, 'assertCanAccessClass'), 'Expected the AJAX roster endpoint to enforce class scope.');
    });

    assignment_test('unknown roles are denied instead of receiving school scope', function () use ($db, $classId, $teacherCode, $schoolId): void {
        $service = new QuickWriteAssignment($db);
        try {
            $service->assertCanAccessClass($classId, [
                'user_code' => $teacherCode,
                'user_level' => 'UNKNOWN',
                'school_id' => $schoolId,
            ]);
        } catch (QuickWriteAssignmentAuthorizationException $exception) {
            return;
        }
        throw new RuntimeException('Expected an unknown user level to be denied.');
    });

    assignment_test('only explicit elevated roles receive same-school class scope', function () use ($db, $classId, $teacherCode, $schoolId): void {
        $service = new QuickWriteAssignment($db);
        foreach (['ADMIN', 'SCORER'] as $userLevel) {
            $service->assertCanAccessClass($classId, [
                'user_code' => $teacherCode,
                'user_level' => $userLevel,
                'school_id' => $schoolId,
            ]);
        }
        try {
            $service->assertCanAccessClass($classId, [
                'user_code' => $teacherCode,
                'user_level' => 'ADMIN',
                'school_id' => 'different-school',
            ]);
        } catch (QuickWriteAssignmentAuthorizationException $exception) {
            return;
        }
        throw new RuntimeException('Expected an elevated user from a different school to be denied.');
    });

    assignment_test('template titles are bounded to the database contract', function () use ($db, $classId, $studentIds, $teacherCode, $schoolId): void {
        $service = new QuickWriteAssignment($db);
        try {
            $service->assign($classId, [$studentIds[0]], str_repeat('x', 41), [
                'user_code' => $teacherCode,
                'user_level' => 'TEACHER',
                'school_id' => $schoolId,
            ]);
        } catch (InvalidArgumentException $exception) {
            return;
        }
        throw new RuntimeException('Expected an overlong template title to be rejected.');
    });

    assignment_test('class navigation uses exact USER_LEVEL role checks', function (): void {
        $classSource = file_get_contents(__DIR__ . '/../includes/WA_Classes.php');
        $dashboardSource = file_get_contents(__DIR__ . '/../main/sch_dashboard.php');
        assignment_assert(!str_contains($classSource, 'strpos($GLOBALS[\'USER_AUTHORITY\']'), 'Expected class lists to avoid authority substring checks.');
        assignment_assert(!str_contains($dashboardSource, 'strpos($GLOBALS[\'USER_AUTHORITY\']'), 'Expected dashboard class menus to avoid authority substring checks.');
        assignment_assert(str_contains($classSource, '$userLevel === \'TEACHER\''), 'Expected class lists to use the exact TEACHER user level.');
        assignment_assert(str_contains($dashboardSource, '$GLOBALS[\'USER_LEVEL\'] === \'TEACHER\''), 'Expected dashboard class menus to use the exact TEACHER user level.');
    });

    assignment_test('CSRF token is tied to both authenticated session and user', function () use ($teacherCode): void {
        $token = QuickWriteAssignment::csrfToken('synthetic-session', $teacherCode);
        QuickWriteAssignment::assertCsrfToken($token, 'synthetic-session', $teacherCode);
        foreach ([
            ['bad-token', 'synthetic-session', $teacherCode],
            [$token, 'other-session', $teacherCode],
            [$token, 'synthetic-session', 'other-user'],
        ] as $invalid) {
            try {
                QuickWriteAssignment::assertCsrfToken($invalid[0], $invalid[1], $invalid[2]);
            } catch (QuickWriteAssignmentAuthorizationException $exception) {
                continue;
            }
            throw new RuntimeException('Expected an invalid CSRF context to be rejected.');
        }
    });

    assignment_test('roster POST has CSRF and generic error handling safeguards', function (): void {
        $rosterSource = file_get_contents(__DIR__ . '/../main/sch_roster.php');
        $classSource = file_get_contents(__DIR__ . '/../includes/WA_Classes.php');
        assignment_assert(str_starts_with($rosterSource, '<?php'), 'Expected no output before roster authentication.');
        assignment_assert(str_contains($rosterSource, "ini_set('display_errors', '0')"), 'Expected display_errors to be disabled.');
        assignment_assert(
            strpos($rosterSource, "ini_set('display_errors', '0')") < strpos($rosterSource, "include_once '../includes/Database.php'"),
            'Expected display_errors to be disabled before application includes run.'
        );
        assignment_assert(str_contains($rosterSource, 'assertCsrfToken'), 'Expected POST CSRF validation.');
        assignment_assert(str_contains($classSource, 'csrf_token'), 'Expected the roster form to include a CSRF token.');
        assignment_assert(str_contains($rosterSource, 'catch (Throwable'), 'Expected unexpected errors to return a generic response.');
    });

    assignment_test('CI runs the quick-write assignment integration test', function (): void {
        $workflow = file_get_contents(__DIR__ . '/../.github/workflows/tests.yml');
        assignment_assert(
            str_contains($workflow, 'php tests/quick_write_assignment_integration_test.php'),
            'Expected the CE workflow to run the assignment regression test.'
        );
    });
} finally {
    cleanup_assignment_fixture($db, $teacherCode, $otherTeacherCode, $studentCodes, $classId, $templateTitle, $promptNames);
}

echo "Ran {$testsRun} quick-write assignment integration tests.\n";
if ($failures !== []) {
    foreach ($failures as $failure) {
        fwrite(STDERR, $failure . "\n");
    }
    exit(1);
}
