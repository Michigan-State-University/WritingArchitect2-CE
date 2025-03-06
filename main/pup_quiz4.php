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
$Q_ESSAY = isset($_REQUEST['Q_ESSAY']) ? $_REQUEST['Q_ESSAY'] : die();
$Q_START_TIME = isset($_REQUEST['Q_START_TIME']) ? $_REQUEST['Q_START_TIME'] : die();

$quiz = new QUIZ($db);
$quiz->Q_ESSAY = $Q_ESSAY;
$quiz->Q_START_TIME = $Q_START_TIME;
$endtime = date('Y-m-d H:i:s');
$quiz->Q_END_TIME = $endtime;
$quiz->save_quiz($db, $qid);


$quiz->save_quiz($db, $qid);
$quiz->load_quiz($db, $qid);

$prompt = new Prompts($db);
$result = $prompt->load_prompt($db, $quiz->Q_PROMPT_ID);
$GLOBALS['page_title'] = "Quick Write";

//$nextpageURL = "pup_quiz4.php?qid=".$quiz->Q_ID."&id=".$GLOBALS["SESSION_ID"];

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

		#TIMER_DISPLAY {
			background-color: #E0C905;
			font-family: "Arial Black", Gadget, sans-serif;
			font-size: 28px;
			font-weight: 700;
			color: #000000;
			display: inline-block;
			height: 52px;
			padding: 4px;
			width: 90px;
		}
	</style>
</head>

<body oncontextmenu="return false">
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
				<div id="quiztitle">Typing Test Instructions</div>
				<div id="quizheading">
					<center>The next page will test your typing skills.</center>
				</div>

				<div id="quizbody">
					<center>You will copy a paragraph as quickly and accurately as you<br>can. After 90 seconds, the exercise will end.</center>
				</div>

				<hr>
				<br>
				<div style="width:855px;">
					<div id="a_right">
						<a href='pup_quiz5.php?qid=<?php echo $qid ?>&id=<?php echo $GLOBALS["SESSION_ID"] ?>' class='waButton'><span>Start Typing Test</span></a>
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