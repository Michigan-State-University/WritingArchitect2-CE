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
				<div id="quiztitle">Passage</div>

				<center>
					<div id="quizbody" style="text-align: center; width:855px;">
						Listen and follow along on the page in front of you or on the computer screen as <b><i><?php echo $prompt->PROMPT_TITLE ?></i></b> is read.
					</div>
					<div style="text-align: center; width:855px;">
						<?php if ($prompt->PROMPT_AUDIO_PASSAGE !== "") { ?>
							<audio controls>
								<source src="../audio/<?php echo $prompt->PROMPT_AUDIO_PASSAGE; ?>" type="audio/mp3">
								Your browser does not support the audio element.
							</audio>
						<?php } ?>
					</div>
					<div style="text-align: left;  width:855px;">
						<?php echo str_replace(["\r"], "<br>", $prompt->PROMPT_BODY); ?>
					</div>
					<br><br>
					<div style="text-align: center; width:855px;"><a href='pup_quiz2.php?qid=<?php echo $qid ?>&id=<?php echo $GLOBALS["SESSION_ID"] ?>' class='waButtonLarge'><span>Begin Planning</span></a></div>
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