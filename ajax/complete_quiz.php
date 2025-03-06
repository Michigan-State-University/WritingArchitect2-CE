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
$query = 'select Q_START_TIME from quiz where Q_VER_QID=:qid';
$stmt = $db->prepare($query);
$stmt->bindValue(':qid', $qid, PDO::PARAM_INT);
$stmt->execute();
if ($stmt->rowCount() > 0) {
	$row = $stmt->fetch();
	$Q_START_TIME = $row['Q_START_TIME'];
}

$query = "update quiz set Q_COMPLETED='1', Q_END_TIME='" . $Q_START_TIME . "', Q_GRADING_STATUS='Submitted' where Q_ID=:qid";
$stmt = $db->prepare($query);
$stmt->bindValue(':qid', $qid, PDO::PARAM_INT);
$stmt->execute();
