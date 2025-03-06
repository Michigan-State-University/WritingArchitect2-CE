<?php
include_once '../includes/Database.php';
include_once '../includes/WA_Security.php';

//get incoming values
$database = new Database();
$db = $database->connect();

$secure_access = check_security($db);

if ($GLOBALS['USER_LEVEL'] != 'ADMIN') die("Access denied.");

//$CLASSID = isset($_GET['CLASSID']) ? $_GET['CLASSID'] : "";
$return_arr = array();

$query = "SELECT Q_ID, Q_WORD_COUNT, Q_SENTENCE_COUNT, Q_WORD_ERROR, Q_SENTENCE_ERROR, Q_CIWS, Q_WORD_ACCURACY, Q_SENTENCE_ACCURACY, Q_WORD_COMPLEXITY, Q_SENTENCE_COMPLEXITY, Q_TYPING_CORRECT, Q_PLANNING, Q_VERSION, Q_TIDE_T, Q_TIDE_I, Q_TIDE_D, Q_TIDE_E, Q_TIDE_C, Q_VER_QID from quiz WHERE Q_COMPLETED=1";
$stmt = $db->prepare($query);
$stmt->execute();
while ($row = $stmt->fetch()) {
	$return_arr[] = array(
		"id" => intval($row['Q_ID']),
		"word_count" => intval($row['Q_WORD_COUNT']),
		"sentence_count" => intval($row['Q_SENTENCE_COUNT']),
		"word_error" => intval($row['Q_WORD_ERROR']),
		"sentence_error" => intval($row['Q_SENTENCE_ERROR']),
		"ciws" => intval($row['Q_CIWS']),
		"planning" => intval($row['Q_PLANNING']),
		"typing_correct" => intval($row['Q_TYPING_CORRECT']),
		"word_accuracy" => floatval($row['Q_WORD_ACCURACY']),
		"sentence_accuracy" => floatval($row['Q_SENTENCE_ACCURACY']),
		"word_complexity" => floatval($row['Q_WORD_COMPLEXITY']),
		"sentence_complexity" => floatval($row['Q_SENTENCE_COMPLEXITY']),
		"tide_t" => intval($row['Q_TIDE_T']),
		"tide_i" => intval($row['Q_TIDE_I']),
		"tide_d" => intval($row['Q_TIDE_D']),
		"tide_e" => intval($row['Q_TIDE_E']),
		"tide_c" => intval($row['Q_TIDE_C']),
		"version_qid" => intval($row['Q_VER_QID']),
		"version" => intval($row['Q_VERSION'])
	);
}

echo json_encode($return_arr);
