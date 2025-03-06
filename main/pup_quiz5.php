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
					document.getElementById(display).innerHTML = minutes + ":" + seconds;

					if (--timer < 0) {
						timer = duration;
						//SubmitFunction();
						document.getElementById('TIMER_DISPLAY').innerHTML = "";
						clearInterval(interVal);
						document.getElementById("TYPING").submit();
					}
				}, 1000);
			}
		}

		CountDown(90, "TIMER_DISPLAY");

		$(document).ready(function() {
			$('#Q_TYPING').bind('cut copy paste', function(event) {
				event.preventDefault();
			});
		});
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
				<div id="quiztitle">Typing Test</div>
				<div id="quizheading">
					<center>Copy the paragraph</center>
				</div>
				<center>
					<form action="pup_quiz6.php?id=<?php echo $GLOBALS["SESSION_ID"]; ?>" method="post" id="TYPING" name="TYPING">
						<input id="qid" type="hidden" name="qid" value="<?php echo $qid ?>">
						<div style="width:855px;">
							<table cellpadding="5">
								<tr>
									<td align="right" valign="top"><textarea id="Q_TYPING" class="form-control" name="Q_TYPING" rows="14" cols="58" spellcheck="false"></textarea></td>
									</td>
									<td align="left" width="50%" valign="top">A little boy lived with his father in a large forest. Every day the father went out to cut wood. One day the boy was walking through the woods with a basket of lunch for his father. Suddenly he met a huge bear. The boy was frightened, but he threw a piece of bread and jelly to the bear. The bear thought it was very kind for the boy to share his lunch. Unfortunately, the bear did not like grape jelly. The bear decided to ask the boy if he wanted to go find honey together instead. When the little boy saw the bear approach him and his father, he was frightened again. This time, the father told the son to be calm. It seemed like the bear was friendly. Together, the bear, son, and father went on a journey through the forest to find honey.</td>
								</tr>
							</table>
						</div>
						<br>
						<div style="text-align: right;  width:855px;">
							<div id="TIMER_DISPLAY"></div>
						</div>
						<hr>
						<br>
						<!--<div style="width:855px;">
								<div id="a_right">
									<input type="submit" class="waButton" value="Save">
								</div>
							</div> -->
						<br><br>
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
</body>

</html>