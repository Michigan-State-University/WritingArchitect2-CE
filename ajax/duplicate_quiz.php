<?php
include_once '../includes/Database.php';
include_once '../includes/WA_Quiz.php';
include_once '../includes/WA_Accounts.php';
include_once '../includes/WA_Security.php';

//get incoming values
$database = new Database();
$db = $database->connect();

$secure_access = check_security($db);

if ($GLOBALS['USER_LEVEL'] == 'STUDENT') die("Access denied.");

$quiz = new QUIZ($db);

$quiz->Q_ID = isset($_POST['Q_ID']) ? $_POST['Q_ID'] : "";
$qid = $quiz->Q_ID;

$ver_number = 0;
// GET VERSION NUMBER
$query = 'select count(*) as cnt from quiz where Q_VER_QID=:qid';
echo $query . '<br>';
$stmt = $db->prepare($query);
$stmt->bindValue(':qid', $qid, PDO::PARAM_INT);
$stmt->execute();
if ($stmt->rowCount() > 0) {
	$row = $stmt->fetch();
	$ver_number = (int)$row['cnt'] + 1;
}
echo $qid . '  ver_number=' . $ver_number . '<br>';
$query = 'insert into quiz (Q_PROMPT_ID, Q_PROMPT_TITLE, Q_STUDENT_ID, Q_START_TIME, Q_END_TIME, Q_DURATION, Q_COMPLETED, Q_TYPING, Q_ESSAY, Q_CREATED_AT, Q_CREATED_BY, Q_MODIFIED_ON, Q_MODIFIED_BY) select Q_PROMPT_ID, Q_PROMPT_TITLE, Q_STUDENT_ID, Q_START_TIME, Q_END_TIME, Q_DURATION, Q_COMPLETED, Q_TYPING, Q_ESSAY, Q_CREATED_AT, Q_CREATED_BY, Q_MODIFIED_ON, Q_MODIFIED_BY from quiz where Q_ID=:qid';
$stmt = $db->prepare($query);
$stmt->bindValue(':qid', $qid, PDO::PARAM_INT);
$stmt->execute();

$new_qid = 0;
$query = 'select Q_ID from quiz order by Q_ID desc';
$stmt = $db->prepare($query);
$stmt->execute();
if ($stmt->rowCount() > 0) {
	$row = $stmt->fetch();
	$new_qid = $row['Q_ID'];
}
echo $qid . '  new_qid=' . $new_qid . '<br>';

if ($new_qid > 0) {
	$query = "update quiz set Q_VER_QID=:qid, Q_VERSION=:ver_number, Q_GRADING_STATUS='Submitted' where Q_ID=:new_qid";
	$stmt = $db->prepare($query);
	$stmt->bindValue(':qid', $qid, PDO::PARAM_INT);
	$stmt->bindValue(':ver_number', $ver_number, PDO::PARAM_INT);
	$stmt->bindValue(':new_qid', $new_qid, PDO::PARAM_INT);
	$stmt->execute();
}
