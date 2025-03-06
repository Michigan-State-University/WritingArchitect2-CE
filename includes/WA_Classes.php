<?php
class School_Class
{
	private $conn;

	// Properties
	public $CLASS_ID;
	public $CLASS_NAME;
	public $CLASS_SCHOOL_ID; //SCHOOL_SN
	public $CLASS_GRADE;  //menu
	public $CLASS_TEACHER_ID;  // user_code
	// Constructor with DB
	public function __contruct($db)
	{
		$this->conn = $db;
	}

	// save single class
	public function save_class($db, $class_id)
	{
		$SQL_CLASS_NAME = addslashes($this->CLASS_NAME);

		$dataval = "CLASS_NAME=:scn, 
			CLASS_SCHOOL_ID=:tcsi, 
			CLASS_GRADE=:tcg, 
			CLASS_TEACHER_ID=:tcti, 
			CLASS_MODIFIED_BY=:guc,
			CLASS_MODIFIED_ON=UTC_TIMESTAMP()";

		if ($class_id == "0") {
			$createdtext = ", CLASS_CREATED_BY=:guc, CLASS_CREATED_AT=UTC_TIMESTAMP()";
			$query = "INSERT into config_classes set " . $dataval . $createdtext;
			$stmt = $db->prepare($query);
			$stmt->bindValue(':scn', $SQL_CLASS_NAME, PDO::PARAM_STR);
			$stmt->bindValue(':tcsi', $this->CLASS_SCHOOL_ID, PDO::PARAM_STR);
			$stmt->bindValue(':tcg', $this->CLASS_GRADE, PDO::PARAM_INT);
			$stmt->bindValue(':tcti', $this->CLASS_TEACHER_ID, PDO::PARAM_STR);
			$stmt->bindValue(':guc', $GLOBALS['USER_CODE'], PDO::PARAM_STR);
			if ($stmt->execute()) {
				$query = "SELECT CLASS_ID from config_classes where CLASS_NAME=:scn";
				$stmt = $db->prepare($query);
				$stmt->bindValue(':scn', $SQL_CLASS_NAME, PDO::PARAM_STR);
				$stmt->execute();
				if ($stmt->rowCount() > 0) {
					$row = $stmt->fetch();
					$this->CLASS_SCHOOL_ID = $row['CLASS_ID'];
					return $row['CLASS_ID'];
				} else {
					return "";
				}
			}
			return "";
		} else {
			$query = "UPDATE config_classes SET " . $dataval . " WHERE CLASS_ID=:cid";
			$stmt = $db->prepare($query);
			$stmt->bindValue(':scn', $SQL_CLASS_NAME, PDO::PARAM_STR);
			$stmt->bindValue(':tcsi', $this->CLASS_SCHOOL_ID, PDO::PARAM_STR);
			$stmt->bindValue(':tcg', $this->CLASS_GRADE, PDO::PARAM_INT);
			$stmt->bindValue(':tcti', $this->CLASS_TEACHER_ID, PDO::PARAM_STR);
			$stmt->bindValue(':guc', $GLOBALS['USER_CODE'], PDO::PARAM_STR);
			$stmt->bindValue(':cid', $class_id, PDO::PARAM_INT);
			$stmt->execute();
			return "";
		}
	}

	// load single class
	public function load_class($db, $class_id)
	{
		$query = "SELECT * from config_classes where CLASS_ID=:cid";
		$stmt = $db->prepare($query);
		$stmt->bindValue(':cid', $class_id, PDO::PARAM_INT);
		$stmt->execute();
		if ($stmt->rowCount() > 0) {
			$row = $stmt->fetch();
			$this->CLASS_NAME = $row['CLASS_NAME'];
			$this->CLASS_SCHOOL_ID = $row['CLASS_SCHOOL_ID'];
			$this->CLASS_GRADE = $row['CLASS_GRADE'];
			$this->CLASS_TEACHER_ID = $row['CLASS_TEACHER_ID'];
			return true;
		} else {
			return false;
		}
	}
}


// list classes
function list_classes($db)
{
	if (strpos($GLOBALS['USER_AUTHORITY'], 'TEACHER') == false) {
		$clauses = "CLASS_SCHOOL_ID=:guss";
	} else {
		$clauses = "CLASS_TEACHER_ID=:guc";
	}

	$class_list = "<table><tr><td class='table_title'>Edit</td><td class='table_title'>Class Name</td><td class='table_title'>Grade</td><td class='table_title'>Teacher</td></tr>";
	$query = "SELECT * from config_classes join config_users on CLASS_TEACHER_ID=USER_CODE WHERE " . $clauses . " order by CLASS_NAME";
	$stmt = $db->prepare($query);
	if (strpos($GLOBALS['USER_AUTHORITY'], 'TEACHER') == false) {
		$stmt->bindValue(':guss', $GLOBALS['USER_SCHOOL_SN'], PDO::PARAM_STR);
	} else {
		$stmt->bindValue(':guc', $GLOBALS['USER_CODE'], PDO::PARAM_STR);
	}
	$stmt->execute();
	while ($row = $stmt->fetch()) {
		$CLASS_ID = $row['CLASS_ID'];
		$delete_link = '<a title="DELETE CLASS" href="" onclick="delete_record(\'' . $row['CLASS_NAME'] . '\',' . $CLASS_ID . ' );return false;"><img src="../images/icn_delete16.png" height="16" width="16"></a>';
		$roster_link = '<a title="SHOW CLASS ROSTER" href="sch_roster.php?cid=' . $CLASS_ID . '&id=' . $GLOBALS["SESSION_ID"] . '"><img src="../images/icn_roster16.png" height="16" width="16"></a>';
		$edit_link = '<a title="EDIT CLASS" href="edit_class.php?cid=' . $CLASS_ID . '&id=' . $GLOBALS["SESSION_ID"] . '"><img src="../images/icn_edit.png"></a>';
		$class_list .= "<tr><td align='center'>$edit_link $delete_link $roster_link</td><td class='table_row'>" . $row['CLASS_NAME'] . "</td><td class='table_row'>" . $row['CLASS_GRADE'] . "</td><td class='table_row'>" . $row['USER_LAST_NAME'] . ", " . $row['USER_FIRST_NAME'] . "</td></tr>";
	}
	$class_list .= "</table>";
	return $class_list;
}

// list class menu
function classes_menu($db, $ITEM_VAL)
{
	if (strpos($GLOBALS['USER_AUTHORITY'], 'TEACHER') == false) {
		$clauses = "CLASS_SCHOOL_ID=:guss";
	} else {
		$clauses = "CLASS_TEACHER_ID=:guc";
	}

	$cdm = '<select id="CLASSES" name="CLASSES" size="1" required class="form-control"><option value=""' . check_selected("", $ITEM_VAL) . '>Select</option>';
	$query = "SELECT * from config_classes join config_users on CLASS_TEACHER_ID=USER_CODE WHERE " . $clauses . " order by CLASS_NAME";
	$stmt = $db->prepare($query);
	if (strpos($GLOBALS['USER_AUTHORITY'], 'TEACHER') == false) {
		$stmt->bindValue(':guss', $GLOBALS['USER_SCHOOL_SN'], PDO::PARAM_STR);
	} else {
		$stmt->bindValue(':guc', $GLOBALS['USER_CODE'], PDO::PARAM_STR);
	}
	$stmt->execute();
	while ($row = $stmt->fetch()) {
		$CLASS_ID = $row['CLASS_ID'];
		$CLASS_NAME = $row['CLASS_NAME'];
		$cdm .= '<option value="' . $CLASS_ID . '"' . check_selected($CLASS_ID, $ITEM_VAL) . '>' . $CLASS_NAME . '</option>';
	}
	$cdm .= "</select>";

	return $cdm;
}


// class roster
function list_roster($db, $class_id)
{
	$query = "SELECT CLASS_NAME from config_classes WHERE CLASS_ID=:cid";
	$stmt = $db->prepare($query);
	$stmt->bindValue(':cid', $class_id, PDO::PARAM_INT);
	$stmt->execute();
	if ($stmt->rowCount() > 0) {
		$row = $stmt->fetch();
		$class_list = $row['CLASS_NAME'];
	}
	$class_list = "<b>" . $class_list . " Roster</b><br>";

	// show the prompt selection menu
	$class_list .= '<form id="qtassign" action="sch_roster.php?id=' . $GLOBALS["SESSION_ID"] . '&cid=' . $class_id . '" method="post" name="qtassign"><table><td><td>Assign the form to selected students&nbsp;</td>';
	$class_list .= "<td>" . qt_menu($db, "", "QTS") . "</td>";
	$class_list .= '<td><input type="submit"  class="waButtonSmall" value="Assign Form"></td></tr></table><br>';

	$class_list .= "<table><tr><td class='table_title'>Add</td><td class='table_title'>Name</td><td class='table_title'>Quick-Write</td></tr>";

	$query = "SELECT USER_CODE, USER_LAST_NAME, USER_FIRST_NAME from config_users WHERE USER_ID IN (select PUPIL_STUDENTID FROM config_pupils WHERE PUPIL_CLASSID=:cid) ORDER BY USER_LAST_NAME, USER_FIRST_NAME ";
	$stmt = $db->prepare($query);
	$stmt->bindValue(':cid', $class_id, PDO::PARAM_INT);
	$stmt->execute();
	while ($row = $stmt->fetch()) {
		$check_link = '<input type="checkbox" name="' . $row['USER_CODE'] . '" value="' . $row['USER_CODE'] . '">';
		$class_list .= "<tr><td align='center'>$check_link</td><td class='table_row'>" . $row['USER_LAST_NAME'] . ", " . $row['USER_FIRST_NAME'] . "</td><td class='table_row'>" . show_pupil_quizzes($db, $row['USER_CODE']) . "</td></tr>";
	}
	$class_list .= "</table></form";
	return $class_list;
}

function show_pupil_quizzes($db, $student_id)
{
	$quiz_count = 0;
	$quiz_list = "";
	$query = "SELECT PROMPT_SHORT_TITLE FROM quiz_prompts, quiz WHERE PROMPT_ID=Q_PROMPT_ID and Q_STUDENT_ID=:sid order by Q_ID";
	$stmt = $db->prepare($query);
	$stmt->bindValue(':sid', $student_id, PDO::PARAM_STR);
	$stmt->execute();
	while ($row = $stmt->fetch()) {
		$quiz_count++;
		if ($quiz_count <= 3) {
			$quiz_list .= $row['PROMPT_SHORT_TITLE'] . ", ";
		}
	}
	if ($quiz_count <= 3) $quiz_list = substr($quiz_list, 0, -2);
	if ($quiz_count > 3) {
		$quiz_count -= 3;
		$quiz_list .= "plus $quiz_count more";
	}
	return $quiz_list;
}

function assign_quizzes($db, $studentids, $QT_NAME)
{
	// NOTE: TAKEN FROM qt_menu(). MIGHT BE INCORRECT.
	$cpm = '<select id="' . $QT_NAME . '" name="' . $QT_NAME . '" size="1" required class="form-control"><option value="">Select</option>';
	// get quiz template
	$query = "SELECT QT_TITLE,QT_PROMPT_1,QT_PROMPT_2,QT_PROMPT_3 from quiz_template where QT_STATUS='ACTIVE' order by QT_TITLE ";
	$stmt = $db->prepare($query);
	$stmt->execute();
	while ($row = $stmt->fetch()) {
		$QT_TITLE = $row['QT_TITLE'];
		$QT_PROMPT_1 = $row['QT_PROMPT_1'];
		$QT_PROMPT_2 = $row['QT_PROMPT_2'];
		$QT_PROMPT_3 = $row['QT_PROMPT_3'];
		$prompt_list = " (" . $QT_PROMPT_1 . ", " . $QT_PROMPT_2 . ", " . $QT_PROMPT_3 . ")";
		$cpm .= '<option value="' . $QT_TITLE . '">' . $QT_TITLE . $prompt_list . '</option>';
	}
	$cpm .= "</select>";
	return $cpm;
}

function qt_menu($db, $ITEM_VAL, $ITEM_NAME)
{
	$cpm = '<select id="' . $ITEM_NAME . '" name="' . $ITEM_NAME . '" size="1" required class="form-control"><option value="">Select</option>';

	$query = "SELECT QT_TITLE,QT_PROMPT_1,QT_PROMPT_2,QT_PROMPT_3 from quiz_template where QT_STATUS='ACTIVE' order by QT_TITLE ";
	$stmt = $db->prepare($query);
	$stmt->execute();
	while ($row = $stmt->fetch()) {
		$QT_TITLE = $row['QT_TITLE'];
		$QT_PROMPT_1 = $row['QT_PROMPT_1'];
		$QT_PROMPT_2 = $row['QT_PROMPT_2'];
		$QT_PROMPT_3 = $row['QT_PROMPT_3'];
		$prompt_list = " (" . $QT_PROMPT_1 . ", " . $QT_PROMPT_2 . ", " . $QT_PROMPT_3 . ")";
		$cpm .= '<option value="' . $QT_TITLE . '">' . $QT_TITLE . $prompt_list . '</option>';
	}
	$cpm .= "</select>";
	return $cpm;
}
