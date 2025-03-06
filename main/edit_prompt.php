<!DOCTYPE html>
<?php
include_once '../includes/Database.php';
include_once '../includes/WA_Security.php';
include_once '../includes/WA_Functions.php';
include_once '../includes/WA_Prompts.php';
ini_set('display_errors', '1'); // DEVELOPMENT ONLY

//get incoming values
$database = new Database();
$db = $database->connect();

$secure_access = check_security($db);

$pid = isset($_REQUEST['pid']) ? $_REQUEST['pid'] : die();
$mode = isset($_POST['mode']) ? $_POST['mode'] : "";

$prompt = new Prompts($db);
$prompt->PROMPT_ID = $pid;
$prompt->PROMPT_SHORT_TITLE = isset($_POST['PROMPT_SHORT_TITLE']) ? $_POST['PROMPT_SHORT_TITLE'] : "";
$prompt->PROMPT_TITLE = isset($_POST['PROMPT_TITLE']) ? $_POST['PROMPT_TITLE'] : "";
$prompt->PROMPT_BODY = isset($_POST['PROMPT_BODY']) ? $_POST['PROMPT_BODY'] : "";
$prompt->PROMPT_AUDIO_PROMPT = isset($_POST['PROMPT_AUDIO_PROMPT']) ? $_POST['PROMPT_AUDIO_PROMPT'] : "";
$prompt->PROMPT_AUDIO_PASSAGE = isset($_POST['PROMPT_AUDIO_PASSAGE']) ? $_POST['PROMPT_AUDIO_PASSAGE'] : "";
$prompt->PROMPT_SOURCE = isset($_POST['PROMPT_SOURCE']) ? $_POST['PROMPT_SOURCE'] : "";
$prompt->PROMPT_INSTRUCTIONS = isset($_POST['PROMPT_INSTRUCTIONS']) ? $_POST['PROMPT_INSTRUCTIONS'] : "";
$prompt->PROMPT_STATUS = isset($_POST['PROMPT_STATUS']) ? $_POST['PROMPT_STATUS'] : "";
$prompt->PROMPT_AUDIO_LEN = isset($_POST['PROMPT_AUDIO_LEN']) ? $_POST['PROMPT_AUDIO_LEN'] : "";

if ($GLOBALS['USER_LEVEL'] != "ADMIN" && $GLOBALS['USER_LEVEL'] != "TEACHER") die("Access Denied");

$cntl_message = "";
if ($mode == 'SAVE') {
	$result = $prompt->save_prompt($db, $pid);
	$cntl_message = "Quiz Prompt saved.";
	if ($result != "") {
		if ($result === "NOT UNIQUE") $cntl_message = "Duplicate Quiz Prompt name. Quiz Prompt not saved.";
		else {
			$cntl_message = "Quiz Prompt created.";
			$pid  = $result;
		}
	}
} else {
	$result = $prompt->load_prompt($db, $pid);
}

if ($pid == "0") $GLOBALS['page_title'] = "Create Prompt";
else $GLOBALS['page_title'] = "Edit Prompt";
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
	<link href="../css/parsley.css" rel="stylesheet" />
	<script src="https://code.jquery.com/jquery-3.5.0.js"></script>
	<script language="javascript">
		$(document).ready(function() {
			var cntl_msg = "<?php echo $cntl_message ?>";
			if (cntl_msg !== "") {
				$("#CTL_MESSAGE").show();
				setTimeout(function() {
					$("#CTL_MESSAGE").hide();
				}, 3800);
			}
		});
	</script>
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
				<br>
				<div class="popup" id="CTL_MESSAGE">&nbsp;<?php echo $cntl_message ?></div>
				<form id="prompteditor" data-validate action="edit_prompt.php?id=<?php echo $GLOBALS["SESSION_ID"]; ?>" method="post" name="prompteditor" data-parsley-validate>
					<input type="hidden" id="mode" name="mode" value="SAVE">
					<input type="hidden" id="pid" name="pid" value="<?php echo $pid; ?>">
					<table border="0" cellspacing="4" cellpadding="2">
						<tr>
							<td class="EditTitle"><label for="PROMPT_SHORT_TITLE">Short Name:</label></td>
							<td><input id="PROMPT_SHORT_TITLE" class="form-control" type="text" name="PROMPT_SHORT_TITLE" maxlength="40" value="<?php echo $prompt->PROMPT_SHORT_TITLE; ?>" width="40" required></td>
							<td class="EditTitle"><label for="PROMPT_STATUS">Status:</label></td>
							<td><?php create_drop_menu($db, "STATUS", $prompt->PROMPT_STATUS, "PROMPT_STATUS"); ?></td>
						</tr>
						<tr>
							<td class="EditTitle"><label for="PROMPT_TITLE">Title:</label></td>
							<td><input id="PROMPT_TITLE" value="<?php echo $prompt->PROMPT_TITLE; ?>" class="form-control" type="text" name="PROMPT_TITLE" maxlength="200" required></td>
							<td class="EditTitle"><label for="PROMPT_SOURCE">Source:</label></td>
							<td><input id="PROMPT_SOURCE" value="<?php echo $prompt->PROMPT_SOURCE; ?>" class="form-control" type="text" name="PROMPT_SOURCE" maxlength="100" required></td>
						</tr>
						<tr>
							<td class="EditTitle"><label for="PROMPT_AUDIO_PROMPT">Prompt:</label></td>
							<td><input id="PROMPT_AUDIO_PROMPT" value="<?php echo $prompt->PROMPT_AUDIO_PROMPT; ?>" class="form-control" type="text" name="PROMPT_AUDIO_PROMPT" maxlength="100"></td>
							<td colspan="2"><?php if ($prompt->PROMPT_AUDIO_PROMPT !== "") { ?>
									<audio controls>
										<source src="../audio/<?php echo $prompt->PROMPT_AUDIO_PROMPT; ?>" type="audio/mp3">
										Your browser does not support the audio element.
									</audio>
								<?php } ?>
							</td>
						</tr>
						<tr>
							<td class="EditTitle"><label for="PROMPT_AUDIO_PASSAGE">Passage:</label></td>
							<td><input id="PROMPT_AUDIO_PASSAGE" value="<?php echo $prompt->PROMPT_AUDIO_PASSAGE; ?>" class="form-control" type="text" name="PROMPT_AUDIO_PASSAGE" maxlength="100"></td>
							<td colspan="2"><?php if ($prompt->PROMPT_AUDIO_PASSAGE !== "") { ?>
									<audio controls>
										<source src="../audio/<?php echo $prompt->PROMPT_AUDIO_PASSAGE; ?>" type="audio/mp3">
										Your browser does not support the audio element.
									</audio>
								<?php } ?>
							</td>
						</tr>
						<tr>
							<td class="EditTitle"><label for="PROMPT_AUDIO_LEN">Audio Length:</label></td>
							<td><input id="PROMPT_AUDIO_LEN" class="form-control" type="text" name="PROMPT_AUDIO_LEN" maxlength="4" value="<?php echo $prompt->PROMPT_AUDIO_LEN; ?>"></td>
							<td colspan="2"> In seconds</td>
						</tr>
						<tr>
							<td class="EditTitle" valign="top"><label for="PROMPT_INSTRUCTIONS">Instructions:</label></td>
							<td colspan="4" valign="top"><textarea id="PROMPT_INSTRUCTIONS" class="form-control" name="PROMPT_INSTRUCTIONS" rows="5" cols="73" required><?php echo $prompt->PROMPT_INSTRUCTIONS; ?></textarea></td>
						</tr>
						<tr>
							<td class="EditTitle" valign="top"><label for="PROMPT_BODY">Body:</label></td>
							<td colspan="4" valign="top"><textarea id="PROMPT_BODY" class="form-control" name="PROMPT_BODY" rows="14" cols="73" required><?php echo $prompt->PROMPT_BODY; ?></textarea></td>
						</tr>
						<tr>
							<td>&nbsp;<br><br></td>
							<td>
								<input type="submit" class="waButton" value="Save">&nbsp;&nbsp;&nbsp;&nbsp;
								<a href="edit_prompt.php?pid=0&id=<?php echo $GLOBALS["SESSION_ID"]; ?>" class="waButton">Create Another Prompt</a>
							</td>
						</tr>
					</table>
				</form>
				<br><br><br><br>
			</div>
		</div>
	</div>
	<!-- Bootstrap core JS-->
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js"></script>
	<!-- Core theme JS-->
	<script src="../js/scripts.js"></script>
	<?php require '../includes/empty_footer.php';   ?>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/7.3/highlight.min.js"></script>
	<script src="../js/parsley.min.js"></script>
	<script type="text/javascript">
		$(function() {
			$('#prompteditor').parsley().on('field:validated', function() {
				var ok = $('.parsley-error').length === 0;
			})
		});
	</script>

</body>

</html>
