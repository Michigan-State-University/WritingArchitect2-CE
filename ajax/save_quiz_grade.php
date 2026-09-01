<?php
include_once '../includes/Database.php';
include_once '../includes/WA_Quiz.php';
include_once '../includes/WA_Accounts.php';
include_once '../includes/WA_Security.php';
include_once '../includes/CIWSScoreSubmission.php';

//get incoming values
$database = new Database();
$db = $database->connect();

$secure_access = check_security($db);

if ($GLOBALS['USER_LEVEL'] == 'STUDENT') die("Access denied.");

$quiz = new QUIZ($db);

$quiz->Q_ID = isset($_POST['Q_ID']) ? $_POST['Q_ID'] : "";
$qid = $quiz->Q_ID;

try {
	$submission = CIWSScoreSubmission::fromInput($_POST);
	$source_counts = $submission['counts'];
	$derived_scores = $submission['scores'];
} catch (InvalidArgumentException $exception) {
	http_response_code(422);
	die($exception->getMessage());
}

$quiz->Q_WORD_COUNT = isset($_POST['Q_WORD_COUNT']) ? $_POST['Q_WORD_COUNT'] : "";
$quiz->Q_SENTENCE_COUNT = isset($_POST['Q_SENTENCE_COUNT']) ? $_POST['Q_SENTENCE_COUNT'] : "";
$quiz->Q_WORD_ERROR = $source_counts['word'];
$quiz->Q_SENTENCE_ERROR = $derived_scores['sentence_error'];
$quiz->Q_CIWS = $derived_scores['ciws'];
$quiz->Q_WORD_ACCURACY = isset($_POST['Q_WORD_ACCURACY']) ? $_POST['Q_WORD_ACCURACY'] : "";
$quiz->Q_SENTENCE_ACCURACY = number_format($derived_scores['sentence_accuracy'], 3, '.', '');
$quiz->Q_WORD_COMPLEXITY = isset($_POST['Q_WORD_COMPLEXITY']) ? $_POST['Q_WORD_COMPLEXITY'] : "";
$quiz->Q_SENTENCE_COMPLEXITY = isset($_POST['Q_SENTENCE_COMPLEXITY']) ? $_POST['Q_SENTENCE_COMPLEXITY'] : "";
$quiz->Q_ESSAY_NOTES = isset($_POST['Q_ESSAY_NOTES']) ? $_POST['Q_ESSAY_NOTES'] : "";
$quiz->Q_SCORING = isset($_POST['Q_SCORING']) ? $_POST['Q_SCORING'] : "";
$quiz->Q_GRADER_ID = isset($_POST['Q_GRADER_ID']) ? $_POST['Q_GRADER_ID'] : "";
$quiz->Q_GRADING_STATUS = isset($_POST['Q_GRADING_STATUS']) ? $_POST['Q_GRADING_STATUS'] : "";
$quiz->Q_PLANNING = isset($_POST['Q_PLANNING']) ? $_POST['Q_PLANNING'] : "";
$quiz->Q_TOKEN_CORRECT = $source_counts['correct'];
$quiz->Q_TOKEN_WORD = $source_counts['word'];
$quiz->Q_TOKEN_SEN_INACC = $source_counts['inaccurate'];
$quiz->Q_TOKEN_SEN_OVERLAP = $source_counts['overlap'];
$quiz->Q_TOKEN_SEN_NMAE = $source_counts['nmae'];

$quiz->save_quiz($db, $qid);
$grader_name = get_account_name($db, $quiz->Q_GRADER_ID);
$quiz->load_quiz($db, $qid);

echo $grader_name . "|" . $quiz->Q_GRADING_STATUS . "|" . $date = date('Y-m-d H:i:s');
