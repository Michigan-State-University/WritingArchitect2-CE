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

$nextpageURL = "pup_quiz3.php?qid=" . $qid . "&id=" . $GLOBALS["SESSION_ID"];
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
	<script language='javascript'>
		function CountDown(duration, display) {
			if (!isNaN(duration)) {
				var timer = duration,
					minutes, seconds;

				var interVal = setInterval(function() {
					minutes = parseInt(timer / 60, 10);
					seconds = parseInt(timer % 60, 10);

					//	minutes = minutes < 10 ? "0" + minutes : minutes;
					seconds = seconds < 10 ? "0" + seconds : seconds;

					if (timer <= 180) {
						document.getElementById(display).innerHTML = minutes + ":" + seconds;


						//document.getElementById('TIMER_DISPLAY').style.display = 'block';
						// 	var audio = new Audio('https://media.geeksforgeeks.org/wp-content/uploads/20190531135120/beep.mp3');
						//	audio.autoplay  = true;
						//	audio.play();

					}
					if (--timer < 0) {
						timer = duration;
						//SubmitFunction();
						document.getElementById(display).innerHTML = "";
						clearInterval(interVal);
						alert("Planning time is complete. Click 'OK' to move on to the writing part")
						window.location.href = '<?php echo $nextpageURL; ?>';
					}
				}, 1000);
			}
		}

		CountDown(220, "TIMER_DISPLAY");
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
				<div id="quiztitle">Planning</div>
				<div id="quizheading">
					<center>Use the page in front of you to plan your response.<br>
						Remember to use the planning process that you have been taught in class.
					</center>
				</div>
				<center>
					<div id="quizbody" style="text-align: center; width:855px;">
						<?php echo $prompt->PROMPT_INSTRUCTIONS ?>
					</div>
					<div style="text-align: center; width:855px;">
						<?php if ($prompt->PROMPT_AUDIO_PROMPT !== "") { ?>
							<audio controls>
								<source src="../audio/<?php echo $prompt->PROMPT_AUDIO_PROMPT; ?>" type="audio/mp4">
								Your browser does not support the audio element.
							</audio>
						<?php } ?>
					</div>
					<hr>
					<div style="text-align: left;  width:855px;">
						<b>Remember, a well written informative paper:</b>
						<ol type="1">
							<li>has a clear main idea and stays on topic,</li>
							<li>includes a good introduction and conclusion,</li>
							<li>uses information from the article stated in your own words plus your own ideas, and</li>
							<li>follows the rules of writing.</li>
						</ol>
					</div>
					<div style="text-align: right;  width:855px;">
						<div id="TIMER_DISPLAY"></div>

					</div>
					<hr>
					<br>
					<div style="width:855px;">
						<div id="a_left">
							<a href='pup_quiz1.php?qid=<?php echo $qid ?>&id=<?php echo $GLOBALS["SESSION_ID"] ?>' class='waButton'><span>Back</span></a>
						</div>
						<div id="a_right">
							<a href='pup_quiz3.php?qid=<?php echo $qid ?>&id=<?php echo $GLOBALS["SESSION_ID"] ?>' class='waButton'><span>Start Writing Now</span></a>
						</div>
					</div>
					<br><br><br><br>
				</center>
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