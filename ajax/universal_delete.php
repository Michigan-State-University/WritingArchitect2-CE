<!DOCTYPE html>
<?php
include_once '../includes/Database.php';
include_once '../includes/WA_Accounts.php';
include_once '../includes/WA_Classes.php';
include_once '../includes/WA_Security.php';

//get incoming values
$database = new Database();
$db = $database->connect();

$secure_access = check_security($db);

if ($GLOBALS['USER_LEVEL'] == "STUDENT") die("Access denied.");

# $query = isset($_POST['PAYLOAD']) ? $_POST['PAYLOAD'] : "";
if (!isset($_POST['TYPE'])) {
	echo "Error: TYPE not set";
	die();
}

if (!isset($_POST['VALUE'])) {
	echo "Error: VALUE not set";
	die();
}


$tableName = "";
$column = "";
$value = $_POST['VALUE'];

switch ($_POST['TYPE']) {
	case 'user':
		$targetAccount = new Account($db);
		# find the user by user_code
		$targetAccount->USER_CODE = $value;
		$targetAccount->loadAccountByUserCode($db);
		if ($GLOBALS['USER_LEVEL'] == "ADMIN") {
			$tableName = "config_users";
			$column = "user_code";
			break;
		} elseif ($GLOBALS['USER_LEVEL'] == "SCORER") {
			// Check if $targetAccount->ORGANIATION is the same as the current user's organization
			if ($GLOBALS['USER_ORGANIZATION'] != $targetAccount->USER_ORGANIZATION) die("Access denied.");
			// Check if the targetAccount->USER_LEVEL is lower
			if ($targetAccount->USER_LEVEL == "SCORER" || $targetAccount->USER_LEVEL == "TEACHER" || $targetAccount->USER_LEVEL == "STUDENT") {
				$tableName = "config_users";
				$column = "user_code";
				break;
			} else {
				die("Access denied.");
				break;
			}
		} elseif ($GLOBALS['USER_LEVEL'] == "TEACHER") {
			// Check if $targetAccount->ORGANIATION is the same as the current user's organization
			if ($GLOBALS['USER_ORGANIZATION'] != $targetAccount->USER_ORGANIZATION) die("Access denied.");
			// Check if $targetAccount->USER_LEVEL is lower
			if ($targetAccount->USER_LEVEL == "STUDENT") {
				$tableName = "config_users";
				$column = "user_code";
				break;
			} else {
				die("Access denied.");
				break;
			}
		} else {
			die("Access denied.");
		}
	case 'school':
		if ($GLOBALS['USER_LEVEL'] != "ADMIN") die("Access denied.");
		$tableName = "config_schools";
		$column = "SCHOOL_NAME";
		break;
	case 'class':
		$targetClass = new School_Class($db);
		$targetClass->load_class($db, $value);
		if ($GLOBALS['USER_LEVEL'] == "ADMIN") {
			$tableName = "config_classes";
			$column = "CLASS_ID";
			break;
		} elseif ($GLOBALS['USER_LEVEL'] == "SCORER" || $GLOBALS['USER_LEVEL'] == "TEACHER") {
			// Limit the classes to the scorer's organization
			if ($GLOBALS['USER_SCHOOL_SN'] != $targetClass->CLASS_SCHOOL_ID) die("Access denied.");
			$tableName = "config_classes";
			$column = "CLASS_ID";
			break;
		} else {
			die("Access denied.");
		}
	case 'quiz':
		$tableName = "quiz";
		$column = "Q_ID";
		break;
	case 'quiz_prompt':
		if ($GLOBALS['USER_LEVEL'] == "SCORER") die("Access denied.");
		$tableName = "quiz_prompts";
		$column = "PROMPT_SHORT_TITLE";
		break;
	case 'quiz_template':
		if ($GLOBALS['USER_LEVEL'] != "ADMIN") die("Access denied.");
		$tableName = "quiz_template";
		$column = "QT_TITLE";
		break;
	default:
		# code...
		break;
}

if ($tableName == "" || $column == "") {
	echo "Error: Invalid TYPE";
	die();
}

$query = "DELETE FROM $tableName WHERE $column=:value";
$stmt = $db->prepare($query);
$stmt->bindValue(':value', $value);
$stmt->execute();
?>
