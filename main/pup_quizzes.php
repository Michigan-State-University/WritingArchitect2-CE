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
$GLOBALS['page_title'] = "Quick Writes";

$quiz = new QUIZ($db);
$quiz->get_quiz_counts($db, $GLOBALS['USER_CODE']);
if ($quiz->load_next_quiz($db, $GLOBALS['USER_CODE'])) {
	$next_quiz = "<table width='90%'><tr><td><b>" . $quiz->Q_PROMPT_TITLE . "</b> </td><td><a href='pup_quiz1.php?qid=" . $quiz->Q_ID . "&id=" . $GLOBALS["SESSION_ID"] . "' class='waButton'><span>Begin Quick-Write</span></a></td></tr></table> ";
	$quiz_name = $quiz->Q_PROMPT_TITLE;
} else {
	$next_quiz = "";
	$quiz_name = "";
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
	<link href="../css/WA.css" rel="stylesheet" />
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
				<div id="quiztitle">Quick-Writes</div>
				<div id="quizheading">Quick-Writes: <?php echo $quiz->QUIZZES_TAKEN ?> Taken / <?php echo $quiz->QUIZZES_REMAINING ?> Remaining</div>
				<div id="quizbody"><?php echo $next_quiz ?></div>
				<?php if ($quiz_name != "") { ?>
					<div id="instructions">
						<ul>
							<li>You will be writing about "<?php echo $quiz_name ?>" </li>
							<li>In your paper packet, turn to the page that says "<?php echo $quiz_name ?>".</li>
							<li>If you need a pencil or a pair of headphones, please go get those items now.</li>
							<li>If this is your first quick write on the computer, please watch this video on how it will work.</li>
						</ul>
					</div>
					<center>
						<iframe id="kaltura_player" src="/assets/WA_Walkthrough.mp4" frameborder="0" title="WA INSTRUCTIONAL VIDEO" width="640" height="360"></iframe>
					</center>


				<?php } ?>
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
