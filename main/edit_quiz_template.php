<!DOCTYPE html>
<?php
include_once '../includes/Database.php';
include_once '../includes/WA_Security.php';
include_once '../includes/WA_Functions.php';
include_once '../includes/WA_Quiz_Template.php';
ini_set('display_errors', '1'); // DEVELOPMENT ONLY

//get incoming values
$database = new Database();
$db = $database->connect();

$secure_access = check_security($db);

$qid = isset($_REQUEST['qid']) ? $_REQUEST['qid'] : die();
$mode = isset($_POST['mode']) ? $_POST['mode'] : "";

$qtemplate = new QuizTemplate($db);
$qtemplate->QT_ID = $qid;
$qtemplate->QT_TITLE = isset($_POST['QT_TITLE']) ? $_POST['QT_TITLE'] : "";
$qtemplate->QT_DESCRIPTION = isset($_POST['QT_DESCRIPTION']) ? $_POST['QT_DESCRIPTION'] : "";
$qtemplate->QT_PROMPT_1 = isset($_POST['QT_PROMPT_1']) ? $_POST['QT_PROMPT_1'] : "";
$qtemplate->QT_PROMPT_2 = isset($_POST['QT_PROMPT_2']) ? $_POST['QT_PROMPT_2'] : "";
$qtemplate->QT_PROMPT_3 = isset($_POST['QT_PROMPT_3']) ? $_POST['QT_PROMPT_3'] : "";
$qtemplate->QT_GRADES = isset($_POST['QT_GRADES']) ? $_POST['QT_GRADES'] : "";
$qtemplate->QT_STATUS = isset($_POST['QT_STATUS']) ? $_POST['QT_STATUS'] : "";

if ($GLOBALS['USER_LEVEL'] != "ADMIN") die("Access Denied");

$cntl_message = "";
if ($mode == 'SAVE') {
	$result = $qtemplate->save_quiz_template($db, $qid);
	$cntl_message = "Quiz Template saved.";
	if ($result != "") {
		if ($result === "NOT UNIQUE") $cntl_message = "Duplicate Quiz Template name. Quiz Template not saved.";
		else {
			$cntl_message = "Quiz Template created.";
			$qid  = $result;
		}
	}
} else {
	$result = $qtemplate->load_quiz_template($db, $qid);
}

if ($qid == "0") $GLOBALS['page_title'] = "Create Quiz Template";
else $GLOBALS['page_title'] = "Edit Quiz Template";
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
	<link href="../css/parsley.css" rel="stylesheet" />
	<script src="https://code.jquery.com/jquery-3.5.0.js"></script>
	<script language="javascript">
		$(document).ready(function() {
			var cntl_msg = "<?php echo $cntl_message ?>";
			if (cntl_msg !== "") {
				$("#CTL_MESSAGE").show();
				setTimeout(function() {
					$("#CTL_MESSAGE").hide();
				}, 3800);
			}
		});
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
				<div class="popup" id="CTL_MESSAGE">&nbsp;<?php echo $cntl_message ?></div>
				<form id="quizeditor" data-validate action="edit_quiz_template.php?id=<?php echo $GLOBALS["SESSION_ID"]; ?>" method="post" name="quizeditor" data-parsley-validate>
					<input type="hidden" id="mode" name="mode" value="SAVE">
					<input type="hidden" id="qid" name="qid" value="<?php echo $qid; ?>">
					<table border="0" cellspacing="4" cellpadding="2">
						<tr>
							<td class="EditTitle"><label for="QT_TITLE">Quiz Title:</label></td>
							<td><input id="QT_TITLE" class="form-control" type="text" name="QT_TITLE" maxlength="40" value="<?php echo $qtemplate->QT_TITLE; ?>" width="40" required></td>
							<td class="EditTitle"><label for="QT_DESCRIPTION">Description:</label></td>
							<td><input id="QT_DESCRIPTION" style="width:345px;" value="<?php echo $qtemplate->QT_DESCRIPTION; ?>" class="form-control" type="text" name="QT_DESCRIPTION" maxlength="200" required></td>
						</tr>
						<tr>
							<td class="EditTitle"><label for="QT_GRADES">Grades:</label></td>
							<td><input id="QT_GRADES" value="<?php echo $qtemplate->QT_GRADES; ?>" class="form-control" type="text" name="QT_GRADES" maxlength="20" required></td>
							<td class="EditTitle"><label for="QT_STATUS">Status:</label></td>
							<td><?php create_drop_menu($db, "STATUS", $qtemplate->QT_STATUS, "QT_STATUS"); ?></td>
						</tr>
					</table>
					<table border="0" cellspacing="4" cellpadding="2">
						<tr>
							<td class="EditTitle"><label for="QT_PROMPT_1">Prompt #1:</label></td>
							<td><?php echo create_prompt_menu($db, $qtemplate->QT_PROMPT_1, "QT_PROMPT_1"); ?></td>
							<td class="EditTitle"><label for="QT_PROMPT_2">Prompt #2:</label></td>
							<td><?php echo create_prompt_menu($db, $qtemplate->QT_PROMPT_2, "QT_PROMPT_2"); ?></td>
							<td class="EditTitle"><label for="QT_PROMPT_3">Prompt #3:</label></td>
							<td><?php echo create_prompt_menu($db, $qtemplate->QT_PROMPT_3, "QT_PROMPT_3"); ?></td>
						</tr>
						<tr>
							<td>&nbsp;<br><br></td>
							<td colspan="5">
								<input type="submit" class="waButton" value="Save">&nbsp;&nbsp;&nbsp;&nbsp;
								<a href="edit_quiz_template.php?qid=0&id=<?php echo $GLOBALS["SESSION_ID"]; ?>" class="waButton">Create Another Quiz Template</a>
							</td>
						</tr>
					</table>
				</form>
			</div>
		</div>
	</div>
	<!-- Bootstrap core JS-->
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js"></script>
	<!-- Core theme JS-->
	<script src="../js/scripts.js"></script>
	<?php require '../includes/empty_footer.php';   ?>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/7.3/highlight.min.js"></script>
	<script src="../js/parsley.min.js"></script>
	<script type="text/javascript">
		$(function() {
			$('#quizeditor').parsley().on('field:validated', function() {
				var ok = $('.parsley-error').length === 0;

			});
		});
	</script>
</body>

</html>
