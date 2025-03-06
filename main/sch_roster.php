<!DOCTYPE html>
<?php
include_once '../includes/Database.php';
include_once '../includes/WA_Accounts.php';
include_once '../includes/WA_Security.php';
include_once '../includes/WA_Classes.php';
include_once '../includes/WA_Quiz.php';
ini_set('display_errors', '1'); // DEVELOPMENT ONLY

//get incoming values
$database = new Database();
$db = $database->connect();

$secure_access = check_security($db);
$cid = isset($_REQUEST['cid']) ? $_REQUEST['cid'] : die();
$QTS = isset($_REQUEST['QTS']) ? $_REQUEST['QTS'] : "";
//$STUDENTIDS = isset($_REQUEST['STUDENTID']) ? $_REQUEST['STUDENTID'] : "";

if ($GLOBALS['USER_LEVEL'] == "STUDENT") die("Access denied.");

$studentids = var_export($_POST, true);
$sids = "0";
$studentids = str_replace(" ", "", $studentids);
$a1 = explode(",", $studentids);

foreach ($a1 as $x) {
	if ($x[1] === "'") {
		$sidarr = explode("=>", $x);
		$sidplus = $sidarr[0];
		$sidplus = str_replace("\n", "", $sidplus);
		$sids .= "," . str_replace("'", "", $sidplus);
	}
}
if ($sids != "0") assign_quizzes_to_student($db, $sids, $QTS);

$GLOBALS['page_title'] = "Class Roster";

function assign_quizzes_to_student($db, $listofstudents, $qts)
{
	$std_arr = explode(",", $listofstudents);
	$query = "SELECT QT_PROMPT_1, QT_PROMPT_2, QT_PROMPT_3 from quiz_template where QT_TITLE=:qts";
	$stmt = $db->prepare($query);
	$stmt->bindValue(':qts', $qts, PDO::PARAM_STR);
	$stmt->execute();
	if ($stmt->rowCount() > 0) {
		$row = $stmt->fetch();
		assign_prompt($db, $std_arr, $row['QT_PROMPT_1']);
		assign_prompt($db, $std_arr, $row['QT_PROMPT_2']);
		assign_prompt($db, $std_arr, $row['QT_PROMPT_3']);
	}
}

function assign_prompt($db, $students_arr, $prompt_name)
{
	$query = "SELECT PROMPT_ID, PROMPT_TITLE from quiz_prompts where PROMPT_SHORT_TITLE=:prompt_name";
	$stmt = $db->prepare($query);
	$stmt->bindValue(':prompt_name', $prompt_name, PDO::PARAM_STR);
	$stmt->execute();
	if ($stmt->rowCount() > 0) {
		$row = $stmt->fetch();
		$promptid = $row['PROMPT_ID'];
		// Old code, add backslahes before single quotes
		// $prompttitle = addslashes($row['PROMPT_TITLE']);
		$prompttitle = $row['PROMPT_TITLE'];
		foreach ($students_arr as $s) {
			if ($s != "0") {
				$dataval = "Q_GRADING_STATUS='Pending',";
				$dataval .= "Q_PROMPT_ID=:promptid,";
				$dataval .= "Q_PROMPT_TITLE=:prompttitle,";
				$dataval .= "Q_STUDENT_ID=:s,";

				$createdtext = " Q_CREATED_BY=:guc, Q_CREATED_AT=UTC_TIMESTAMP()";
				$query = "INSERT into quiz set " . $dataval . $createdtext;
				$stmt = $db->prepare($query);
				$stmt->bindValue(':promptid', $promptid, PDO::PARAM_INT);
				$stmt->bindValue(':prompttitle', $prompttitle, PDO::PARAM_STR);
				$stmt->bindValue(':s', $s, PDO::PARAM_STR);
				$stmt->bindValue(':guc', $GLOBALS['USER_CODE'], PDO::PARAM_STR);
				$stmt->execute();
			}
		}
	}
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
	<script src="https://code.jquery.com/jquery-3.5.0.js"></script>
	<script language="javascript">
		function delete_record(user_code) {
			var msg_str = "Delete user " + user_code + "?";
			if (confirm(msg_str)) {
				// Get ?id from query string
				let sessionID = new URLSearchParams(window.location.search).get("id");

				$.ajax({
					url: '../ajax/universal_delete.php?id=' + sessionID,
					type: "POST",
					data: ({
						TYPE: 'user',
						VALUE: user_code
					}),
					success: function(data) {
						location.reload();
					},
					error: function(data) {
						alert('error: ' + data.responseText);
					}
				});
			}
		}
	</script>
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
				<br>
				<?php echo list_roster($db, $cid); ?>
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
