<!DOCTYPE html>
<?php
$err_msg = "";

$emsg = "";
if (isset($_GET["em"])) $msg = $_GET["em"];

if ($emsg == 'f') $err_msg = "Login attempt failed";
?>
<html lang="en">

<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
	<meta name="description" content="" />
	<meta name="author" content="" />
	<link rel="icon" type="image/x-icon" href="/favicon.ico" />
	<link rel="icon" type="image/x-icon" href="favicon.ico" />
	<title>Writing Architect</title>
	<!-- Favicon-->
	<!-- Core theme CSS (includes Bootstrap)-->
	<link href="../css/styles.css" rel="stylesheet" />
	<link href="../css/WA.css" rel="stylesheet" />
</head>

<body>
	<?php require '../includes/empty_header.php';   ?>
	<table align="center">
		<tr>
			<td align="center" colspan="3"><br><br></td>
		</tr>
		<tr>
			<td valign="middle"><img src="../images/WA logo2.png" alt=""></td>
			<td valign="middle" bgcolor="grey">&nbsp;</td>
			<td valign="middle" width="400">
				<div id="error_msg"><?php echo $err_msg; ?></div>
				<form id="login_form" action="login_validate.php" method="post" name="login_form">
					<table border="0" cellspacing="4" cellpadding="2">
						<tr>
							<td class="EditTitle">User ID</td>
							<td><input id="user_name" class="form-control" type="text" name="user_name"></td>
						</tr>
						<tr>
							<td class="EditTitle">Password</td>
							<td><input id="password" class="form-control" type="password" name="password"></td>
						</tr>
						<tr>
							<td>&nbsp;<br><br></td>
							<td>
								<a class="waButton" href="./" onClick="document.forms.login_form.submit();return false;"><span>LOGIN</span></a>
							</td>
						</tr>
					</table>
				</form>
			</td>
		</tr>
		<p>&nbsp;</p>
		<?php require '../includes/empty_footer.php';   ?>
	</table>
	<!-- Bootstrap core JS-->
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>