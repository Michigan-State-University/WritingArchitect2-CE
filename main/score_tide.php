<!DOCTYPE html>
<?php
include_once '../includes/Database.php';
include_once '../includes/WA_Accounts.php';
include_once '../includes/WA_Security.php';
include_once '../includes/WA_Quiz_TIDE.php';
ini_set('display_errors', '1'); // DEVELOPMENT ONLY

//get incoming values
$database = new Database();
$db = $database->connect();

$secure_access = check_security($db);
$sid = isset($_REQUEST['sid']) ? $_REQUEST['sid'] : die();

if ($GLOBALS['USER_LEVEL'] == "STUDENT") die("Access denied.");

$quiz = new QUIZ($db);
$quiz->load_quiz($db, $sid);
$quizdate = date_create($quiz->Q_START_TIME);
$acc = new Account($db);
$student_name = get_account_name($db, $quiz->Q_STUDENT_ID);
$grader_name = get_account_name($db, $quiz->Q_GRADER_ID);

// If Q_TIDE is null or empty, set it to Q_ESSAY
if (!$quiz->Q_TIDE or (strlen($quiz->Q_TIDE) == 0)) $quiz->Q_TIDE = $quiz->Q_ESSAY;
$bodytag = $quiz->Q_TIDE;

//	$bodyslash = addslashes($bodytag);
//	$scoreslash = addslashes($quiz->Q_TIDE_SCORING);

// check status to determine which buttons should be locked out
$save_disable = "";
$complete_disable = "";
if ($quiz->Q_GRADING_STATUS == "Completed") {
	$save_disable = "disabled";
	$complete_disable = "disabled";
}

?>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
	<link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
	<title>TIDE</title>
	<link href="../css/styles.css" rel="stylesheet" />
	<link href="../css/tide_style.css" rel="stylesheet" />
	<link href="../css/score_style.css" rel="stylesheet" />
	<script type="text/javascript" src="../includes/tide_script.js?v=6f"></script>
	<script src="https://code.jquery.com/jquery-3.5.0.js?v=1"></script>
	<style>
		#footer {
			background: #FFF;
			position: fixed;
			bottom: 0;
			left: 0;
			height: 20px;
			width: 100%;
			font-family: sans-serif;
			font-size: 11px;
			text-align: center;
			padding-top: 4px;
		}

		.popup {
			position: relative;
			z-index: 999;
			width: 150px;
			height: 30px;
			font-family: Verdana, Arial, Helvetica, sans-serif;
			text-align: left;
			font-size: 15px;
			;
			color: black;
			background-color: #48D0B2;
			padding-top: 5px;
			display: none;
			margin: 2px 0px 0px 0px;
		}
	</style>
	<script language="javascript">
		$(document).ready(function() {
			$("#CTL_MESSAGE").hide(); // hide the save message
		});

		function loadResponse2(scoredata) {
			if (scoredata == "") parseInput();
			else document.getElementById("essay-scoring").innerHTML = scoredata;
		}

		function save_tide(quiz_completed) {
			// get payload
			q_id = document.getElementById("Q_ID").value;
			q_grader_id = document.getElementById("Q_GRADER_ID").value;
			if (quiz_completed) q_grading_status = "Completed";
			else q_grading_status = "In Progress";


			q_tide_t = "";
			q_tide_i = "";
			q_tide_d = "";
			q_tide_e = "";
			q_tide_c = "";
			q_tide_scoring = localStorage.getItem("scoring");
			q_tide = document.getElementById("sourceText").innerHTML;

			scr = updateTotals();
			for (var key in scr) {
				var value = scr[key];
				switch (key) {
					case "T":
						q_tide_t = value;
						break;
					case "I":
						q_tide_i = value;
						break;
					case "D":
						q_tide_d = value;
						break;
					case "E":
						q_tide_e = value;
						break;
					case "C":
						q_tide_c = value;
						break;
				}
			}

			// Get ?id from query string
			let sessionID = new URLSearchParams(window.location.search).get("id");

			$.ajax({
				url: '../ajax/save_TIDE_grade.php?id=' + sessionID,
				type: "POST",
				//  dataType: 'jsonp',
				data: ({
					Q_ID: q_id,
					Q_TIDE: q_tide,
					Q_TIDE_SCORING: q_tide_scoring,
					Q_TIDE_T: q_tide_t,
					Q_TIDE_I: q_tide_i,
					Q_TIDE_D: q_tide_d,
					Q_TIDE_E: q_tide_e,
					Q_TIDE_C: q_tide_c,
					Q_GRADER_ID: q_grader_id,
					Q_GRADING_STATUS: q_grading_status
				}),
				success: function(data) {
					// update SCORED BY Section
					items = data.split("|");
					document.getElementById("scorer").innerHTML = items[0];
					document.getElementById("score-status").innerHTML = items[1];
					document.getElementById("score-timestamp").innerHTML = items[2];

					$("#CTL_MESSAGE").show();
					setTimeout(function() {
						$("#CTL_MESSAGE").hide();
					}, 3800);
				},
				error: function(data) {
					alert('error: ' + data.responseText);
				}
			});
		}
	</script>
</head>

<body onload="reset(true, <?php echo $quiz->Q_ID ?>)">
	<input type="hidden" id="Q_ID" name="Q_ID" value="<?php echo $quiz->Q_ID ?>">
	<input type="hidden" id="Q_GRADER_ID" name="Q_GRADER_ID" value="<?php echo $GLOBALS['USER_CODE'] ?>">
	<div class='container-fluid'>
		<div class="container-fluid student">
			<h1><?php echo date_format($quizdate, "m/d/Y") ?> — <?php echo $quiz->Q_PROMPT_TITLE ?></h1>
		</div>
		<hr>
		<div class="container-fluid nav-header">
			<a href="score_quiz.php?sid=<?php echo $quiz->Q_ID ?>&id=<?php echo $GLOBALS["SESSION_ID"]; ?>">Essay Response</a>
			<a href="score_typing.php?sid=<?php echo $quiz->Q_ID ?>&id=<?php echo $GLOBALS["SESSION_ID"]; ?>">Typing Test</a>
			<a href="score_tide.php?sid=<?php echo $quiz->Q_ID ?>&id=<?php echo $GLOBALS["SESSION_ID"]; ?>" class="tab-current">TIDE</a>
			<a href="https://writing-architect.netlify.app/#/introduction" class="scoring-docs" target="_blank">Scoring Docs</a>
		</div>
		<div style="margin-top:-16px;">
			<hr>
		</div>
		<div class="container-md row">
			<div id='buttonbar' class="container-fluid col-6">
				<button id='T' title="TOPIC" onClick='tideSelect(this.id)' style='background:#ffadad; opacity: 0.5;'>T</button>
				<button id='I' title="IDEA" onClick='tideSelect(this.id)' style='background:#fdffb6; opacity: 0.5'>I</button>
				<button id='D' title="DETAIL" onClick='tideSelect(this.id)' style='background:#caffbf; opacity: 0.5'>D</button>
				<button id='E' title="ENDING" onClick='tideSelect(this.id)' style='background:#9bf6ff; opacity: 0.5'>E</button>
				<button id='C' title="COPY" onClick='tideSelect(this.id)' style='background:#afafaf; opacity: 0.5; text-decoration: underline;'>C</button>
				<button id='reset' onClick='reset(false, <?php echo $quiz->Q_ID ?>)'>Reset</button>
				<div class='container-fluid row'>
					<span id='selectedTIDE'>Select a TIDE scoring type</span>
				</div>
			</div>

			<div class="container-fluid col-6">
				<table id="scoreTotals" class="table table-bordered table-striped"></table>
			</div>

			<hr>
			<div id='selection'><em>Click a TIDE scoring type, then drag to select one or more words.</em></div>
			<div id='sourceText'><?php echo $bodytag; ?></div>
			<br><br>
			<hr>
			<fieldset id="fs-scored-by" class="row">
				<legend class="hor-legend">SCORED BY</legend>
				<div class="col-9">
					<label for="scorer">Scorer</label>
					<code id="scorer"><?php echo $grader_name ?></code>
					<label for="score-status">Grading Status</label>
					<code id="score-status"><?php echo $quiz->Q_GRADING_STATUS ?></code>
					<label for="score-timestamp">Last saved</label>
					<code id="score-timestamp"><?php echo $quiz->Q_MODIFIED_ON ?></code>
					<span class="popup" id="CTL_MESSAGE">Quiz Saved</span>
				</div>
				<div class="col-3 save-btns">
					<button id="btn-save" class="btn btn-warning" onclick="save_tide(false)">Save</button>
					<button id="btn-sign" class="btn btn-success" onclick="save_tide(true)" disabled="">Complete</button>
				</div>
			</fieldset>


			<table id="display_json_data" class="table table-bordered table-striped"></table>
		</div>
	</div>
</body>
<?php require '../includes/empty_footer.php';   ?>

</html>
