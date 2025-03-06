<?php
class Schools
{
	private $conn;

	// Properties
	public $SCHOOL_ID;
	public $SCHOOL_NAME;
	public $SCHOOL_SN;
	public $SCHOOL_CONTACT;
	public $SCHOOL_STATUS;
	// Constructor with DB
	public function __contruct($db)
	{
		$this->conn = $db;
	}

	// save single school
	public function save_school($db, $school_id)
	{
		$dupid = $school_id;
		$SQL_SCHOOL_NAME = addslashes($this->SCHOOL_NAME);
		$SQL_SCHOOL_SN = addslashes($this->SCHOOL_SN);
		$SQL_SCHOOL_CONTACT = addslashes($this->SCHOOL_CONTACT);

		$dataval = "SCHOOL_NAME=:sname, 
			SCHOOL_SN=:ssn, 
			SCHOOL_CONTACT=:scontact, 
			SCHOOL_STATUS=:sstatus, 
			SCHOOL_MODIFIED_BY=:guc,
			SCHOOL_MODIFIED_ON=UTC_TIMESTAMP()";

		$query = "select SCHOOL_ID from config_schools where SCHOOL_ID<>:sid and SCHOOL_NAME=:sname";
		$stmt = $db->prepare($query);
		$stmt->bindValue(':sid', $school_id, PDO::PARAM_INT);
		$stmt->bindValue(':sname', $SQL_SCHOOL_NAME, PDO::PARAM_STR);
		$stmt->execute();
		if ($stmt->rowCount() > 0) {
			return "NOT UNIQUE";
		} else {
			if ($school_id == "0") {
				$createdtext = ", SCHOOL_CREATED_BY=:guc, SCHOOL_CREATED_AT=UTC_TIMESTAMP()";
				$query = "INSERT into config_schools set " . $dataval . $createdtext;
				$stmt = $db->prepare($query);
				$stmt->bindValue(':sname', $SQL_SCHOOL_NAME, PDO::PARAM_STR);
				$stmt->bindValue(':ssn', $SQL_SCHOOL_SN, PDO::PARAM_STR);
				$stmt->bindValue(':scontact', $SQL_SCHOOL_CONTACT, PDO::PARAM_STR);
				$stmt->bindValue(':sstatus', $this->SCHOOL_STATUS, PDO::PARAM_STR);
				$stmt->bindValue(':guc', $GLOBALS['USER_CODE'], PDO::PARAM_STR);
				if ($stmt->execute()) {
					$query = "SELECT SCHOOL_ID from config_schools where SCHOOL_SN=:ssn";
					$stmt = $db->prepare($query);
					$stmt->bindValue(':ssn', $SQL_SCHOOL_SN, PDO::PARAM_STR);
					$stmt->execute();
					if ($stmt->rowCount() > 0) {
						$row = $stmt->fetch();
						$this->SCHOOL_ID = $row['SCHOOL_ID'];
						return $row['SCHOOL_ID'];
					} else {
						return "";
					}
				}
				return "";
			} else {
				$query = "UPDATE config_schools SET " . $dataval . " WHERE SCHOOL_ID=:sid";
				$stmt = $db->prepare($query);
				$stmt->bindValue(':sname', $SQL_SCHOOL_NAME, PDO::PARAM_STR);
				$stmt->bindValue(':ssn', $SQL_SCHOOL_SN, PDO::PARAM_STR);
				$stmt->bindValue(':scontact', $SQL_SCHOOL_CONTACT, PDO::PARAM_STR);
				$stmt->bindValue(':sstatus', $this->SCHOOL_STATUS, PDO::PARAM_STR);
				$stmt->bindValue(':guc', $GLOBALS['USER_CODE'], PDO::PARAM_STR);
				$stmt->bindValue(':sid', $school_id, PDO::PARAM_INT);
				$stmt->execute();
				return "";
			}
		}
	}

	// load single school
	public function load_school($db, $school_id)
	{
		$query = "SELECT * from config_schools where SCHOOL_ID=:sid";
		$stmt = $db->prepare($query);
		$stmt->bindValue(':sid', $school_id, PDO::PARAM_INT);
		$stmt->execute();
		if ($stmt->rowCount() > 0) {
			$row = $stmt->fetch();
			$this->SCHOOL_NAME = $row['SCHOOL_NAME'];
			$this->SCHOOL_SN = $row['SCHOOL_SN'];
			$this->SCHOOL_CONTACT = $row['SCHOOL_CONTACT'];
			$this->SCHOOL_STATUS = $row['SCHOOL_STATUS'];
			return true;
		} else {
			return false;
		}
	}
}

// list admin schools
function list_schools($db)
{
	$school_list = "<table><tr><td class='table_title'>Actions</td><td class='table_title'>School Name</td><td class='table_title'>Abbreviated Name</td><td  class='table_title'>Contact</td><td class='table_title'>Status</td></tr>";
	$query = "SELECT * from config_schools order by SCHOOL_NAME";
	$stmt = $db->prepare($query);
	$stmt->execute();
	while ($row = $stmt->fetch()) {
		$SCHOOL_ID = $row['SCHOOL_ID'];
		$delete_link = '<a href="" onclick="delete_record(\'' . $row['SCHOOL_NAME'] . '\');return false;"><img src="../images/icn_delete.png" height="16" width="16"></a>';
		$edit_link = '<a href="edit_school.php?sid=' . $SCHOOL_ID . '&id=' . $GLOBALS["SESSION_ID"] . '"><img src="../images/icn_edit.png"></a>';
		$school_list .= "<tr><td align='center'>$edit_link $delete_link</td><td class='table_row'>" . $row['SCHOOL_NAME'] . "</td><td class='table_row'>" . $row['SCHOOL_SN'] . "</td><td class='table_row'>" . $row['SCHOOL_CONTACT'] . "</td><td class='table_row'>" . $row['SCHOOL_STATUS'] . "</td></tr>";
	}
	$school_list .= "</table>";
	return $school_list;
}
