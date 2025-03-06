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
$GLOBALS['page_title'] = "Administrative Dashboard";

if ($GLOBALS['USER_LEVEL'] == "STUDENT") die("Access Denied");

function menu_of_classes($db, $ITEM_NAME)
{
	$cdm = '<select id="' . $ITEM_NAME . '" name="' . $ITEM_NAME . '" size="1">';

	if (strpos($GLOBALS['USER_AUTHORITY'], 'TEACHER') == false) {
		$clauses = "CLASS_SCHOOL_ID=:guss";
	} else {
		$clauses = "CLASS_TEACHER_ID=:guc";
	}

	$query = "SELECT CLASS_ID, CLASS_NAME, USER_LAST_NAME, USER_FIRST_NAME from config_classes join config_users on CLASS_TEACHER_ID=USER_CODE WHERE " . $clauses . " order by CLASS_NAME";
	$stmt = $db->prepare($query);
	if (strpos($GLOBALS['USER_AUTHORITY'], 'TEACHER') == false) {
		$stmt->bindValue(':guss', $GLOBALS['USER_SCHOOL_SN'], PDO::PARAM_STR);
	} else {
		$stmt->bindValue(':guc', $GLOBALS['USER_CODE'], PDO::PARAM_STR);
	}
	$stmt->execute();
	while ($row = $stmt->fetch()) {
		$M_CLASS_ID = $row['CLASS_ID'];
		$M_CLASS_NAME = $row['CLASS_NAME'] . " - " . $row['USER_LAST_NAME'] . ", " . $row['USER_FIRST_NAME'];

		$cdm .= '<option value="' . $M_CLASS_ID . '">' . $M_CLASS_NAME . '</option>';
	}
	$cdm .= "</select>";
	echo $cdm;
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
				<br>
				<?php echo $GLOBALS['USER_LEVEL']; ?>
			</div>
			<?php if ($GLOBALS['USER_LEVEL'] == 'SCORER') { ?>
				<a href="quiz_exportJSON.php?id=<?php echo $GLOBALS["SESSION_ID"]; ?>" target="JSON" class="waButtonSmall">Export Quiz Scores</a>
			<?php } ?>
			<br>
			<br>
			<?php if ($GLOBALS['USER_LEVEL'] == 'SCORER' || $GLOBALS['USER_LEVEL'] == 'TEACHER') { ?>
				<form id="classeditor" action="rpt_scores.php?id=<?php echo $GLOBALS["SESSION_ID"]; ?>" method="post" name="classeditor">
					<table border="1">
						<tr>
							<td><b>QUIZ REPORT</b></td>
						</tr>
						<tr>
							<td>Select Class: <?php menu_of_classes($db, "r_classid"); ?> <input type="submit" class="waButton" value="Display Report">&nbsp;&nbsp;&nbsp;</td>
						</tr>
					</table>
				</form>
			<?php } ?>
		</div>
	</div>
	<!-- Bootstrap core JS-->
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js"></script>
	<!-- Core theme JS-->
	<script src="../js/scripts.js"></script>
	<?php require '../includes/empty_footer.php';   ?>
</body>

</html>
