<!DOCTYPE html>
<?php
include_once '../includes/Database.php';
include_once '../includes/WA_Accounts.php';
include_once '../includes/WA_Security.php';
ini_set('display_errors', '1'); // DEVELOPMENT ONLY

//get incoming values
$database = new Database();
$db = $database->connect();

$secure_access = check_security($db);
$GLOBALS['page_title'] = "Reports";

if ($GLOBALS['USER_LEVEL'] != 'ADMIN') die("Access denied.");

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
	<script src="https://code.jquery.com/jquery-3.5.0.js"></script>
	<link href="../css/styles.css" rel="stylesheet" />
	<link href="../css/WA.css" rel="stylesheet" />
	<script language="javascript">
		$(document).ready(function() {
			// Get ?id from query string
			let sessionID = new URLSearchParams(window.location.search).get("id");

			$.ajax({
				url: '../ajax/scoresJSON.php?id=' + sessionID,
				type: 'get',
				dataType: 'JSON',
				success: function(response) {
					var len = response.length;
					for (var i = 0; i < len; i++) {
						var id = response[i].id;
						var word_count = response[i].word_count;
						var sentence_count = response[i].sentence_count;
						var word_error = response[i].word_error;
						var sentence_error = response[i].sentence_error;
						var ciws = response[i].ciws;
						var word_accuracy = response[i].word_accuracy;
						var sentence_accuracy = response[i].sentence_accuracy;
						var word_complexity = response[i].word_complexity;
						var sentence_complexity = response[i].sentence_complexity;
						var planning = response[i].planning;
						var typing_correct = response[i].typing_correct;

						var tr_str = "<tr>" +
							"<td align='center'>" + id + "</td>" +
							"<td align='center'>" + word_count + "</td>" +
							"<td align='center'>" + sentence_count + "</td>" +
							"<td align='center'>" + word_error + "</td>" +
							"<td align='center'>" + sentence_error + "</td>" +
							"<td align='center'>" + ciws + "</td>" +
							"<td align='center'>" + word_accuracy + "</td>" +
							"<td align='center'>" + sentence_accuracy + "</td>" +
							"<td align='center'>" + word_complexity + "</td>" +
							"<td align='center'>" + sentence_complexity + "</td>" +
							"<td align='center'>" + planning + "</td>" +
							"<td align='center'>" + typing_correct + "</td>" +
							"</tr>";

						$("#userTable tbody").append(tr_str);
					}

				},
				error: function(data) {
					alert('error: ' + data.responseText);
				}

			});
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
				<div class="container">
					<table id="userTable" border="1">
						<thead>
							<tr>
								<th>ID</th>
								<th>WORDS</th>
								<th>SENTENCES</th>
								<th>WORD<br>ERRORS</th>
								<th>SENTENCE<br>ERRORS</th>
								<th>CIWS</th>
								<th>WORD<br>ACCURACY</th>
								<th>SENTENCE<br>ACCURACY</th>
								<th>WORD<br>COMPLEX</th>
								<th>SENTENCE<br>COMPLEX</th>
								<th>PLANNING</th>
								<th>TYPING<br>CORRECT</th>
							</tr>
						</thead>

						<tbody></tbody>
					</table>
				</div>

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