<!DOCTYPE html>
<?php
include_once '../includes/Database.php';
include_once '../includes/WA_Accounts.php';
include_once '../includes/WA_Security.php';
include_once '../includes/WA_Functions.php';
include_once '../includes/WA_Classes.php';
ini_set('display_errors', '1'); // DEVELOPMENT ONLY

//get incoming values
$database = new Database();
$db = $database->connect();

$secure_access = check_security($db);

$cid = isset($_REQUEST['cid']) ? $_REQUEST['cid'] : die();
$mode = isset($_POST['mode']) ? $_POST['mode'] : "";

$class = new School_Class($db);
$class->CLASS_ID = $cid;
$class->CLASS_NAME = isset($_POST['CLASS_NAME']) ? $_POST['CLASS_NAME'] : "";
$class->CLASS_SCHOOL_ID = $GLOBALS['USER_SCHOOL_SN'];
$class->CLASS_GRADE = isset($_POST['CLASS_GRADE']) ? $_POST['CLASS_GRADE'] : "";
$class->CLASS_TEACHER_ID = isset($_POST['CLASS_TEACHER_ID']) ? $_POST['CLASS_TEACHER_ID'] : "";

if ($GLOBALS['USER_LEVEL'] == "STUDENT") die("Access Denied");

$cntl_message = "";
if ($mode == 'SAVE') {
	$result = $class->save_class($db, $cid);
	$cntl_message = "Class saved.";
	if ($result != "") {
		$cntl_message = "Class created.";
		$cid  = $result;
	}
} else {
	$result = $class->load_class($db, $cid);
}

if ($cid == "0") $GLOBALS['page_title'] = "Create Class";
else $GLOBALS['page_title'] = "Edit Class";


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
				<form id="classeditor" action="edit_class.php?id=<?php echo $GLOBALS["SESSION_ID"]; ?>" method="post" name="classeditor" data-parsley-validate>
					<input type="hidden" id="mode" name="mode" value="SAVE">
					<input type="hidden" id="cid" name="cid" value="<?php echo $cid; ?>">
					<div class="popup" id="CTL_MESSAGE">&nbsp;Quiz Saved</div>
					<table border="0" cellspacing="4" cellpadding="2">
						<tr>
							<td class="EditTitle" nowrap><label for="CLASS_NAME">Class Name:</label></td>
							<td><input id="CLASS_NAME" class="form-control" type="text" name="CLASS_NAME" maxlength="100" value="<?php echo $class->CLASS_NAME; ?>" required></td>
						</tr>
						<tr>
							<td class="EditTitle"><label for="CLASS_GRADE">Grade:</label></td>
							<td><input id="CLASS_GRADE" value="<?php echo $class->CLASS_GRADE; ?>" class="form-control" type="text" name="CLASS_GRADE" required></td>
						</tr>
						<tr>
							<td class="EditTitle"><label for="CLASS_TEACHER_ID">Teacher:</label></td>
							<td><?php menu_school_teacher($db, $class->CLASS_TEACHER_ID, "CLASS_TEACHER_ID"); ?></td>
						</tr>
						<tr>
							<td>&nbsp;<br><br></td>
							<td>
								<input type="submit" class="waButton" value="Save">&nbsp;&nbsp;&nbsp;&nbsp;
								<?php
								if ($cid > 0) { ?>
									<a href="edit_class.php?cid=0&id=<?php echo $GLOBALS["SESSION_ID"]; ?>" class="waButton">Create Another Class</a>
								<?php
								}
								?>
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
	<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/7.3/highlight.min.js"></script>
	<script src="../js/parsley.min.js"></script>
	<?php require '../includes/empty_footer.php';   ?>
	<script type="text/javascript">
		$(function() {
			$('#classeditor').parsley().on('field:validated', function() {
					var ok = $('.parsley-error').length === 0;

				})
				.on('form:submit', function() {
					//return false; // Don't submit form for this demo
				});
		});
	</script>
</body>

</html>
