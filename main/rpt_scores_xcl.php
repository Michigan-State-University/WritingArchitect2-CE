<?php

include_once '../includes/Database.php';
include_once '../includes/WA_Accounts.php';
include_once '../includes/WA_Security.php';

//get incoming values
$database = new Database();
$db = $database->connect();

$secure_access = check_security($db);
$GLOBALS['page_title'] = "Report Scores";
$r_classid = isset($_GET['r_classid']) ? $_GET['r_classid'] : "";

if ($GLOBALS['USER_LEVEL'] == "STUDENT") die("Access denied.");

//	$r_classid = 16;
$class_teacher = "";
$query = 'select CLASS_NAME, USER_LAST_NAME, USER_FIRST_NAME from config_classes join config_users on CLASS_TEACHER_ID=USER_CODE where CLASS_ID=:cid';
$stmt = $db->prepare($query);
$stmt->bindValue(':cid', $r_classid, PDO::PARAM_INT);
$stmt->execute();
while ($row = $stmt->fetch()) {
	$class_teacher = $row['CLASS_NAME'] . "_" . $row['USER_LAST_NAME'] . " " . $row['USER_FIRST_NAME'];
}
$file = $class_teacher . " Quiz_scores.xls";
header("Content-type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=$file");

function filterData(&$str)
{
	$str = preg_replace("/\t/", "\\t", $str);
	$str = preg_replace("/\r?\n/", "\\n", $str);
	if (strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"';
}

function write_report($db, $classid)
{
	$students = array();
	$test1 = array();
	$test2 = array();
	$test3 = array();

	$query = 'select distinct USER_CODE, USER_LAST_NAME, USER_FIRST_NAME from v_quiz_scores where CLASS_ID=:cid order by USER_LAST_NAME, USER_FIRST_NAME';
	$stmt = $db->prepare($query);
	$stmt->bindValue(':cid', $classid, PDO::PARAM_INT);
	$stmt->execute();
	while ($row = $stmt->fetch()) {
		$students[$row['USER_CODE']] = $row['USER_LAST_NAME'] . ", " . $row['USER_FIRST_NAME'];
	}


	foreach ($students as $x => $x_value) {
		$query = "select Q_PLANNING,Q_TYPING,Q_WORD_COMPLEXITY,Q_WORD_ACCURACY, Q_PROMPT_TITLE, Q_SENTENCE_ACCURACY,Q_SENTENCE_COMPLEXITY,Q_CIWS,Q_TIDE_T,Q_TIDE_I,Q_TIDE_D,Q_TIDE_E from v_quiz_scores where USER_CODE=:x order by Q_ID";
		$stmt = $db->prepare($query);
		$stmt->bindValue(':x', $x, PDO::PARAM_STR);
		$stmt->execute();
		$q_idx = 1;
		$test1[$x]['TIDE'] = "";
		$test1[$x]['PLANNING'] = "";
		$test1[$x]['TYPING'] = "";
		$test1[$x]['WORDCOMP'] = "";
		$test1[$x]['WORDACC'] = "";
		$test1[$x]['SENTACC'] = "";
		$test1[$x]['SENTCOMP'] = "";
		$test1[$x]['CIWS'] = "";

		$test2[$x]['TIDE'] = "";
		$test2[$x]['PLANNING'] = "";
		$test2[$x]['TYPING'] = "";
		$test2[$x]['WORDCOMP'] = "";
		$test2[$x]['WORDACC'] = "";
		$test2[$x]['SENTACC'] = "";
		$test2[$x]['SENTCOMP'] = "";
		$test2[$x]['CIWS'] = "";

		$test3[$x]['TIDE'] = "";
		$test3[$x]['PLANNING'] = "";
		$test3[$x]['TYPING'] = "";
		$test3[$x]['WORDCOMP'] = "";
		$test3[$x]['WORDACC'] = "";
		$test3[$x]['SENTACC'] = "";
		$test3[$x]['SENTCOMP'] = "";
		$test3[$x]['CIWS'] = "";

		while ($row = $stmt->fetch()) {
			$tide = intval($row['Q_TIDE_T']) + intval($row['Q_TIDE_I']) + intval($row['Q_TIDE_D']) + intval($row['Q_TIDE_E']);

			switch ($q_idx) {
				case 1:
					$test1[$x]['TIDE'] = $tide;
					$test1[$x]['PLANNING'] = $row['Q_PLANNING'];
					$test1[$x]['TYPING'] = strlen($row['Q_TYPING']);
					$test1[$x]['WORDCOMP'] = (int)$row['Q_WORD_COMPLEXITY'];
					$test1[$x]['WORDACC'] = trucatenum($row['Q_WORD_ACCURACY']);
					$test1[$x]['SENTACC'] = trucatenum($row['Q_SENTENCE_ACCURACY']);
					$test1[$x]['SENTCOMP'] = trucatenum($row['Q_SENTENCE_COMPLEXITY']);
					$test1[$x]['CIWS'] = calc_CIWS($row['Q_CIWS'], $row['Q_PROMPT_TITLE']);
					break;
				case 2:
					$test2[$x]['TIDE'] = $tide;
					$test2[$x]['PLANNING'] = $row['Q_PLANNING'];
					$test2[$x]['TYPING'] = strlen($row['Q_TYPING']);
					$test2[$x]['WORDCOMP'] = (int)$row['Q_WORD_COMPLEXITY'];
					$test2[$x]['WORDACC'] = trucatenum($row['Q_WORD_ACCURACY']);
					$test2[$x]['SENTACC'] = trucatenum($row['Q_SENTENCE_ACCURACY']);
					$test2[$x]['SENTCOMP'] = trucatenum($row['Q_SENTENCE_COMPLEXITY']);
					$test2[$x]['CIWS'] = calc_CIWS($row['Q_CIWS'], $row['Q_PROMPT_TITLE']);
					break;
				case 3:
					$test3[$x]['TIDE'] = $tide;
					$test3[$x]['PLANNING'] = $row['Q_PLANNING'];
					$test3[$x]['TYPING'] = strlen($row['Q_TYPING']);
					$test3[$x]['WORDCOMP'] = (int)$row['Q_WORD_COMPLEXITY'];
					$test3[$x]['WORDACC'] = trucatenum($row['Q_WORD_ACCURACY']);
					$test3[$x]['SENTACC'] = trucatenum($row['Q_SENTENCE_ACCURACY']);
					$test3[$x]['SENTCOMP'] = trucatenum($row['Q_SENTENCE_COMPLEXITY']);
					$test3[$x]['CIWS'] = calc_CIWS($row['Q_CIWS'], $row['Q_PROMPT_TITLE']);
					break;
			}
			$q_idx++;
		}
	}

	$data = array();

	foreach ($students as $x => $x_value) {
		$newrow = array("STUDENT" => $students[$x], "TIDE 1" => $test1[$x]['TIDE'], "TIDE 2" => $test2[$x]['TIDE'], "TIDE 3" => $test3[$x]['TIDE'], "PLANNING 1" => $test1[$x]['PLANNING'], "PLANNING 2" => $test2[$x]['PLANNING'], "PLANNING 3" => $test3[$x]['PLANNING'], "TYPING 1" => $test1[$x]['TYPING'], "TYPING 2" => $test2[$x]['TYPING'], "TYPING 3" => $test3[$x]['PLANNING'], "WORD COMPLEXITY 1" => $test1[$x]['WORDCOMP'], "WORD COMPLEXITY 2" => $test2[$x]['WORDCOMP'], "WORD COMPLEXITY 3" => $test3[$x]['WORDCOMP'], "WORD ACCURACY 1" => $test1[$x]['WORDACC'], "WORD ACCURACY 2" => $test2[$x]['WORDACC'], "WORD ACCURACY 3" => $test3[$x]['WORDACC'], "SENT ACCURACY 1" => $test1[$x]['SENTACC'], "SENT ACCURACY 2" => $test2[$x]['SENTACC'], "SENT ACCURACY 3" => $test3[$x]['SENTACC'], "SENT COMPLEXITY 1" => $test1[$x]['SENTCOMP'], "SENT COMPLEXITY 2" => $test2[$x]['SENTCOMP'], "SENT COMPLEXITY 3" => $test3[$x]['SENTCOMP'], "CIWS 1" => $test1[$x]['CIWS'], "CIWS 2" => $test2[$x]['CIWS'], "CIWS 3" => $test3[$x]['CIWS']);
		array_push($data, $newrow);
	}



	$flag = false;
	foreach ($data as $row) {
		if (!$flag) {
			// display column names as first row 
			echo implode("\t", array_keys($row)) . "\n";
			$flag = true;
		}
		// filter data 
		array_walk($row, 'filterData');
		echo implode("\t", array_values($row)) . "\n";
	}

	exit;
}
function trucatenum($d)
{
	$newval = $d;
	if ($d != '') {
		$newval = number_format($d, 2, '.', '');
	}
	return $newval;
}
function calc_CIWS($CIWS, $P_TITLE)
{
	$ret_value = $CIWS;
	//if ((int)$CIWS > 0) {
	if (false) {
		switch ($P_TITLE) {
			case "How to Speed up Extinctions":
				// no change
				break;
			case "A Diet to Fuel an Invasive Carp":
				// no change
				break;
			case "Here's a Food Wrapper You Can Eat":
				$ret_value = 15.1287 + (1.0449 * $CIWS);
				break;
			case "Plastic Bottle Village":
				// no change
				break;
			case "Swat Up: Six Reasons to Love Flies":
				$ret_value = 16.2154 + (0.9559 * $CIWS);
				break;
			case "Can an Elevated Bus Solve China's Traffic Woes?":
				$ret_value = -14.21 + (1.1215 * $CIWS);
				break;
		}
	}

	return $ret_value;
}
?>
<?php write_report($db, $r_classid); ?>
