<!DOCTYPE html>
<?php
include_once '../includes/Database.php';
include_once '../includes/WA_Accounts.php';
include_once '../includes/WA_Security.php';
include_once '../includes/WA_Quiz.php';
include_once '../includes/WA_Functions.php';
ini_set('display_errors', '1'); // DEVELOPMENT ONLY

//get incoming values
$database = new Database();
$db = $database->connect();

$secure_access = check_security($db);
$sid = isset($_REQUEST['sid']) ? $_REQUEST['sid'] : die();

if ($GLOBALS['USER_LEVEL'] == "STUDENT") die("Access Denied");

$quiz = new QUIZ($db);
$quiz->load_quiz($db, $sid);
$quizdate = date_create($quiz->Q_START_TIME);
$acc = new Account($db);
$student_name = get_account_name($db, $quiz->Q_STUDENT_ID);
$grader_name = get_account_name($db, $quiz->Q_GRADER_ID);

$duration = date_create($quiz->Q_DURATION);
// check status to determine which buttons should be locked out
$save_disable = "";
$complete_disable = "";
if ($quiz->Q_GRADING_STATUS == "Completed") {
	$save_disable = "disabled";
	$complete_disable = "disabled";
} else {
	if ($quiz->Q_SCORING == "") $complete_disable = "disabled";
}
//echo implode(' ', $GLOBALS);
//var_dump($GLOBALS);
//echo "<pre>";print_r($GLOBALS);
$scr = $quiz->Q_SCORING;
if (is_null($quiz->Q_SCORING) != 1) {
	$scr = str_replace("'", "’", $quiz->Q_SCORING);
}
?>
<html lang="en">

<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
	<meta name="description" content="" />
	<meta name="author" content="" />
	<link rel="icon" type="image/x-icon" href="/favicon.ico" />
	<link rel="icon" type="image/x-icon" href="/favicon.ico" />
	<title>Writing Architect</title>
	<!-- Favicon-->
	<link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
	<!-- Core theme CSS (includes Bootstrap)-->
	<link href="../css/styles.css" rel="stylesheet" />
	<link href="../css/score_style.css?v=3a" rel="stylesheet" />
	<script type="text/javascript" src="../includes/scripts.js?v=4"></script>
	<script src="https://code.jquery.com/jquery-3.5.0.js"></script>
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
	</script>
</head>

<body onload="document.getElementById('essay-raw-in').focus();">
	<div class="d-flex" id="wrapper">
		<div class="container-fluid">
			<div class="container-fluid row">
				<div class="container-fluid student">
					<h1><?php echo date_format($quizdate, "m/d/Y") ?> — <?php echo $quiz->Q_PROMPT_TITLE ?></h1>
				</div>
				<hr>
				<div class="container-fluid nav-header">
					<a href="score_quiz.php?sid=<?php echo $quiz->Q_ID ?>&id=<?php echo $GLOBALS["SESSION_ID"]; ?>" class="tab-current">Essay Response</a>
					<a href="score_typing.php?sid=<?php echo $quiz->Q_ID ?>&id=<?php echo $GLOBALS["SESSION_ID"]; ?>">Typing Test</a>
					<a href="score_tide.php?sid=<?php echo $quiz->Q_ID ?>&id=<?php echo $GLOBALS["SESSION_ID"]; ?>" class="tab-current">TIDE</a>
					<a href="https://writing-architect.netlify.app/#/introduction" class="scoring-docs" target="_blank">Scoring Docs</a>
				</div>
				<hr>
				<div class="col-12 col-xl-6">
					<h2>Essay Response</h2>
					<input type="hidden" id="Q_ID" name="Q_ID" value="<?php echo $quiz->Q_ID ?>">
					<input type="hidden" id="Q_GRADER_ID" name="Q_GRADER_ID" value="<?php echo $GLOBALS['USER_CODE'] ?>">
					<textarea class="essay-raw" id="essay-raw-in" spellcheck="true" onfocus="this.blur()"><?php echo $quiz->Q_ESSAY ?></textarea>
					<hr style="transform: translate(0,-5px)">
					<div class="sc-scoring container">
						<div class="row">
							<fieldset id="fs-counts" class="col">
								<legend class="hor-legend">COUNTS</legend>
								<label for="essay-duration">Duration</label>
								<span id="essay-duration"><?php echo $duration->format('i:s') ?></span><br>
								<label for="essay-words">Word</label>
								<span id="essay-words"></span><br>
								<label for="essay-sentences">Sentence</label>
								<span id="essay-sentences"></span><br>
								<label for="sc-c">^ - Correct</label>
								<span id="sc-c"></span><br>
								<label for="sc-we">Ⓦ - Word Flag</label>
								<span id="sc-we"></span><br>
								<label for="sc-se">Ⓢ - Sentence Flag</label>
								<span id="sc-se"></span><br>
								<label for="sc-s-inacc">Ⓢ - Inaccurate</label>
								<span id="sc-s-inacc"></span><br>
								<label for="sc-s-overlap">Ⓢ - Overlap</label>
								<span id="sc-s-overlap"></span><br>
								<label for="sc-s-nmae">Ⓢ - NMAE</label>
								<span id="sc-s-nmae"></span><br>
							</fieldset>
							<fieldset id="fs-score" class="col">
								<legend class="hor-legend">SCORE</legend>
								<label for="sc-ciws">CIWS</label>
								<span id="sc-ciws"></span><br>
								<label for="sc-w-acc">Word Accuracy</label>
								<span id="sc-w-acc">##</span><br>
								<label for="sc-w-acc">Sentence Accuracy</label>
								<span id="sc-s-acc">##</span><br>
								<label for="essay-words">Word Complexity</label>
								<span id="essay-wordsGTE7"></span><br>
								<label for="essay-words">Sentence Complexity</label>
								<span id="essay-sent-complex">##</span><br>
								<label for="planning" style="color:red;">Planning</label>
								<span id="planning"><?php create_drop_menu_small($db, "PLANNING", $quiz->Q_PLANNING, "Q_PLANNING"); ?></span><br>
							</fieldset>
						</div>
						<fieldset id="fs-notes" class="row">
							<legend class="hor-legend">NOTES</legend>
							<textarea id="notes" style="width:100%" rows="7"><?php echo $quiz->Q_ESSAY_NOTES ?></textarea>
						</fieldset>
					</div>
				</div>
				<!-- rightside -->
				<div class="col-12 col-xl-6">
					<div class="row">
						<div class="col col-2">
							<h2>Scoring</h2>
						</div>
						<div class="col btn-undo d-flex justify-content-end">
							<button class="btn btn-danger" onclick="resetAll()" disabled="true" id="reset-all">Reset All</button>
							<button class="btn btn-warning" onclick="resetSelected()" id="reset-selected" disabled="true">Reset Selected</button>
							<button class="btn btn-warning" onclick="undoStack()" id="undo" disabled="true">Undo</button>
						</div>
					</div>
					<div id="essay-scoring" class="essay-scoring">
					</div>
					<!-- scoring buttons -->
					<hr>
					<fieldset id="fs-word" style="white-space: nowrap;">
						<legend class="vert-legend">WORD</legend>
					</fieldset>
					<fieldset id="fs-sentence" style="white-space: nowrap;">
						<legend class="vert-legend">SENTENCE</legend>
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
							<button id="btn-save" class="btn btn-warning" onclick="save_scoring2(false)" <?php echo $save_disable ?>>Save</button>
							<button id="btn-sign" class="btn btn-success" onclick="save_scoring(true)" <?php echo $complete_disable ?>>Complete</button>
						</div>
					</fieldset>
				</div>
			</div>
			<script>
				loadResponse2('<?php echo $scr ?>');
				countInput();
				countScores();
				makeScoreBtns('cap_missing,spell_error,spell_seq', 'fs-word');
				makeScoreBtns('inacc_missing,inacc_incorrect,inacc_seq,overlap_fit,overlap_seq,nmae_trans,nmae_seq', 'fs-sentence');
			</script>
		</div>
	</div>
	<!-- Bootstrap core JS-->
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js"></script>
	<!-- Core theme JS-->
	<script src="../js/scripts.js"></script>
	<?php require '../includes/empty_footer.php';   ?>
</body>

</html>