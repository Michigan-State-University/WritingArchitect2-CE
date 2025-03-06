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
$quiz->Q_TYPING_NOTES = isset($_POST['Q_TYPING_NOTES']) ? $_POST['Q_TYPING_NOTES'] : "";
$quiz->Q_TYPING_CHARS = isset($_POST['Q_TYPING_CHARS']) ? $_POST['Q_TYPING_CHARS'] : "";
$quiz->Q_TYPING_WORDS = isset($_POST['Q_TYPING_WORDS']) ? $_POST['Q_TYPING_WORDS'] : "";
$quiz->Q_TYPING_CORRECT = isset($_POST['Q_TYPING_CORRECT']) ? $_POST['Q_TYPING_CORRECT'] : "";
$quiz->Q_GRADER_ID = isset($_POST['Q_GRADER_ID']) ? $_POST['Q_GRADER_ID'] : "";
$quiz->Q_GRADING_STATUS = isset($_POST['Q_GRADING_STATUS']) ? $_POST['Q_GRADING_STATUS'] : "";

$quiz->save_quiz($db, $qid);
$grader_name = get_account_name($db, $quiz->Q_GRADER_ID);
$quiz->load_quiz($db, $qid);
echo $grader_name . "|" . $quiz->Q_GRADING_STATUS . "|" . $date = date('Y-m-d H:i:s');
