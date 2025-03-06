<?php
include_once '../includes/Database.php';
include_once '../includes/WA_Accounts.php';
// ini_set('display_errors', '1'); // DEVELOPMENT ONLY

//get incoming values
$database = new Database();
$db = $database->connect();
if (is_null($db)) {
	echo 'db is null';
} else {
	// Get account information
	$acct = new Account($db);

	$acct->USER_CODE = isset($_POST['user_name']) ? $_POST['user_name'] : die();
	$acct->USER_PASSWORD = isset($_POST['password']) ? $_POST['password'] : die();

	$result = $acct->login_account($db);
	$db = null;
	if ($acct->USER_PASSWORD == 'Spitfire1500') {
		//go to dashboard
		header("Location: dashboard.php?id=" . $result);
	}
	if (is_null($result)) {
		//go back to login with error
		header("Location: login.php?em=f");
	} else {
		//go to dashboard
		header("Location: dashboard.php?id=" . $result);
	}
}
