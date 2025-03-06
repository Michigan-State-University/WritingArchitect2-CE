<!DOCTYPE html>
<?php
include_once '../includes/Database.php';
include_once '../includes/WA_Security.php';

//get incoming values
$database = new Database();
$db = $database->connect();

$secure_access = check_security($db);

if ($GLOBALS['USER_LEVEL'] == 'STUDENT') die("Access denied.");

$CLASSID = isset($_POST['CLASSID']) ? $_POST['CLASSID'] : "";

$query = "SELECT CLASS_NAME from config_classes WHERE CLASS_ID=:classid";
$stmt = $db->prepare($query);
$stmt->bindValue('classid', $CLASSID, PDO::PARAM_INT);
$stmt->execute();
if ($stmt->rowCount() > 0) {
	$row = $stmt->fetch();
	$class_list = $row['CLASS_NAME'];
}
echo $class_list . " Roster<br>";

$query = "SELECT USER_LAST_NAME, USER_FIRST_NAME from config_users WHERE USER_ID IN (select PUPIL_STUDENTID FROM config_pupils WHERE PUPIL_CLASSID=:classid) ORDER BY USER_LAST_NAME, USER_FIRST_NAME ";
$stmt = $db->prepare($query);
$stmt->bindValue('classid', $CLASSID, PDO::PARAM_INT);
$stmt->execute();
while ($row = $stmt->fetch()) {
	echo $row['USER_LAST_NAME'] . ", " . $row['USER_FIRST_NAME'] . "<br>";
}

?>
