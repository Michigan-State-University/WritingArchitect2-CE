<!DOCTYPE html>
<?php
include_once '../includes/Database.php';
include_once '../includes/WA_Accounts.php';
include_once '../includes/WA_Security.php';
include_once '../includes/WA_Functions.php';
include_once '../includes/WA_Schools.php';
ini_set('display_errors', '1'); // DEVELOPMENT ONLY

//get incoming values
$database = new Database();
$db = $database->connect();

$secure_access = check_security($db);

$sid = isset($_REQUEST['sid']) ? $_REQUEST['sid'] : die();
$mode = isset($_POST['mode']) ? $_POST['mode'] : "";

$sch_acct = new Schools($db);
$sch_acct->SCHOOL_ID = $sid;
$sch_acct->SCHOOL_NAME = isset($_POST['SCHOOL_NAME']) ? $_POST['SCHOOL_NAME'] : "";
$sch_acct->SCHOOL_SN = isset($_POST['SCHOOL_SN']) ? $_POST['SCHOOL_SN'] : "";
$sch_acct->SCHOOL_CONTACT = isset($_POST['SCHOOL_CONTACT']) ? $_POST['SCHOOL_CONTACT'] : "";
$sch_acct->SCHOOL_STATUS = isset($_POST['SCHOOL_STATUS']) ? $_POST['SCHOOL_STATUS'] : "";

if ($GLOBALS['USER_LEVEL'] != "ADMIN") die("Access denied.");

$cntl_message = "";
if ($mode == 'SAVE') {
	$result = $sch_acct->save_school($db, $sid);
	echo $result;

	$cntl_message = "School saved.";
	if ($result != "") {
		if ($result === "NOT UNIQUE") $cntl_message = "Duplicate school name. School not saved.";
		else {
			$cntl_message = "School created.";
			$sid  = $result;
		}
	}
} else {
	$result = $sch_acct->load_school($db, $sid);
}

if ($sid == "0") $GLOBALS['page_title'] = "Create School";
else $GLOBALS['page_title'] = "Edit School";

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
				<form id="schooleditor" action="edit_school.php?id=<?php echo $GLOBALS["SESSION_ID"]; ?>" method="post" name="schooleditor" data-parsley-validate>
					<input type="hidden" id="mode" name="mode" value="SAVE">
					<input type="hidden" id="sid" name="sid" value="<?php echo $sid; ?>">
					<table border="0" cellspacing="4" cellpadding="2">
						<tr>
							<td class="EditTitle"><label for="SCHOOL_NAME">School Name:</label></td>
							<td><input id="SCHOOL_NAME" style="width:345px;" class="form-control" type="text" name="SCHOOL_NAME" maxlength="100" value="<?php echo $sch_acct->SCHOOL_NAME; ?>" width="20" required></td>
							<td class="EditTitle"><label for="SCHOOL_SN">Abbr. Name:</label></td>
							<td><input id="SCHOOL_SN" style="width:110px;" value="<?php echo $sch_acct->SCHOOL_SN; ?>" class="form-control" type="text" name="SCHOOL_SN" maxlength="20" required></td>
						</tr>
						<tr>
							<td class="EditTitle"><label for="SCHOOL_CONTACT">Contact:</label></td>
							<td><input id="SCHOOL_CONTACT" style="width:345px;" value="<?php echo $sch_acct->SCHOOL_CONTACT; ?>" class="form-control" type="text" name="SCHOOL_CONTACT" maxlength="60" required></td>
							<td class="EditTitle"><label for="SCHOOL_STATUS">Status:</label></td>
							<td><?php create_drop_menu($db, "STATUS", $sch_acct->SCHOOL_STATUS, "SCHOOL_STATUS"); ?></td>
						</tr>
						<tr>
							<td>&nbsp;<br><br></td>
							<td>
								<input type="submit" class="waButton" value="Save">
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
			$('#schooleditor').parsley().on('field:validated', function() {
				var ok = $('.parsley-error').length === 0;
			})
		});
	</script>

</body>

</html>
