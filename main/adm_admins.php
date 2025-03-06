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
$GLOBALS['page_title'] = "Program Adminstrators";

if ($GLOBALS['USER_LEVEL'] != "ADMIN") die("Access denied.");

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
	<script language="javascript">
		function delete_record(user_code) {
			var msg_str = "Delete user " + user_code + "?";
			if (confirm(msg_str)) {
				// Get ?id from query string
				let sessionID = new URLSearchParams(window.location.search).get("id");

				$.ajax({
					url: '../ajax/universal_delete.php?id=' + sessionID,
					type: "POST",
					data: ({
						TYPE: 'user',
						VALUE: user_code
					}),
					success: function(data) {
						location.reload();
					},
					error: function(data) {
						alert('error: ' + data.responseText);
					}
				});
			}
		}
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
			<div class="container-fluid"><br>
				<a href="edit_user.php?USER_LEVEL=ADMIN&uid=0&id=<?php echo $GLOBALS["SESSION_ID"] ?>" class="waButtonSmall">Create New User</a>
				<?php echo list_admins($db); ?>
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
