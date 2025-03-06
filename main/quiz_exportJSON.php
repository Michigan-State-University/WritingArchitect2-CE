<?php
include_once '../includes/Database.php';
include_once '../includes/WA_Security.php';

//get incoming values
$database = new Database();
$db = $database->connect();

$secure_access = check_security($db);

if ($GLOBALS['USER_LEVEL'] != "ADMIN" && $GLOBALS['USER_LEVEL'] != "SCORER") die("Access denied.");

$file = "Quiz_scores.xls";
header("Content-type: aapplication/json");
header("Content-Disposition: attachment; filename=$file");

//$CLASSID = isset($_GET['CLASSID']) ? $_GET['CLASSID'] : "";
$return_arr = array();

$query = "SELECT * from v_quiz_scores";
$stmt = $db->prepare($query);

$stmt->execute();
while ($row = $stmt->fetch()) {
	$return_arr[] = array(
		"user_code" => $row['USER_CODE'],
		"user_last_name" => $row['USER_LAST_NAME'],
		"user_first_name" => $row['USER_FIRST_NAME'],
		"class_id" => intval($row['CLASS_ID']),
		"class_name" => $row['CLASS_NAME'],
		"teacher_id" => $row['CLASS_TEACHER_ID'],
		"id" => intval($row['Q_ID']),
		"prompt_id" => intval($row['Q_PROMPT_ID']),
		"prompt_title" => $row['Q_PROMPT_TITLE'],
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
		"typre_word_count" => intval($row['Q_TYPE_WORD_COUNT']),
		"character_count" => intval($row['Q_CHARACTER_COUNT']),
		"typing_correct" => intval($row['Q_TYPING_CORRECT']),
		"typing_chars" => intval($row['Q_TYPING_CHARS']),
		"planning" => intval($row['Q_PLANNING']),
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
