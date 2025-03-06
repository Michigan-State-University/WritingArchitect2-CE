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
$quiz->Q_TIDE = isset($_POST['Q_TIDE']) ? $_POST['Q_TIDE'] : "";
$quiz->Q_TIDE_SCORING = isset($_POST['Q_TIDE_SCORING']) ? $_POST['Q_TIDE_SCORING'] : "";
$quiz->Q_TIDE_T = isset($_POST['Q_TIDE_T']) ? $_POST['Q_TIDE_T'] : "";
$quiz->Q_TIDE_I = isset($_POST['Q_TIDE_I']) ? $_POST['Q_TIDE_I'] : "";
$quiz->Q_TIDE_D = isset($_POST['Q_TIDE_D']) ? $_POST['Q_TIDE_D'] : "";
$quiz->Q_TIDE_E = isset($_POST['Q_TIDE_E']) ? $_POST['Q_TIDE_E'] : "";
$quiz->Q_TIDE_C = isset($_POST['Q_TIDE_C']) ? $_POST['Q_TIDE_C'] : "";
$quiz->Q_GRADER_ID = isset($_POST['Q_GRADER_ID']) ? $_POST['Q_GRADER_ID'] : "";
$quiz->Q_GRADING_STATUS = isset($_POST['Q_GRADING_STATUS']) ? $_POST['Q_GRADING_STATUS'] : "";

$quiz->save_quiz($db, $qid);
$grader_name = get_account_name($db, $quiz->Q_GRADER_ID);
$quiz->load_quiz($db, $qid);
echo $grader_name . "|" . $quiz->Q_GRADING_STATUS . "|" . $date = date('Y-m-d H:i:s');
