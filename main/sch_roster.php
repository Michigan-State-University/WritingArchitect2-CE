<?php
ini_set('display_errors', '0');

include_once '../includes/Database.php';
include_once '../includes/WA_Accounts.php';
include_once '../includes/WA_Security.php';
include_once '../includes/WA_Classes.php';
include_once '../includes/WA_Quiz.php';
include_once '../includes/QuickWriteAssignment.php';

//get incoming values
$database = new Database();
$db = $database->connect();

$secure_access = check_security($db);
if ($GLOBALS['USER_LEVEL'] == "STUDENT") die("Access denied.");

$actor = [
	'user_code' => $GLOBALS['USER_CODE'],
	'user_level' => $GLOBALS['USER_LEVEL'],
	'school_id' => $GLOBALS['USER_SCHOOL_SN'],
];
$assignment = new QuickWriteAssignment($db);
try {
	$cid = QuickWriteAssignment::parseClassId(isset($_GET['cid']) ? $_GET['cid'] : null);
	$assignment->assertCanAccessClass($cid, $actor);
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		QuickWriteAssignment::assertCsrfToken(
			isset($_POST['csrf_token']) ? $_POST['csrf_token'] : null,
			(string) $GLOBALS['SESSION_ID'],
			(string) $GLOBALS['USER_CODE']
		);
		$studentIds = QuickWriteAssignment::parseStudentIds($_POST);
		$templateTitle = isset($_POST['QTS']) ? (string) $_POST['QTS'] : '';
		$assignment->assign($cid, $studentIds, $templateTitle, $actor);
	}
} catch (QuickWriteAssignmentAuthorizationException $exception) {
	http_response_code(403);
	die('Access denied.');
} catch (InvalidArgumentException $exception) {
	http_response_code(400);
	die(htmlspecialchars($exception->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
} catch (Throwable $exception) {
	error_log('Quick-write assignment failed: ' . $exception->getMessage());
	http_response_code(500);
	die('Unable to process the quick-write assignment.');
}

$GLOBALS['page_title'] = "Class Roster";
?>
<!DOCTYPE html>
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
		function delete_record(user_id) {
			var msg_str = "Delete selected user (account ID " + user_id + ")?";
			if (confirm(msg_str)) {
				// Get ?id from query string
				let sessionID = new URLSearchParams(window.location.search).get("id");

				$.ajax({
					url: '../ajax/universal_delete.php?id=' + sessionID,
					type: "POST",
					data: ({
						TYPE: 'user',
						VALUE: user_id
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
