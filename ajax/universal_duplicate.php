<?php
include_once '../includes/Database.php';
//get incoming values
$database = new Database();
$db = $database->connect();

$FLD_NAME = isset($_POST['FLD_NAME']) ? $_POST['FLD_NAME'] : "";
$TBL_NAME = isset($_POST['TBL_NAME']) ? $_POST['TBL_NAME'] : "";
$INDEX_NAME = isset($_POST['INDEX_NAME']) ? $_POST['INDEX_NAME'] : "";

$INDEX_ID = isset($_POST['INDEX_ID']) ? $_POST['INDEX_ID'] : "";
$FLD_VALUE = isset($_POST['name']) ? $_POST['name'] : "";
$table_code = isset($_POST['p']) ? $_POST['p'] : "";
$FLD_VALUE = addslashes($FLD_VALUE);

switch ($table_code) {
	case "QT":
		$query = "select QT_ID from QUI_TEMPLATE where QT_TITLE='" . $FLD_VALUE . "' and QT_ID<>" . $INDEX_ID;
		echo $query;
		break;
}

/*	$stmt = $db->prepare($query);
	$stmt->execute();
	if ($stmt->rowCount() > 0) {			
		switch ($FLD_NAME) {
			case "USER_CODE":
				echo "This User ID value already exists. Please select a unique ID";
				break;
		}
	}*/
