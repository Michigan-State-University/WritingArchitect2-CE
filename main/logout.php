<?php
include_once '../includes/Database.php';
include_once '../includes/WA_Security.php';
// ini_set('display_errors', '1'); // DEVELOPMENT ONLY

//get incoming values
$database = new Database();
$db = $database->connect();

$GLOBALS["SESSION_ID"] = isset($_GET['id']) ? $_GET['id'] : die();

$date = new DateTime();
$sql_date = date_format($date, "Y-m-d H:i:s");

$query = "update man_sessions set session_end_time=:sql_date WHERE session_public_id=:gsid";
$stmt = $db->prepare($query);
$stmt->bindValue(':sql_date', $sql_date, PDO::PARAM_STR);
$stmt->bindValue(':gsid', $GLOBALS["SESSION_ID"], PDO::PARAM_STR);
$stmt->execute();
$db = null;
header("Location: ../index.php?msg=LO");
