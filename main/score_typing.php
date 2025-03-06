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
$sid = isset($_REQUEST['sid']) ? $_REQUEST['sid'] : die();

if ($GLOBALS['USER_LEVEL'] == "STUDENT") die("Access denied.");

$quiz = new QUIZ($db);
$quiz->load_quiz($db, $sid);
$quizdate = date_create($quiz->Q_START_TIME);
$acc = new Account($db);
$student_name = get_account_name($db, $quiz->Q_STUDENT_ID);
$grader_name = get_account_name($db, $quiz->Q_GRADER_ID);

$typing_length = strlen($quiz->Q_TYPING);

// check status to determine which buttons should be locked out
$save_disable = "";
$complete_disable = "";
if ($quiz->Q_GRADING_STATUS == "Completed") {
	$save_disable = "disabled";
	$complete_disable = "disabled";
} else {
	if ($quiz->Q_SCORING == "") $complete_disable = "disabled";
}

?>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
	<link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
	<title>Typing</title>
	<link href="../css/styles.css" rel="stylesheet" />
	<link href="../css/score_style.css" rel="stylesheet" />
	<script type="text/javascript" src="../includes/scripts.js"></script>
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

		function save_typing(quiz_completed) {
			// get payload
			q_id = document.getElementById("Q_ID").value;
			q_typing_notes = document.getElementById("notes").value;
			q_typing_chars = document.getElementById("count-response-char").innerHTML;
			q_typing_words = document.getElementById("count-response-word").innerHTML;
			q_typing_correct = document.getElementById("count-correct").value;
			q_grader_id = document.getElementById("Q_GRADER_ID").value;
			if (quiz_completed) q_grading_status = "Completed";
			else q_grading_status = "In Progress";

			// Get ?id from query string
			let sessionID = new URLSearchParams(window.location.search).get("id");

			$.ajax({
				url: '../ajax/save_typing_grade.php?id=' + sessionID,
				type: "POST",
				//  dataType: 'jsonp',
				data: ({
					Q_ID: q_id,
					Q_TYPING_NOTES: q_typing_notes,
					Q_TYPING_CHARS: q_typing_chars,
					Q_TYPING_WORDS: q_typing_words,
					Q_TYPING_CORRECT: q_typing_correct,
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

		function update_correct() {
			var incorrect = document.getElementById("count-incorrect").value;
			var num_incorrect = parseInt(incorrect);
			if (incorrect == "") num_incorrect = 0;
			var typedwords = document.getElementById("wordlen").value;
			var num_typedwords = parseInt(typedwords);

			document.getElementById("count-correct").value = num_typedwords - num_incorrect;
			//		document.getElementById("correct_word_display").innerHTML = num_typedwords - num_incorrect;
		}
	</script>
</head>

<body onload="update_correct()" class="container-fluid col-lg-10" oncopy="return false" oncut="return false" onpaste="return false">
	<div class="container-fluid row">
		<div class="container-fluid student">
			<h1><?php echo date_format($quizdate, "m/d/Y") ?> — <?php echo $quiz->Q_PROMPT_TITLE ?></h1>
		</div>
		<hr>
		<div class="container-fluid nav-header">
			<a href="score_quiz.php?sid=<?php echo $quiz->Q_ID ?>&id=<?php echo $GLOBALS["SESSION_ID"]; ?>">Essay Response</a>
			<a href="score_typing.php?sid=<?php echo $quiz->Q_ID ?>&id=<?php echo $GLOBALS["SESSION_ID"]; ?>" class="tab-current">Typing Test</a>
			<a href="score_tide.php?sid=<?php echo $quiz->Q_ID ?>&id=<?php echo $GLOBALS["SESSION_ID"]; ?>" class="tab-current">TIDE</a>
			<a href="https://writing-architect.netlify.app/#/introduction" class="scoring-docs" target="_blank">Scoring Docs</a>
		</div>
		<hr>
		<!-- left side -->
		<div class="container-fluid row">
			<div class="col-12 col-lg-6">
				<input type="hidden" id="Q_ID" name="Q_ID" value="<?php echo $quiz->Q_ID ?>">
				<input type="hidden" id="Q_GRADER_ID" name="Q_GRADER_ID" value="<?php echo $GLOBALS['USER_CODE'] ?>">
				<h2 class="page-title">Typing Response</h2>
				<textarea id="copy-response" rows="10" onkeyup="scoreTyping()" readonly><?php echo $quiz->Q_TYPING ?></textarea>
				<hr style="transform: translate(0,-5px)">
				<fieldset id="fs-typing-notes">
					<legend class="hor-legend">NOTES</legend>
					<textarea id="notes" style="width:100%" rows="7"><?php echo $quiz->Q_TYPING_NOTES ?></textarea>
				</fieldset>
			</div>
			<!-- right side -->
			<div class="col-12 col-lg-6 typing-scoring">
				<h2 class="page-title">Source Text</h2>
				<div class="container-fluid" id="copy-passage">
					<!-- copy passage -->
				</div>
				<hr>
				<fieldset id="fs-typing">
					<legend class="hor-legend">COUNTS</legend>
					<label for=count-response> </label>
					<em>Char</em>
					<em>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Word</em>
					<br>
					<label for=count-source>count - source</label>
					<span type="text" id="count-source-char" name="count-source-char"></span>
					<span type="text" id="count-source-word" name="count-source-word"></span>
					<br>
					<label for=count-response>count - response</label>
					<span type="text" id="count-response-char" name="count-response-char"></span>
					<span type="text" id="count-response-word" name="count-response-word"></span>
					<br>

					<label for=count-correct>count - incorrect</label>
					<input type="number" min="0" max="<?php echo $typing_length ?>" id="count-incorrect" name="count-incorrect" value="<?php echo $typing_length - $quiz->Q_TYPING_CORRECT ?>" onchange="update_correct()">
					<br>
					<label for=count-correct>count - correct</label>
					<!--	<span id="correct_word_display"><?php echo $quiz->Q_TYPING_CORRECT ?></span>
				<input type="hidden" id="count-correct" name="count-correct" value="<?php echo $quiz->Q_TYPING_CORRECT ?>"> -->
					<input type="hidden" id="wordlen" name="wordlen" value="<?php echo $typing_length ?>">
					<input type="text" id="count-correct" name="count-correct" value="<?php echo $quiz->Q_TYPING_CORRECT ?>" readonly>


				</fieldset>
			</div>

			<!-- full width bottom -->
			<div class="container-fluid">
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
						<button id="btn-save" class="btn btn-warning" onclick="save_typing(false)" <?php echo $save_disable ?>>Save</button>
						<button id="btn-sign" class="btn btn-success" <?php echo $complete_disable ?>>Complete</button>
					</div>
				</fieldset>
			</div>
		</div>
		<?php require '../includes/empty_footer.php';   ?>

</body>
<script>
	loadPassage();
	scoreTyping();
</script>

</html>