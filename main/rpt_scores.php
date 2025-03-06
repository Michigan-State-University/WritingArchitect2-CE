<!DOCTYPE html>
<?php
include_once '../includes/Database.php';
include_once '../includes/WA_Accounts.php';
include_once '../includes/WA_Security.php';
ini_set('display_errors', '1'); // DEVELOPMENT ONLY

//get incoming values
$database = new Database();
$db = $database->connect();

$secure_access = check_security($db);
$GLOBALS['page_title'] = "Report Scores";
$r_classid = isset($_POST['r_classid']) ? $_POST['r_classid'] : "";

if ($GLOBALS['USER_LEVEL'] == "STUDENT") die("Access denied.");

//	$r_classid = 16;
$class_teacher = "";
$query = 'select CLASS_NAME, USER_LAST_NAME, USER_FIRST_NAME from config_classes join config_users on CLASS_TEACHER_ID=USER_CODE where CLASS_ID=:cid';
$stmt = $db->prepare($query);
$stmt->bindValue(':cid', $r_classid, PDO::PARAM_INT);
$stmt->execute();
while ($row = $stmt->fetch()) {
	$class_teacher = "<b>CLASS: </b>" . $row['CLASS_NAME'] . "  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>TEACHER: </b>" . $row['USER_LAST_NAME'] . ", " . $row['USER_FIRST_NAME'];
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
		$query = "select Q_PLANNING,Q_TYPING,Q_WORD_COMPLEXITY,Q_WORD_ACCURACY,Q_PROMPT_TITLE,Q_SENTENCE_ACCURACY,Q_SENTENCE_COMPLEXITY,Q_CIWS,Q_TIDE_T,Q_TIDE_I,Q_TIDE_D,Q_TIDE_E from v_quiz_scores where USER_CODE=:x order by Q_ID";
		//	echo $query . "<br>";
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


	// write table
	echo "<table><tr><td width='200'></td><td colspan='3' align='center'><b>&nbsp;TIDE&nbsp;</b></td><td colspan='3' align='center'><b>&nbsp;PLANNING&nbsp;</b></td><td colspan='3' align='center'><b>&nbsp;TYPING&nbsp;</b></td><td colspan='3' align='center'><b>&nbsp;WORD COMPLEXITY&nbsp;</b></td><td colspan='3' align='center'><b>&nbsp;WORD ACCURACY&nbsp;</b></td><td colspan='3' align='center'><b>&nbsp;SENT ACCURACY&nbsp;</b></td><td colspan='3' align='center'><b>&nbsp;SENT COMPLEXITY&nbsp;</b></td><td colspan='3' align='center'><b>&nbsp;CIWS&nbsp;</b></td></tr>";

	echo "<tr><td><b>Student</b></td><td align='right' width='30'><b>1</b></td><td align='right' width='30'><b>2</b></td><td align='right' width='30'><b>3</b></td><td align='right' width='30'><b>1</b></td><td align='right' width='30'><b>2</b></td><td align='right' width='30'><b>3</b></td><td align='right'><b>1</b></td><td align='right'><b>2</b></td><td align='right'><b>3</b></td><td align='right' width='30'><b>1</b></td><td align='right' width='30'><b>2</b></td><td align='right' width='30'><b>3</b></td><td align='right'><b>1</b></td><td align='right'><b>2</b></td><td align='right'><b>3</b></td><td align='right'><b>1</b></td><td align='right'><b>2</b></td><td align='right'><b>3</b></td><td align='right'><b>1</b></td><td align='right'><b>2</b></td><td align='right'><b>3</b></td><td align='right'><b>1</b></td><td align='right'><b>2</b></td><td align='right'><b>3</b></td></tr> 	";

	foreach ($students as $x => $x_value) {
		echo "<tr><td>" . $x_value . "</td>";
		echo "<td align='right'>" . $test1[$x]['TIDE'] . "</td><td align='right'>" . $test2[$x]['TIDE'] . "</td><td align='right'>" . $test3[$x]['TIDE'] . "</td>";
		echo "<td align='right'>" . $test1[$x]['PLANNING'] . "</td><td align='right'>" . $test2[$x]['PLANNING'] . "</td><td align='right'>" . $test3[$x]['PLANNING'] . "</td>";
		echo "<td align='right'>" . $test1[$x]['TYPING'] . "</td><td align='right'>" . $test2[$x]['TYPING'] . "</td><td align='right'>" . $test3[$x]['TYPING'] . "</td>";
		echo "<td align='right'>" . $test1[$x]['WORDCOMP'] . "</td><td align='right'>" . $test2[$x]['WORDCOMP'] . "</td><td align='right'>" . $test3[$x]['WORDCOMP'] . "</td>";
		echo "<td align='right'>" . $test1[$x]['WORDACC'] . "</td><td align='right'>" . $test2[$x]['WORDACC'] . "</td><td align='right'>" . $test3[$x]['WORDACC'] . "</td>";
		echo "<td align='right'>" . $test1[$x]['SENTACC'] . "</td><td align='right'>" . $test2[$x]['SENTACC'] . "</td><td align='right'>" . $test3[$x]['SENTACC'] . "</td>";
		echo "<td align='right'>" . $test1[$x]['SENTCOMP'] . "</td><td align='right'>" . $test2[$x]['SENTCOMP'] . "</td><td align='right'>" . $test3[$x]['SENTCOMP'] . "</td>";
		echo "<td align='right'>" . $test1[$x]['CIWS'] . "</td><td align='right'>" . $test2[$x]['CIWS'] . "</td><td align='right'>" . $test3[$x]['CIWS'] . "</td></tr>";
	}
	echo "</table><br><br><br><br>";
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
<html lang="en">

<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
	<meta name="description" content="" />
	<meta name="author" content="" />
	<link rel="icon" type="image/x-icon" href="/favicon.ico" />
	<title>Writing Architect</title>
	<!-- Favicon-->
	<link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
	<!-- Core theme CSS (includes Bootstrap)-->
	<link href="../css/styles.css" rel="stylesheet" />
	<link href="../css/WA.css" rel="stylesheet" />
</head>

<body>
	<div class="d-flex" id="wrapper">
		<!-- Sidebar-->
		<!-- Menu navigation-->
		<?php require '../includes/WA_menu.php';   ?>
		<!-- Page content wrapper-->
		<div id="page-content-wrapper">
			<!-- Top navigation-->
			<?php require '../includes/header.php';   ?>
			<!-- Page content-->
			<div class="container-fluid">

				<?php echo  $class_teacher; ?>
				&nbsp;&nbsp;&nbsp;&nbsp;<a href="rpt_scores_xcl.php?&id=<?php echo $GLOBALS["SESSION_ID"] ?>&r_classid=<?php echo $r_classid ?>" target="excel" class="waButtonSmall">Export to Excel</a>
				<?php write_report($db, $r_classid); ?>
			</div>
		</div>
	</div>
	<!-- Bootstrap core JS-->
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js"></script>
	<!-- Core theme JS-->
	<script src="../js/scripts.js"></script>
	<?php require '../includes/empty_footer.php';   ?>
</body>

</html>
