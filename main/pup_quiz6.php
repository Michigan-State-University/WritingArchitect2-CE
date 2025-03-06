<!DOCTYPE html>
<?php
include_once '../includes/Database.php';
include_once '../includes/WA_Accounts.php';
include_once '../includes/WA_Security.php';
include_once '../includes/WA_Quiz.php';
include_once '../includes/WA_Prompts.php';
ini_set('display_errors', '1'); // DEVELOPMENT ONLY

//get incoming values
$database = new Database();
$db = $database->connect();

$secure_access = check_security($db);
$qid = isset($_REQUEST['qid']) ? $_REQUEST['qid'] : die();
$Q_TYPING = isset($_REQUEST['Q_TYPING']) ? $_REQUEST['Q_TYPING'] : die();

$quiz = new QUIZ($db);
$quiz->Q_TYPING = $Q_TYPING;
$quiz->Q_COMPLETED = 1;
$quiz->Q_GRADING_STATUS = 'Submitted';
$quiz->save_quiz($db, $qid);
$quiz->load_quiz($db, $qid);
$GLOBALS['page_title'] = "Quick Write";

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
	<style>
		#a_left {
			position: absolute;
			left: 70px;
		}

		#a_center {
			position: absolute;
			left: 50%;
			margin-left: -100px;
		}

		#a_right {
			position: absolute;
			right: 70px;
		}
	</style>

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
				<div id="quiztitle">Quiz Complete</div>
				<div id="quizheading">
					<center>Time is up! Your response has been updated.</center>
				</div>
				<div id="quizbody">
					<center>
						Good work. You are finished for today. Thank you for your participation. Please <br>remove your headphones to indicate to the teacher that you are finished.
					</center>
				</div>
				<hr>
				<br>
				<div style="width:855px;">
					<div id="a_right">
						<a href='logout.php?&id=<?php echo $GLOBALS["SESSION_ID"] ?>' class='waButton'><span>Close</span></a>
					</div>
				</div>
				<br><br><br>
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
