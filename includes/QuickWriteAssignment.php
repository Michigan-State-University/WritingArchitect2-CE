<?php

final class QuickWriteAssignmentAuthorizationException extends RuntimeException
{
}

final class QuickWriteAssignment
{
    private const MAX_DATABASE_ID = 2147483647;
    private const MAX_STUDENT_IDS = 500;
    private const MAX_TEMPLATE_TITLE_LENGTH = 40;

    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public static function parseStudentIds(array $input): array
    {
        if (!isset($input['student_ids']) || !is_array($input['student_ids'])) {
            throw new InvalidArgumentException('Select at least one student.');
        }
        if (count($input['student_ids']) > self::MAX_STUDENT_IDS) {
            throw new InvalidArgumentException('Too many students were selected.');
        }

        $studentIds = [];
        foreach ($input['student_ids'] as $studentId) {
            if (!is_int($studentId) && !is_string($studentId)) {
                throw new InvalidArgumentException('Invalid student selection.');
            }

            $studentId = (string) $studentId;
            if (!preg_match('/^[1-9][0-9]{0,9}$/', $studentId) || (int) $studentId > self::MAX_DATABASE_ID) {
                throw new InvalidArgumentException('Invalid student selection.');
            }

            $studentIds[(int) $studentId] = (int) $studentId;
        }

        if ($studentIds === []) {
            throw new InvalidArgumentException('Select at least one student.');
        }

        return array_values($studentIds);
    }

    public static function parseClassId($classId): int
    {
        if ((!is_int($classId) && !is_string($classId))
            || !preg_match('/^[1-9][0-9]{0,9}$/', (string) $classId)
            || (int) $classId > self::MAX_DATABASE_ID) {
            throw new InvalidArgumentException('Invalid class.');
        }

        return (int) $classId;
    }

    public function assign(int $classId, array $studentIds, string $templateTitle, array $actor): int
    {
        $templateTitle = trim($templateTitle);
        if ($classId <= 0 || $classId > self::MAX_DATABASE_ID || $templateTitle === '' || strlen($templateTitle) > self::MAX_TEMPLATE_TITLE_LENGTH) {
            throw new InvalidArgumentException('A class and quick-write form are required.');
        }

        $studentIds = self::parseStudentIds(['student_ids' => $studentIds]);
        [$userCode, $userLevel, $schoolId] = $this->actorContext($actor);

        $this->db->beginTransaction();
        try {
            $this->authorizeClass($classId, $userCode, $userLevel, $schoolId, true);
            $students = $this->loadStudentsForUpdate($classId, $studentIds);
            $prompts = $this->loadTemplatePrompts($templateTitle);

            $inserted = 0;
            foreach ($students as $student) {
                $inserted += $this->assignMissingPrompts($student['USER_CODE'], $prompts, $userCode);
            }

            $this->db->commit();
            return $inserted;
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $exception;
        }
    }

    public function assertCanAccessClass(int $classId, array $actor): void
    {
        if ($classId <= 0 || $classId > self::MAX_DATABASE_ID) {
            throw new QuickWriteAssignmentAuthorizationException('Access denied.');
        }
        [$userCode, $userLevel, $schoolId] = $this->actorContext($actor);
        $this->authorizeClass($classId, $userCode, $userLevel, $schoolId, false);
    }

    public static function csrfToken(string $sessionId, string $userCode): string
    {
        return hash_hmac('sha256', 'quick-write-assignment|' . $userCode, $sessionId);
    }

    public static function assertCsrfToken($submittedToken, string $sessionId, string $userCode): void
    {
        if (!is_string($submittedToken)
            || !hash_equals(self::csrfToken($sessionId, $userCode), $submittedToken)) {
            throw new QuickWriteAssignmentAuthorizationException('Access denied.');
        }
    }

    private function actorContext(array $actor): array
    {
        $userCode = isset($actor['user_code']) ? trim((string) $actor['user_code']) : '';
        $userLevel = isset($actor['user_level']) ? strtoupper(trim((string) $actor['user_level'])) : '';
        $schoolId = isset($actor['school_id']) ? trim((string) $actor['school_id']) : '';
        if ($userCode === '' || !in_array($userLevel, ['TEACHER', 'ADMIN', 'SCORER'], true)) {
            throw new QuickWriteAssignmentAuthorizationException('Access denied.');
        }
        return [$userCode, $userLevel, $schoolId];
    }

    private function authorizeClass(int $classId, string $userCode, string $userLevel, string $schoolId, bool $forUpdate): void
    {
        if ($userLevel === 'TEACHER') {
            $query = 'SELECT CLASS_ID FROM config_classes '
                . 'WHERE CLASS_ID=:class_id AND CLASS_TEACHER_ID=:scope_id';
            $scopeId = $userCode;
        } elseif ($userLevel === 'ADMIN' || $userLevel === 'SCORER') {
            if ($schoolId === '') {
                throw new QuickWriteAssignmentAuthorizationException('Access denied.');
            }
            $query = 'SELECT CLASS_ID FROM config_classes '
                . 'WHERE CLASS_ID=:class_id AND CLASS_SCHOOL_ID=:scope_id';
            $scopeId = $schoolId;
        } else {
            throw new QuickWriteAssignmentAuthorizationException('Access denied.');
        }
        if ($forUpdate) {
            $query .= ' FOR UPDATE';
        }

        $statement = $this->db->prepare($query);
        $statement->bindValue(':class_id', $classId, PDO::PARAM_INT);
        $statement->bindValue(':scope_id', $scopeId, PDO::PARAM_STR);
        $statement->execute();
        if (!$statement->fetch()) {
            throw new QuickWriteAssignmentAuthorizationException('Access denied.');
        }
    }

    private function loadStudentsForUpdate(int $classId, array $studentIds): array
    {
        $placeholders = [];
        foreach ($studentIds as $index => $studentId) {
            $placeholders[] = ':student_' . $index;
        }

        $query = 'SELECT u.USER_ID, u.USER_CODE FROM config_pupils p '
            . 'JOIN config_users u ON u.USER_ID=p.PUPIL_STUDENTID '
            . "WHERE p.PUPIL_CLASSID=:class_id AND u.USER_STATUS='ACTIVE' AND u.USER_LEVEL='STUDENT' "
            . 'AND u.USER_ID IN (' . implode(',', $placeholders) . ') FOR UPDATE';
        $statement = $this->db->prepare($query);
        $statement->bindValue(':class_id', $classId, PDO::PARAM_INT);
        foreach ($studentIds as $index => $studentId) {
            $statement->bindValue(':student_' . $index, $studentId, PDO::PARAM_INT);
        }
        $statement->execute();

        $studentsById = [];
        while ($student = $statement->fetch()) {
            $studentsById[(int) $student['USER_ID']] = $student;
        }
        if (count($studentsById) !== count($studentIds)) {
            throw new InvalidArgumentException('One or more selected students are not active members of this class.');
        }

        $students = [];
        foreach ($studentIds as $studentId) {
            $students[] = $studentsById[$studentId];
        }
        return $students;
    }

    private function loadTemplatePrompts(string $templateTitle): array
    {
        $template = $this->db->prepare(
            'SELECT QT_PROMPT_1, QT_PROMPT_2, QT_PROMPT_3 FROM quiz_template '
            . "WHERE QT_TITLE=:title AND QT_STATUS='ACTIVE'"
        );
        $template->bindValue(':title', $templateTitle, PDO::PARAM_STR);
        $template->execute();
        $templateRow = $template->fetch();
        if (!$templateRow) {
            throw new InvalidArgumentException('The selected quick-write form is unavailable.');
        }

        $promptNames = array_values(array_unique(array_filter([
            trim((string) $templateRow['QT_PROMPT_1']),
            trim((string) $templateRow['QT_PROMPT_2']),
            trim((string) $templateRow['QT_PROMPT_3']),
        ], static fn(string $name): bool => $name !== '')));
        if ($promptNames === []) {
            throw new InvalidArgumentException('The selected quick-write form has no prompts.');
        }

        $placeholders = implode(',', array_fill(0, count($promptNames), '?'));
        $statement = $this->db->prepare(
            "SELECT PROMPT_ID, PROMPT_SHORT_TITLE, PROMPT_TITLE FROM quiz_prompts "
            . "WHERE PROMPT_SHORT_TITLE IN ({$placeholders})"
        );
        $statement->execute($promptNames);

        $promptsByName = [];
        while ($prompt = $statement->fetch()) {
            $promptsByName[$prompt['PROMPT_SHORT_TITLE']] = $prompt;
        }
        if (count($promptsByName) !== count($promptNames)) {
            throw new InvalidArgumentException('One or more prompts in the selected form are unavailable.');
        }

        $prompts = [];
        foreach ($promptNames as $promptName) {
            $prompts[] = $promptsByName[$promptName];
        }
        return $prompts;
    }

    private function assignMissingPrompts(string $studentCode, array $prompts, string $createdBy): int
    {
        $promptIds = array_map(static fn(array $prompt): int => (int) $prompt['PROMPT_ID'], $prompts);
        $placeholders = implode(',', array_fill(0, count($promptIds), '?'));
        $existing = $this->db->prepare(
            "SELECT Q_PROMPT_ID FROM quiz WHERE Q_STUDENT_ID=? "
            . "AND Q_PROMPT_ID IN ({$placeholders}) AND Q_COMPLETED IS NULL FOR UPDATE"
        );
        $existing->execute(array_merge([$studentCode], $promptIds));
        $existingPromptIds = array_flip(array_map('intval', $existing->fetchAll(PDO::FETCH_COLUMN)));

        $insert = $this->db->prepare(
            'INSERT INTO quiz '
            . '(Q_GRADING_STATUS, Q_PROMPT_ID, Q_PROMPT_TITLE, Q_STUDENT_ID, Q_CREATED_BY, Q_CREATED_AT) '
            . "VALUES ('Pending', :prompt_id, :prompt_title, :student_code, :created_by, UTC_TIMESTAMP())"
        );
        $inserted = 0;
        foreach ($prompts as $prompt) {
            $promptId = (int) $prompt['PROMPT_ID'];
            if (isset($existingPromptIds[$promptId])) {
                continue;
            }
            $insert->execute([
                ':prompt_id' => $promptId,
                ':prompt_title' => $prompt['PROMPT_TITLE'],
                ':student_code' => $studentCode,
                ':created_by' => $createdBy,
            ]);
            $inserted++;
        }
        return $inserted;
    }
}
