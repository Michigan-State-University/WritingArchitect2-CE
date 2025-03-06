<?php
class QUIZ
{
	private $conn;

	// Properties
	public $Q_ID;
	public $Q_PROMPT_ID;
	public $Q_PROMPT_TITLE; // Title of the quiz
	public $Q_STUDENT_ID;  // student code
	public $Q_START_TIME;  // start date and time
	public $Q_END_TIME;  // end date and time
	public $Q_DURATION;  // duration in time
	public $Q_COMPLETED;  // Completed
	public $Q_ESSAY;  // essay
	public $Q_TYPING;  // typing test
	public $Q_WORD_COUNT;  // word count
	public $Q_SENTENCE_COUNT;  // sentence count
	public $Q_WORD_ERROR;  // word errors
	public $Q_SENTENCE_ERROR;  // sentence errors
	public $Q_CIWS;  // CIWS
	public $Q_WORD_ACCURACY;  // word accuracy
	public $Q_SENTENCE_ACCURACY;  // sentence accuracy
	public $Q_WORD_COMPLEXITY;  // word complexity
	public $Q_SENTENCE_COMPLEXITY;  // sentence complexity
	public $Q_SCORING;  // the essay in scored format

	public $Q_ESSAY_NOTES;  // essay notes
	public $Q_TYPING_NOTES;  // typing notes
	public $Q_TYPE_WORD_COUNT;  // Typing word count from passage
	public $Q_CHARACTER_COUNT;  // Typing Character count from passage
	public $Q_TYPING_CORRECT;  // Correctly typed words
	public $Q_TYPING_WORDS;  // typed words
	public $Q_TYPING_CHARS;  // typed characters
	public $Q_GRADING_STATUS;  // status of the grading
	public $Q_GRADER_ID;  // ID of the Grader
	public $Q_PLANNING;  // Grade for Planning

	public $Q_TIDE;  // TIDE markup
	public $Q_TIDE_T;  // TIDE score T
	public $Q_TIDE_I;  // TIDE score I
	public $Q_TIDE_D;  // TIDE score D
	public $Q_TIDE_E;  // TIDE score E
	public $Q_TIDE_C;  // TIDE score C
	public $Q_MODIFIED_ON;  // modified date and time

	public $QUIZZES_TAKEN;  // not in DB
	public $QUIZZES_REMAINING;  // not in DB

	// Constructor with DB
	public function __contruct($db)
	{
		$this->conn = $db;
	}

	// save single quiz
	public function save_quiz_tide($db, $quiz_id)
	{
		$SQL_Q_ESSAY = addslashes($this->Q_ESSAY);
		$SQL_Q_TYPING = addslashes($this->Q_TYPING);
		$SQL_Q_SCORING = addslashes($this->Q_SCORING);
		$SQL_Q_ESSAY_NOTES = addslashes($this->Q_ESSAY_NOTES);
		$SQL_Q_TYPING_NOTES = addslashes($this->Q_TYPING_NOTES);
		$Q_TIDE = addslashes($this->Q_TIDE);

		// Quiz is already created, we only have to update the parts that are saved. 
		$dataval = "";
		if ($SQL_Q_ESSAY != "") $dataval .= "Q_ESSAY='$SQL_Q_ESSAY',";
		if ($SQL_Q_TYPING != "") $dataval .= "Q_TYPING='$SQL_Q_TYPING',";
		if ($SQL_Q_ESSAY_NOTES != "") $dataval .= "Q_ESSAY_NOTES='$SQL_Q_ESSAY_NOTES',";
		if ($SQL_Q_TYPING_NOTES != "") $dataval .= "Q_TYPING_NOTES='$SQL_Q_TYPING_NOTES',";
		if ($SQL_Q_SCORING != "") $dataval .= "Q_SCORING='$SQL_Q_SCORING',";

		$this->Q_WORD_ACCURACY = str_replace("##", "", $this->Q_WORD_ACCURACY);
		$this->Q_SENTENCE_ACCURACY = str_replace("##", "", $this->Q_SENTENCE_ACCURACY);
		$this->Q_SENTENCE_COMPLEXITY = str_replace("##", "", $this->Q_SENTENCE_COMPLEXITY);
		if ($this->Q_START_TIME != "") $dataval .= "Q_START_TIME='$this->Q_START_TIME',";
		if ($this->Q_END_TIME != "") $dataval .= "Q_END_TIME='$this->Q_END_TIME',";
		if ($this->Q_END_TIME != "" && $this->Q_END_TIME != "") {
			$duration = $this->calc_duration($this->Q_START_TIME, $this->Q_END_TIME);
			$dataval .= "Q_DURATION='$duration',";
		}
		if ($this->Q_COMPLETED != "") $dataval .= "Q_COMPLETED='$this->Q_COMPLETED',";
		if ($this->Q_GRADING_STATUS != "") $dataval .= "Q_GRADING_STATUS='$this->Q_GRADING_STATUS',";
		if ($this->Q_WORD_COUNT != "") $dataval .= "Q_WORD_COUNT=$this->Q_WORD_COUNT,";
		if ($this->Q_SENTENCE_COUNT != "") $dataval .= "Q_SENTENCE_COUNT=$this->Q_SENTENCE_COUNT,";
		if ($this->Q_WORD_ERROR != "") $dataval .= "Q_WORD_ERROR=$this->Q_WORD_ERROR,";
		if ($this->Q_SENTENCE_ERROR != "") $dataval .= "Q_SENTENCE_ERROR=$this->Q_SENTENCE_ERROR,";
		if ($this->Q_CIWS != "") $dataval .= "Q_CIWS=$this->Q_CIWS,";
		if ($this->Q_WORD_ACCURACY != "") $dataval .= "Q_WORD_ACCURACY=$this->Q_WORD_ACCURACY,";
		if ($this->Q_SENTENCE_ACCURACY != "") $dataval .= "Q_SENTENCE_ACCURACY=$this->Q_SENTENCE_ACCURACY,";
		if ($this->Q_WORD_COMPLEXITY != "") $dataval .= "Q_WORD_COMPLEXITY=$this->Q_WORD_COMPLEXITY,";
		if ($this->Q_SENTENCE_COMPLEXITY != "") $dataval .= "Q_SENTENCE_COMPLEXITY=$this->Q_SENTENCE_COMPLEXITY,";
		if ($this->Q_TYPE_WORD_COUNT != "") $dataval .= "Q_TYPE_WORD_COUNT=$this->Q_TYPE_WORD_COUNT,";
		if ($this->Q_CHARACTER_COUNT != "") $dataval .= "Q_CHARACTER_COUNT=$this->Q_CHARACTER_COUNT,";
		if ($this->Q_TYPING_CORRECT != "") {
			$this->Q_TYPING_CORRECT = filter_var($this->Q_TYPING_CORRECT, FILTER_SANITIZE_NUMBER_INT);

			$dataval .= "Q_TYPING_CORRECT=$this->Q_TYPING_CORRECT,";
		}
		if ($this->Q_TYPING_WORDS != "") $dataval .= "Q_TYPING_WORDS=$this->Q_TYPING_WORDS,";
		if ($this->Q_TYPING_CHARS != "") $dataval .= "Q_TYPING_CHARS=$this->Q_TYPING_CHARS,";
		if ($this->Q_GRADER_ID != "") $dataval .= "Q_GRADER_ID='$this->Q_GRADER_ID',";
		if ($this->Q_PLANNING != "") $dataval .= "Q_PLANNING='$this->Q_PLANNING',";

		if ($this->Q_TIDE_T != "") $dataval .= "Q_TIDE_T='$this->Q_TIDE_T',";
		if ($this->Q_TIDE_I != "") $dataval .= "Q_TIDE_I='$this->Q_TIDE_I',";
		if ($this->Q_TIDE_D != "") $dataval .= "Q_TIDE_D='$this->Q_TIDE_D',";
		if ($this->Q_TIDE_E != "") $dataval .= "Q_TIDE_E='$this->Q_TIDE_E',";
		if ($this->Q_TIDE_C != "") $dataval .= "Q_TIDE_C='$this->Q_TIDE_C',";




		$dataval .= "Q_MODIFIED_BY=:guc, Q_MODIFIED_ON=:qmo";
		$query = "UPDATE quiz SET " . $dataval . " WHERE Q_ID=:qid";
		//echo "<--".$query."-->";
		$stmt = $db->prepare($query);
		$stmt->bindValue(':guc', $GLOBALS['USER_CODE'], PDO::PARAM_STR);
		$stmt->bindValue(':qmo', date('Y-m-d H:i:s'), PDO::PARAM_STR);
		$stmt->bindValue(':qid', $quiz_id, PDO::PARAM_INT);
		$stmt->execute();
		return "";
	}

	// load single quiz
	public function load_quiz($db, $quiz_id)
	{
		$query = "SELECT * from quiz where Q_ID=:qid";
		$stmt = $db->prepare($query);
		$stmt->bindValue(':qid', $quiz_id, PDO::PARAM_INT);
		$stmt->execute();
		if ($stmt->rowCount() > 0) {
			$row = $stmt->fetch();
			$this->Q_ID = $row['Q_ID'];
			$this->Q_PROMPT_ID = $row['Q_PROMPT_ID'];
			$this->Q_PROMPT_TITLE = $row['Q_PROMPT_TITLE'];
			$this->Q_STUDENT_ID = $row['Q_STUDENT_ID'];
			$this->Q_START_TIME = $row['Q_START_TIME'];
			$this->Q_END_TIME = $row['Q_END_TIME'];
			$this->Q_DURATION = $row['Q_DURATION'];
			$this->Q_COMPLETED = $row['Q_COMPLETED'];
			$this->Q_ESSAY = $row['Q_ESSAY'];
			$this->Q_TYPING = $row['Q_TYPING'];
			$this->Q_WORD_COUNT = $row['Q_WORD_COUNT'];
			$this->Q_SENTENCE_COUNT = $row['Q_SENTENCE_COUNT'];
			$this->Q_WORD_ERROR = $row['Q_WORD_ERROR'];
			$this->Q_SENTENCE_ERROR = $row['Q_SENTENCE_ERROR'];
			$this->Q_CIWS = $row['Q_CIWS'];
			$this->Q_WORD_ACCURACY = $row['Q_WORD_ACCURACY'];
			$this->Q_SENTENCE_ACCURACY = $row['Q_SENTENCE_ACCURACY'];
			$this->Q_WORD_COMPLEXITY = $row['Q_WORD_COMPLEXITY'];
			$this->Q_SENTENCE_COMPLEXITY = $row['Q_SENTENCE_COMPLEXITY'];
			$this->Q_SCORING = $row['Q_SCORING'];
			$this->Q_ESSAY_NOTES = $row['Q_ESSAY_NOTES'];
			$this->Q_TYPING_NOTES = $row['Q_TYPING_NOTES'];
			$this->Q_TYPE_WORD_COUNT = $row['Q_TYPE_WORD_COUNT'];
			$this->Q_CHARACTER_COUNT = $row['Q_CHARACTER_COUNT'];
			$this->Q_TYPING_CORRECT = $row['Q_TYPING_CORRECT'];
			$this->Q_TYPING_WORDS = $row['Q_TYPING_WORDS'];
			$this->Q_TYPING_CHARS = $row['Q_TYPING_CHARS'];
			$this->Q_GRADING_STATUS = $row['Q_GRADING_STATUS'];
			$this->Q_GRADER_ID = $row['Q_GRADER_ID'];
			$this->Q_MODIFIED_ON = $row['Q_MODIFIED_ON'];
			$this->Q_PLANNING = $row['Q_PLANNING'];

			$this->Q_TIDE = $row['Q_TIDE'];
			$this->Q_TIDE_T = $row['Q_TIDE_T'];
			$this->Q_TIDE_I = $row['Q_TIDE_I'];
			$this->Q_TIDE_D = $row['Q_TIDE_D'];
			$this->Q_TIDE_E = $row['Q_TIDE_E'];
			$this->Q_TIDE_C = $row['Q_TIDE_C'];
			return true;
		} else {
			return false;
		}
	}

	// load quiz counts
	public function calc_duration($start_time, $end_time)
	{
		$s1 = strtotime($start_time);
		$e2 = strtotime($end_time);
		$interval = $e2 - $s1;
		$mins = floor($interval / 60);
		$secs = $interval % 60;
		return "00:" . $mins . ":" . $secs;
	}

	// load next quiz
	public function load_next_quiz($db, $student_id)
	{
		$query = "SELECT Q_ID, Q_PROMPT_TITLE, Q_PROMPT_ID from quiz where Q_STUDENT_ID=:sid and Q_COMPLETED is null ORDER BY Q_ID";
		$stmt = $db->prepare($query);
		$stmt->bindValue(':sid', $student_id, PDO::PARAM_STR);
		$stmt->execute();
		if ($stmt->rowCount() > 0) {
			$row = $stmt->fetch();
			$this->Q_ID = $row['Q_ID'];
			$this->Q_PROMPT_TITLE = $row['Q_PROMPT_TITLE'];
			$this->Q_PROMPT_ID = $row['Q_PROMPT_ID'];
			return true;
		} else {
			return false;
		}
	}

	public function load_future_quizzes($db, $student_id)
	{
		$quiz_list = "<table width='90%'><tr><td>&nbsp;&nbsp;<b>Upcoming Quizzes</b></td></tr>";
		$query = "SELECT Q_ID, Q_PROMPT_TITLE, Q_PROMPT_ID from quiz where Q_STUDENT_ID=:sid and Q_COMPLETED is null ORDER BY Q_ID";
		$stmt = $db->prepare($query);
		$stmt->bindValue(':sid', $student_id, PDO::PARAM_STR);
		$stmt->execute();
		$isfirstrow = true;
		while ($row = $stmt->fetch()) {
			if ($isfirstrow) $isfirstrow = false;
			else $quiz_list .= "<tr><td><b><font color='GREY'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $row['Q_PROMPT_TITLE'] . "</font></b></td></tr>";
		}
		$quiz_list .= "</table>";
		return $quiz_list;
	}

	public function load_pending_quizzes($db, $teacherid)
	{
		$quiz_list = "<table width='90%'><tr><td colspan='5'>&nbsp;&nbsp;<b>Pending Quick-Writes</b></td></tr><tr><td class='table_title'>Actions</td><td class='table_title'>Last Name</td><td class='table_title'>First Name</td><td  class='table_title'>Class</td><td class='table_title'>Quick-Write</td></tr>";
		$query = "SELECT Q_ID, Q_PROMPT_TITLE, Q_PROMPT_ID, USER_LAST_NAME, USER_FIRST_NAME, CLASS_NAME from quiz join v_students_in_class on Q_STUDENT_ID=USER_CODE where CLASS_TEACHER_ID=:tid and Q_COMPLETED is null and USER_STATUS='ACTIVE' ORDER BY USER_LAST_NAME, USER_FIRST_NAME";
		//$quiz_list = $query;
		$stmt = $db->prepare($query);
		$stmt->bindValue(':tid', $teacherid, PDO::PARAM_STR);
		$stmt->execute();
		$isfirstrow = true;
		while ($row = $stmt->fetch()) {
			$delete_link = '<a href="" onclick="delete_record(\'' . $row['Q_ID'] . '\');return false;"><img src="../images/icn_delete.png" height="16" width="16"></a>';
			$quiz_list .= "<tr><td>$delete_link</td><td>" . $row['USER_LAST_NAME'] . "</td><td>" . $row['USER_FIRST_NAME'] . "</td><td>" . $row['CLASS_NAME'] . "</td><td>" . $row['Q_PROMPT_TITLE'] . "</td></tr>";
		}
		$quiz_list .= "</table>";

		return $quiz_list;
	}

	public function load_completed_quizzes($db, $orgid)
	{
		$quiz_list = "<table width='90%'><tr><td colspan='5'>&nbsp;&nbsp;<b>Score Quick-Writes</b></td></tr><tr><td class='table_title'>Actions</td><td class='table_title'>Status</td><td class='table_title'>Last Name</td><td class='table_title'>First Name</td><td  class='table_title'>Class</td><td class='table_title'>Quick-Write</td></tr>";
		if ($orgid == "") $query = "SELECT Q_ID, Q_PROMPT_TITLE, Q_PROMPT_ID, Q_GRADING_STATUS, USER_LAST_NAME, USER_FIRST_NAME, CLASS_NAME from quiz join v_students_in_class on Q_STUDENT_ID=USER_CODE where Q_COMPLETED is not null and USER_STATUS='ACTIVE' ORDER BY USER_LAST_NAME, USER_FIRST_NAME";
		else {
			if ($GLOBALS['USER_LEVEL'] == 'TEACHER') {
				$query = "SELECT Q_ID, Q_PROMPT_TITLE, Q_PROMPT_ID, Q_GRADING_STATUS, USER_LAST_NAME, USER_FIRST_NAME, CLASS_NAME from quiz join v_students_in_class on Q_STUDENT_ID=USER_CODE where CLASS_TEACHER_ID=:tid and Q_COMPLETED is not null and USER_STATUS='ACTIVE' ORDER BY USER_LAST_NAME, USER_FIRST_NAME";
			} else {
				$query = "SELECT Q_ID, Q_PROMPT_TITLE, Q_PROMPT_ID, Q_GRADING_STATUS, USER_LAST_NAME, USER_FIRST_NAME, CLASS_NAME from quiz join v_students_in_class on Q_STUDENT_ID=USER_CODE where CLASS_TEACHER_ID in (select USER_CODE FROM config_users where USER_ORGANIZATION=:oid) and Q_COMPLETED is not null and USER_STATUS='ACTIVE' ORDER BY USER_LAST_NAME, USER_FIRST_NAME";
			}
		}
		//$quiz_list = $query;
		$stmt = $db->prepare($query);
		if ($orgid != "") {
			if ($GLOBALS['USER_LEVEL'] == 'TEACHER') {
				$stmt->bindValue(':tid', $GLOBALS['USER_CODE'], PDO::PARAM_STR);
			} else {
				$stmt->bindValue(':oid', $orgid, PDO::PARAM_STR);
			}
			$stmt->execute();
			$isfirstrow = true;
			while ($row = $stmt->fetch()) {
				$edit_link = '<a href="score_quiz.php?sid=' . $row['Q_ID'] . '&id=' . $GLOBALS["SESSION_ID"] . '" target="scorequiz"><img src="../images/icn_grading.png"></a>';
				$quiz_list .= "<tr><td align='center'>$edit_link</td><td>" . $row['Q_GRADING_STATUS'] . "</td><td>" . $row['USER_LAST_NAME'] . "</td><td>" . $row['USER_FIRST_NAME'] . "</td><td>" . $row['CLASS_NAME'] . "</td><td>" . $row['Q_PROMPT_TITLE'] . "</td></tr>";
			}
			$quiz_list .= "</table>";

			return $quiz_list;
		}
	}


	// load quiz counts
	public function get_quiz_counts($db, $student_id)
	{
		$query = "SELECT count(Q_ID) as cnt from quiz where Q_STUDENT_ID=:sid and Q_COMPLETED=1";
		$stmt = $db->prepare($query);
		$stmt->bindValue(':sid', $student_id, PDO::PARAM_STR);
		$stmt->execute();
		$row = $stmt->fetch();
		$this->QUIZZES_TAKEN = $row['cnt'];

		$query = "SELECT count(Q_ID) as cnt from quiz where Q_STUDENT_ID=:sid and Q_COMPLETED is null";
		$stmt = $db->prepare($query);
		$stmt->bindValue(':sid', $student_id, PDO::PARAM_STR);
		$stmt->execute();
		$row = $stmt->fetch();
		$this->QUIZZES_REMAINING = $row['cnt'];
	}
}
