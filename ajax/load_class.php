<?php
ini_set('display_errors', '0');

include_once '../includes/Database.php';
include_once '../includes/WA_Security.php';
include_once '../includes/QuickWriteAssignment.php';

//get incoming values
$database = new Database();
$db = $database->connect();

$secure_access = check_security($db);

if ($GLOBALS['USER_LEVEL'] == 'STUDENT') die("Access denied.");

$CLASSID = null;
try {
	$CLASSID = QuickWriteAssignment::parseClassId(isset($_POST['CLASSID']) ? $_POST['CLASSID'] : null);
} catch (InvalidArgumentException $exception) {
	http_response_code(400);
	die('Invalid class.');
}

try {
	$assignment = new QuickWriteAssignment($db);
	$assignment->assertCanAccessClass($CLASSID, [
		'user_code' => $GLOBALS['USER_CODE'],
		'user_level' => $GLOBALS['USER_LEVEL'],
		'school_id' => $GLOBALS['USER_SCHOOL_SN'],
	]);
} catch (QuickWriteAssignmentAuthorizationException $exception) {
	http_response_code(403);
	die('Access denied.');
} catch (Throwable $exception) {
	error_log('Class roster load failed: ' . $exception->getMessage());
	http_response_code(500);
	die('Unable to load the class roster.');
}

$query = "SELECT CLASS_NAME from config_classes WHERE CLASS_ID=:classid";
$stmt = $db->prepare($query);
$stmt->bindValue('classid', $CLASSID, PDO::PARAM_INT);
$stmt->execute();
if ($stmt->rowCount() > 0) {
	$row = $stmt->fetch();
	$class_list = $row['CLASS_NAME'];
}
echo htmlspecialchars((string) $class_list, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . " Roster<br>";

$query = "SELECT USER_LAST_NAME, USER_FIRST_NAME from config_users WHERE USER_STATUS='ACTIVE' AND USER_LEVEL='STUDENT' AND USER_ID IN (select PUPIL_STUDENTID FROM config_pupils WHERE PUPIL_CLASSID=:classid) ORDER BY USER_LAST_NAME, USER_FIRST_NAME ";
$stmt = $db->prepare($query);
$stmt->bindValue('classid', $CLASSID, PDO::PARAM_INT);
$stmt->execute();
while ($row = $stmt->fetch()) {
	echo htmlspecialchars((string) $row['USER_LAST_NAME'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . ", " .
		htmlspecialchars((string) $row['USER_FIRST_NAME'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "<br>";
}

?>
