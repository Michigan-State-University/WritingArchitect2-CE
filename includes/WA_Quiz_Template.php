<?php
class QuizTemplate
{
	private $conn;

	// Properties
	public $QT_ID;
	public $QT_TITLE;
	public $QT_DESCRIPTION;
	public $QT_PROMPT_1;
	public $QT_PROMPT_2;
	public $QT_PROMPT_3;
	public $QT_GRADES;
	public $QT_STATUS;
	// Constructor with DB
	public function __contruct($db)
	{
		$this->conn = $db;
	}

	// save single quiz
	public function save_quiz_template($db, $qt_id)
	{
		$SQL_QT_TITLE = addslashes($this->QT_TITLE);
		$SQL_QT_DESCRIPTION = addslashes($this->QT_DESCRIPTION);
		$SQL_QT_GRADES = addslashes($this->QT_GRADES);


		$dataval = "QT_TITLE=:qt_title, 
			QT_DESCRIPTION=:qt_description, 
			QT_PROMPT_1=:qt_1, 
			QT_PROMPT_2=:qt_2, 
			QT_PROMPT_3=:qt_3, 
			QT_GRADES=:qt_grades
			QT_STATUS=:qt_status,
			QT_MODIFIED_BY=:guc,
			QT_MODIFIED_ON=UTC_TIMESTAMP()";

		$query = "select QT_ID from quiz_template where QT_ID<>:qt_id and QT_TITLE=:qt_title";
		$stmt = $db->prepare($query);
		$stmt->bindValue(':qt_id', $qt_id, PDO::PARAM_INT);
		$stmt->bindValue(':qt_title', $SQL_QT_TITLE, PDO::PARAM_STR);
		$stmt->execute();
		if ($stmt->rowCount() > 0) {
			return "NOT UNIQUE";
		} else {
			if ($qt_id == "0") {
				$createdtext = ", QT_CREATED_BY=:guc ,QT_CREATED_AT=UTC_TIMESTAMP()";
				$query = "INSERT into quiz_template set " . $dataval . $createdtext;
				$stmt = $db->prepare($query);
				$stmt->bindValue(':qt_title', $SQL_QT_TITLE, PDO::PARAM_STR);
				$stmt->bindValue(':qt_description', $SQL_QT_DESCRIPTION, PDO::PARAM_STR);
				$stmt->bindValue(':qt_1', $this->QT_PROMPT_1, PDO::PARAM_STR);
				$stmt->bindValue(':qt_2', $this->QT_PROMPT_2, PDO::PARAM_STR);
				$stmt->bindValue(':qt_3', $this->QT_PROMPT_3, PDO::PARAM_STR);
				$stmt->bindValue(':qt_grades', $SQL_QT_GRADES, PDO::PARAM_STR);
				$stmt->bindValue(':qt_status', $this->QT_STATUS, PDO::PARAM_STR);
				$stmt->bindValue(':guc', $GLOBALS['USER_CODE'], PDO::PARAM_STR);
				if ($stmt->execute()) {
					$query = "SELECT QT_ID from quiz_template where QT_TITLE=:qt_title";
					$stmt = $db->prepare($query);
					$stmt->bindValue(':qt_title', $SQL_QT_TITLE, PDO::PARAM_STR);
					$stmt->execute();
					if ($stmt->rowCount() > 0) {
						$row = $stmt->fetch();
						$this->QT_ID = $row['QT_ID'];
						return $row['QT_ID'];
					} else {
						return "";
					}
				}
				return "";
			} else {
				$query = "UPDATE quiz_template SET " . $dataval . " WHERE QT_ID=:qt_id";
				$stmt = $db->prepare($query);
				$stmt->bindValue(':qt_title', $SQL_QT_TITLE, PDO::PARAM_STR);
				$stmt->bindValue(':qt_description', $SQL_QT_DESCRIPTION, PDO::PARAM_STR);
				$stmt->bindValue(':qt_1', $this->QT_PROMPT_1, PDO::PARAM_STR);
				$stmt->bindValue(':qt_2', $this->QT_PROMPT_2, PDO::PARAM_STR);
				$stmt->bindValue(':qt_3', $this->QT_PROMPT_3, PDO::PARAM_STR);
				$stmt->bindValue(':qt_grades', $SQL_QT_GRADES, PDO::PARAM_STR);
				$stmt->bindValue(':qt_status', $this->QT_STATUS, PDO::PARAM_STR);
				$stmt->bindValue(':guc', $GLOBALS['USER_CODE'], PDO::PARAM_STR);
				$stmt->bindValue(':qt_id', $qt_id, PDO::PARAM_INT);
				$stmt->execute();
				return "";
			}
		}
	}

	// load single quiz
	public function load_quiz_template($db, $qt_id)
	{
		$query = "SELECT * from quiz_template where QT_ID=:qt_id";
		$stmt = $db->prepare($query);
		$stmt->bindValue(':qt_id', $qt_id, PDO::PARAM_INT);
		$stmt->execute();
		if ($stmt->rowCount() > 0) {
			$row = $stmt->fetch();
			$this->QT_TITLE = $row['QT_TITLE'];
			$this->QT_DESCRIPTION = $row['QT_DESCRIPTION'];
			$this->QT_PROMPT_1 = $row['QT_PROMPT_1'];
			$this->QT_PROMPT_2 = $row['QT_PROMPT_2'];
			$this->QT_PROMPT_3 = $row['QT_PROMPT_3'];
			$this->QT_STATUS = $row['QT_STATUS'];
			$this->QT_GRADES = $row['QT_GRADES'];
			return true;
		} else {
			return false;
		}
	}
}


// list quiz templates
function list_quiz_templates($db)
{
	$quiz_list = "<table><tr><td class='table_title'>Edit</td><td class='table_title'>Name</td><td class='table_title'>Prompt #1</td><td class='table_title'>Prompt #2</td><td class='table_title'>Prompt #3</td><td class='table_title'>Grades</td><td class='table_title'>Status</td></tr>";
	$query = "SELECT * from quiz_template order by QT_ID";
	$stmt = $db->prepare($query);
	$stmt->execute();
	while ($row = $stmt->fetch()) {
		$QT_ID = $row['QT_ID'];
		$delete_link = '<a href="" onclick="delete_record(\'' . $row['QT_TITLE'] . '\');return false;"><img src="../images/icn_delete.png" height="16" width="16"></a>';
		$edit_link = '<a href="edit_quiz_template.php?qid=' . $QT_ID . '&id=' . $GLOBALS["SESSION_ID"] . '"><img src="../images/icn_edit.png"></a>';
		$quiz_list .= "<tr><td align='center'>$edit_link $delete_link</td><td class='table_row'>" . $row['QT_TITLE'] . "</td><td class='table_row'>" . $row['QT_PROMPT_1'] . "</td><td class='table_row'>" . $row['QT_PROMPT_2'] . "</td><td class='table_row'>" . $row['QT_PROMPT_3'] . "</td><td class='table_row'>" . $row['QT_GRADES'] . "</td><td class='table_row'>" . $row['QT_STATUS'] . "</td></tr>";
	}
	$quiz_list .= "</table>";
	return $quiz_list;
}
