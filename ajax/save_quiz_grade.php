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

$quiz->Q_WORD_COUNT = isset($_POST['Q_WORD_COUNT']) ? $_POST['Q_WORD_COUNT'] : "";
$quiz->Q_SENTENCE_COUNT = isset($_POST['Q_SENTENCE_COUNT']) ? $_POST['Q_SENTENCE_COUNT'] : "";
$quiz->Q_WORD_ERROR = isset($_POST['Q_WORD_ERROR']) ? $_POST['Q_WORD_ERROR'] : "";
$quiz->Q_SENTENCE_ERROR = isset($_POST['Q_SENTENCE_ERROR']) ? $_POST['Q_SENTENCE_ERROR'] : "";
$quiz->Q_CIWS = isset($_POST['Q_CIWS']) ? $_POST['Q_CIWS'] : "";
$quiz->Q_WORD_ACCURACY = isset($_POST['Q_WORD_ACCURACY']) ? $_POST['Q_WORD_ACCURACY'] : "";
$quiz->Q_SENTENCE_ACCURACY = isset($_POST['Q_SENTENCE_ACCURACY']) ? $_POST['Q_SENTENCE_ACCURACY'] : "";
$quiz->Q_WORD_COMPLEXITY = isset($_POST['Q_WORD_COMPLEXITY']) ? $_POST['Q_WORD_COMPLEXITY'] : "";
$quiz->Q_SENTENCE_COMPLEXITY = isset($_POST['Q_SENTENCE_COMPLEXITY']) ? $_POST['Q_SENTENCE_COMPLEXITY'] : "";
$quiz->Q_ESSAY_NOTES = isset($_POST['Q_ESSAY_NOTES']) ? $_POST['Q_ESSAY_NOTES'] : "";
$quiz->Q_SCORING = isset($_POST['Q_SCORING']) ? $_POST['Q_SCORING'] : "";
$quiz->Q_GRADER_ID = isset($_POST['Q_GRADER_ID']) ? $_POST['Q_GRADER_ID'] : "";
$quiz->Q_GRADING_STATUS = isset($_POST['Q_GRADING_STATUS']) ? $_POST['Q_GRADING_STATUS'] : "";
$quiz->Q_PLANNING = isset($_POST['Q_PLANNING']) ? $_POST['Q_PLANNING'] : "";
$quiz->Q_TOKEN_CORRECT = isset($_POST['Q_TOKEN_CORRECT']) ? $_POST['Q_TOKEN_CORRECT'] : "";
$quiz->Q_TOKEN_WORD = isset($_POST['Q_TOKEN_WORD']) ? $_POST['Q_TOKEN_WORD'] : "";
$quiz->Q_TOKEN_SEN_INACC = isset($_POST['Q_TOKEN_SEN_INACC']) ? $_POST['Q_TOKEN_SEN_INACC'] : "";
$quiz->Q_TOKEN_SEN_OVERLAP = isset($_POST['Q_TOKEN_SEN_OVERLAP']) ? $_POST['Q_TOKEN_SEN_OVERLAP'] : "";
$quiz->Q_TOKEN_SEN_NMAE = isset($_POST['Q_TOKEN_SEN_NMAE']) ? $_POST['Q_TOKEN_SEN_NMAE'] : "";

$quiz->save_quiz($db, $qid);
$grader_name = get_account_name($db, $quiz->Q_GRADER_ID);
$quiz->load_quiz($db, $qid);

echo $grader_name . "|" . $quiz->Q_GRADING_STATUS . "|" . $date = date('Y-m-d H:i:s');
