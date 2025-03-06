<!DOCTYPE html>
<?php
include_once '../includes/Database.php';
include_once '../includes/WA_Accounts.php';
include_once '../includes/WA_Security.php';
include_once '../includes/WA_Quiz.php';
ini_set('display_errors', '1'); // DEVELOPMENT ONLY

//get incoming values
$database = new Database();
$db = $database->connect();

$secure_access = check_security($db);
$GLOBALS['page_title'] = "Scoring";
$quiz = new QUIZ($db);

if ($GLOBALS['USER_LEVEL'] != "ADMIN") die("Access Denied");

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
	<script src="https://code.jquery.com/jquery-3.5.0.js?v=1"></script>
	<style>
		.gray {
			color: darkgray;
		}
	</style>

	<script language="javascript">
		function duplicate_quiz(qid) {
			var msg_str = "Duplicate Quiz " + qid + "?";
			if (confirm(msg_str)) {
				// Get ?id from query string
				let sessionID = new URLSearchParams(window.location.search).get("id");

				$.ajax({
					url: '../ajax/duplicate_quiz.php?id=' + sessionID,
					type: "POST",
					data: ({
						Q_ID: qid
					}),
					success: function(data) {
						//	alert('error: '+data);
						location.reload();
					},
					error: function(data) {
						alert('error: ' + data.responseText);
					}
				});
			}
		}

		function complete_quiz(qid) {
			var msg_str = "Complete Quiz " + qid + "?";

			if (confirm(msg_str)) {
				// Get ?id from query string
				let sessionID = new URLSearchParams(window.location.search).get("id");

				$.ajax({
					url: '../ajax/complete_quiz.php?id=' + sessionID,
					type: "POST",
					data: ({
						Q_ID: qid
					}),
					success: function(data) {
						//	alert('error: '+data);
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
				<?php echo $quiz->load_completed_quizzes($db, $GLOBALS['USER_ORGANIZATION'], 'LIVE') ?>
			</div>
			<div class="container-fluid">
				<br>
				<?php echo $quiz->load_completed_quizzes($db, $GLOBALS['USER_ORGANIZATION'], 'PENDING') ?>
			</div>
			<div class="container-fluid">
				<br>
				<?php echo $quiz->load_completed_quizzes($db, $GLOBALS['USER_ORGANIZATION'], 'COMPLETED') ?>
			</div>
			<br><br><br>
		</div>
		<br><br><br><br>
		<!-- Bootstrap core JS-->
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js"></script>
		<!-- Core theme JS-->
		<script src="../js/scripts.js"></script>
		<?php require '../includes/empty_footer.php';   ?>
</body>

</html>
