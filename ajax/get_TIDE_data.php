<?php
include_once '../includes/Database.php';
include_once '../includes/WA_Security.php';

//get incoming values
$database = new Database();
$db = $database->connect();

$secure_access = check_security($db);

if ($GLOBALS['USER_LEVEL'] == 'STUDENT') die("Access denied.");

$Q_ID = isset($_GET['Q_ID']) ? $_GET['Q_ID'] : "";

$query = "SELECT Q_TIDE, Q_TIDE_SCORING, Q_ESSAY from quiz WHERE Q_ID=:qid";
$stmt = $db->prepare($query);
$stmt->bindValue(':qid', $Q_ID, PDO::PARAM_INT);
$stmt->execute();
while ($row = $stmt->fetch()) {
	echo $row['Q_ESSAY'] . "||" . $row['Q_TIDE'] . "||" . $row['Q_TIDE_SCORING'];
}
