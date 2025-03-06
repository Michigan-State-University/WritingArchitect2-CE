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

$quiz = new QUIZ($db);
$quiz->load_quiz($db, $qid);

$prompt = new Prompts($db);
$result = $prompt->load_prompt($db, $quiz->Q_PROMPT_ID);
$nextpageURL = "pup_quiz4.php?qid=" . $qid . "&id=" . $GLOBALS["SESSION_ID"];
$GLOBALS['page_title'] = "Quick Write";

//get_starttime
$starttime = date('Y-m-d H:i:s');

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

		.mpopup {
			display: none;
			position: fixed;
			z-index: 1;
			padding-top: 100px;
			left: 0;
			top: 0;
			width: 100%;
			height: 100%;
			overflow: auto;
			background-color: rgb(0, 0, 0);
			background-color: rgba(0, 0, 0, 0.4);
		}

		.modal-content {
			position: relative;
			background-color: #fff;
			margin: auto;
			padding: 0;
			width: 450px;
			box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
			-webkit-animation-name: animatetop;
			-webkit-animation-duration: 0.4s;
			animation-name: animatetop;
			animation-duration: 0.4s;
			border-radius: 0.3rem;
		}

		.modal-header {
			padding: 2px 12px;
			background-color: #ffffff;
			color: #333;
			border-bottom: 1px solid #e9ecef;
			border-top-left-radius: 0.3rem;
			border-top-right-radius: 0.3rem;
		}

		.modal-header h2 {
			font-size: 1.25rem;
			margin-top: 14px;
			margin-bottom: 14px;
		}

		.modal-body {
			padding: 2px 12px;
		}

		.modal-footer {
			padding: 1rem;
			background-color: #ffffff;
			color: #333;
			border-top: 1px solid #e9ecef;
			border-bottom-left-radius: 0.3rem;
			border-bottom-right-radius: 0.3rem;
			text-align: right;
		}

		.close {
			color: #888;
			float: right;
			font-size: 28px;
			font-weight: bold;
		}

		.close:hover,
		.close:focus {
			color: #000;
			text-decoration: none;
			cursor: pointer;
		}

		/* add animation effects */
		@-webkit-keyframes animatetop {
			from {
				top: -300px;
				opacity: 0
			}

			to {
				top: 0;
				opacity: 1
			}
		}

		@keyframes animatetop {
			from {
				top: -300px;
				opacity: 0
			}

			to {
				top: 0;
				opacity: 1
			}
		}
	</style>
	<script language='javascript'>
		function CountDown(duration, display) {
			if (!isNaN(duration)) {
				var timer = duration,
					minutes, seconds;

				var interVal = setInterval(function() {
					minutes = parseInt(timer / 60, 10);
					seconds = parseInt(timer % 60, 10);
					seconds = seconds < 10 ? "0" + seconds : seconds;

					if (timer <= 60) {
						document.getElementById(display).innerHTML = minutes + ":" + seconds;
					}
					if (--timer < 0) {
						timer = duration;
						//SubmitFunction();
						document.getElementById('TIMER_DISPLAY').innerHTML = "";
						clearInterval(interVal);
						document.getElementById('mpopupBox').style.display = "block";
						//	document.getElementById("ESSAY").submit();
					}
				}, 1000);
			}
		}

		CountDown(900, "TIMER_DISPLAY");

		$(document).ready(function() {
			//	$('#Q_ESSAY').bind('cut copy paste', function(event) {
			//		event.preventDefault();
			//	});
		});

		// Select modal
		var mpopup = document.getElementById('mpopupBox');

		// Select trigger link
		var mpLink = document.getElementById("mpopupLink");

		// Select close action element
		var close = document.getElementsByClassName("close")[0];

		function save() {
			document.getElementById("ESSAY").submit();
		}
	</script>
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
				<div id="quiztitle">Writing</div>
				<div id="quizheading">
					<center><?php echo $prompt->PROMPT_TITLE ?><br>
						Type your response in the text box below. Writing time will automatically end after 15 minutes.
					</center>
				</div>
				<center><br><br>
					<form action="pup_quiz4.php?id=<?php echo $GLOBALS["SESSION_ID"]; ?>" method="post" id="ESSAY" name="ESSAY">

						<input id="qid" type="hidden" name="qid" value="<?php echo $qid ?>">
						<input id="Q_START_TIME" type="hidden" name="Q_START_TIME" value="<?php echo $starttime ?>">
						<div style="text-align: center; width:855px;">
							<textarea id="Q_ESSAY" class="form-control" name="Q_ESSAY" rows="20" cols="120" spellcheck="false"></textarea>
						</div>
						<br>
						<div style="text-align: right;  width:855px;">
							<div id="TIMER_DISPLAY"></div>
						</div>
						<hr>
						<br>

						<div style="width:855px;">
							<div id="a_right">
								<input type="submit" class="waButton" value="Submit Early">
							</div>
						</div>
						<br><br><br><br>
					</form>
				</center>
			</div>
		</div>
	</div>
	<!-- Bootstrap core JS-->
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js"></script>
	<!-- Core theme JS-->
	<script src="../js/scripts.js"></script>
	<?php require '../includes/empty_footer.php';   ?>
	<!-- Modal popup box -->
	<div id="mpopupBox" class="mpopup">
		<!-- Modal content -->
		<div class="modal-content">
			<div class="modal-body">
				<p>Time is up! Click button to to start the tying test.</p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" onclick="save();">Proceed to Typing Test</button>
			</div>
		</div>
	</div>
</body>

</html>