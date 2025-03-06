<?php
class quiz
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

	public $Q_TIDE;  // TIDE Markup
	public $Q_TIDE_SCORING;  // TIDE scoring table
	public $Q_TIDE_T;  // Score T
	public $Q_TIDE_I;  // Score I
	public $Q_TIDE_D;  // Score D
	public $Q_TIDE_E;  // Score E
	public $Q_TIDE_C;  // Score C
	public $Q_VERSION;  // Duplicate Version
	public $Q_MODIFIED_ON;  // Modified date and time

	// Added for fine-grained token counts
	public $Q_TOKEN_CORRECT;
	public $Q_TOKEN_WORD;
	public $Q_TOKEN_SEN_INACC;
	public $Q_TOKEN_SEN_OVERLAP;
	public $Q_TOKEN_SEN_NMAE;

	public $QUIZZES_TAKEN;  // not in DB
	public $QUIZZES_REMAINING;  // not in DB

	// Constructor with DB
	public function __contruct($db)
	{
		$this->conn = $db;
	}

	// save single quiz
	public function save_quiz($db, $quiz_id)
	{
		// Replaces null with empty string
		if ($this->Q_ESSAY == null) $this->Q_ESSAY = "";
		if ($this->Q_TYPING == null) $this->Q_TYPING = "";
		if ($this->Q_SCORING == null) $this->Q_SCORING = "";
		if ($this->Q_ESSAY_NOTES == null) $this->Q_ESSAY_NOTES = "";
		if ($this->Q_TYPING_NOTES == null) $this->Q_TYPING_NOTES = "";
		if ($this->Q_TIDE == null) $this->Q_TIDE = "";
		if ($this->Q_TIDE_SCORING == null) $this->Q_TIDE_SCORING = "";

		$SQL_Q_ESSAY = $this->Q_ESSAY;
		$SQL_Q_TYPING = $this->Q_TYPING;
		$SQL_Q_TYPE_WORD_COUNT = str_word_count($SQL_Q_TYPING, 0);
		$SQL_Q_CHARACTER_COUNT = strlen($SQL_Q_TYPING);
		$SQL_Q_SCORING = addslashes($this->Q_SCORING);
		$SQL_Q_ESSAY_NOTES = addslashes($this->Q_ESSAY_NOTES);
		$SQL_Q_TYPING_NOTES = addslashes($this->Q_TYPING_NOTES);
		$Q_TIDE = $this->Q_TIDE;
		$Q_TIDE_SCORING = $this->Q_TIDE_SCORING;

		// Quiz is already created, we only have to update the parts that are saved. 
		$dataval = "";
		if ($SQL_Q_ESSAY != "") $dataval .= "Q_ESSAY=:sql_q_essay,";
		if ($SQL_Q_TYPING != "") $dataval .= "Q_TYPING=:sql_q_typing,";
		if ($SQL_Q_ESSAY_NOTES != "") $dataval .= "Q_ESSAY_NOTES=:sql_q_essay_notes,";
		if ($SQL_Q_TYPING_NOTES != "") $dataval .= "Q_TYPING_NOTES=:sql_q_typing_notes,";
		if ($SQL_Q_SCORING != "") $dataval .= "Q_SCORING=:sql_q_scoring,";
		$dataval .= "Q_TYPE_WORD_COUNT=:sql_q_type_word_count,";
		$dataval .= "Q_CHARACTER_COUNT=:sql_q_character_count,";

		// Replaces null with empty string
		if ($this->Q_WORD_ACCURACY == null) $this->Q_WORD_ACCURACY = "";
		if ($this->Q_SENTENCE_ACCURACY == null) $this->Q_SENTENCE_ACCURACY = "";
		if ($this->Q_SENTENCE_COMPLEXITY == null) $this->Q_SENTENCE_COMPLEXITY = "";

		$this->Q_WORD_ACCURACY = str_replace("##", "", $this->Q_WORD_ACCURACY);
		$this->Q_SENTENCE_ACCURACY = str_replace("##", "", $this->Q_SENTENCE_ACCURACY);
		$this->Q_SENTENCE_COMPLEXITY = str_replace("##", "", $this->Q_SENTENCE_COMPLEXITY);
		if ($this->Q_START_TIME != "") $dataval .= "Q_START_TIME=:q_start_time,";
		if ($this->Q_END_TIME != "") $dataval .= "Q_END_TIME=:q_end_time,";
		$duration = "";
		if ($this->Q_END_TIME != "" && $this->Q_END_TIME != "") {
			$duration = $this->calc_duration($this->Q_START_TIME, $this->Q_END_TIME);
			$dataval .= "Q_DURATION=:q_duration,";
		}
		if ($this->Q_COMPLETED != "") $dataval .= "Q_COMPLETED=:q_completed,";
		if ($this->Q_GRADING_STATUS != "") $dataval .= "Q_GRADING_STATUS=:q_grading_status,";
		if ($this->Q_WORD_COUNT != "") $dataval .= "Q_WORD_COUNT=:q_word_count,";
		if ($this->Q_SENTENCE_COUNT != "") $dataval .= "Q_SENTENCE_COUNT=:q_sentence_count,";
		if ($this->Q_WORD_ERROR != "") $dataval .= "Q_WORD_ERROR=:q_word_error,";
		if ($this->Q_SENTENCE_ERROR != "") $dataval .= "Q_SENTENCE_ERROR=:q_sentence_error,";
		if ($this->Q_CIWS != "") $dataval .= "Q_CIWS=:q_ciws,";
		if ($this->Q_WORD_ACCURACY != "") $dataval .= "Q_WORD_ACCURACY=:q_word_accuracy,";
		if ($this->Q_SENTENCE_ACCURACY != "") $dataval .= "Q_SENTENCE_ACCURACY=:q_sentence_accuracy,";
		if ($this->Q_WORD_COMPLEXITY != "") $dataval .= "Q_WORD_COMPLEXITY=:q_word_complexity,";
		if ($this->Q_SENTENCE_COMPLEXITY != "") $dataval .= "Q_SENTENCE_COMPLEXITY=:q_sentence_complexity,";
		if ($this->Q_TYPE_WORD_COUNT != "") $dataval .= "Q_TYPE_WORD_COUNT=:q_type_word_count,";
		if ($this->Q_CHARACTER_COUNT != "") $dataval .= "Q_CHARACTER_COUNT=:q_character_count,";
		if ($this->Q_TYPING_CORRECT != "") {
			$this->Q_TYPING_CORRECT = filter_var($this->Q_TYPING_CORRECT, FILTER_SANITIZE_NUMBER_INT);

			$dataval .= "Q_TYPING_CORRECT=:q_typing_correct,";
		}
		if ($this->Q_TYPING_WORDS != "") $dataval .= "Q_TYPING_WORDS=:q_typing_words,";
		if ($this->Q_TYPING_CHARS != "") $dataval .= "Q_TYPING_CHARS=:q_typing_chars,";
		if ($this->Q_GRADER_ID != "") $dataval .= "Q_GRADER_ID=:q_grader_id,";
		if ($this->Q_PLANNING != "") $dataval .= "Q_PLANNING=:q_planning,";

		// Added for fine-grained token counts
		$dataval .= "Q_TOKEN_CORRECT=:q_token_correct,";
		$dataval .= "Q_TOKEN_WORD=:q_token_word,";
		$dataval .= "Q_TOKEN_SEN_INACC=:q_token_sen_inacc,";
		$dataval .= "Q_TOKEN_SEN_OVERLAP=:q_token_sen_overlap,";
		$dataval .= "Q_TOKEN_SEN_NMAE=:q_token_sen_nmae,";

		if ($Q_TIDE != "") $dataval .= "Q_TIDE=:q_tide,";
		if ($Q_TIDE_SCORING != "") $dataval .= "Q_TIDE_SCORING=:q_tide_scoring,";
		if ($this->Q_TIDE_T != "") $dataval .= "Q_TIDE_T=:q_tide_t,";
		if ($this->Q_TIDE_I != "") $dataval .= "Q_TIDE_I=:q_tide_i,";
		if ($this->Q_TIDE_D != "") $dataval .= "Q_TIDE_D=:q_tide_d,";
		if ($this->Q_TIDE_E != "") $dataval .= "Q_TIDE_E=:q_tide_e,";
		if ($this->Q_TIDE_C != "") $dataval .= "Q_TIDE_C=:q_tide_c,";

		$dataval .= "Q_MODIFIED_BY=:q_modified_by, Q_MODIFIED_ON=:q_modified_on";

		$query = "UPDATE quiz SET " . $dataval . " WHERE Q_ID=:q_id";
		//echo "<--".$query."-->";
		$stmt = $db->prepare($query);
		if ($SQL_Q_ESSAY != "") $stmt->bindValue(':sql_q_essay', $SQL_Q_ESSAY, PDO::PARAM_STR);
		if ($SQL_Q_TYPING != "") $stmt->bindValue(':sql_q_typing', $SQL_Q_TYPING, PDO::PARAM_STR);
		$stmt->bindValue(':sql_q_type_word_count', $SQL_Q_TYPE_WORD_COUNT, PDO::PARAM_INT);
		$stmt->bindValue(':sql_q_character_count', $SQL_Q_CHARACTER_COUNT, PDO::PARAM_INT);
		if ($SQL_Q_ESSAY_NOTES != "") $stmt->bindValue(':sql_q_essay_notes', $SQL_Q_ESSAY_NOTES, PDO::PARAM_STR);
		if ($SQL_Q_TYPING_NOTES != "") $stmt->bindValue(':sql_q_typing_notes', $SQL_Q_TYPING_NOTES, PDO::PARAM_STR);
		if ($SQL_Q_SCORING != "") $stmt->bindValue(':sql_q_scoring', $SQL_Q_SCORING);

		if ($this->Q_START_TIME != "") $stmt->bindValue(':q_start_time', $this->Q_START_TIME, PDO::PARAM_STR);
		if ($this->Q_END_TIME != "") $stmt->bindValue(':q_end_time', $this->Q_END_TIME, PDO::PARAM_STR);
		if ($this->Q_END_TIME != "" && $this->Q_END_TIME != "") $stmt->bindValue(':q_duration', $duration, PDO::PARAM_STR);
		if ($this->Q_COMPLETED != "") $stmt->bindValue(':q_completed', $this->Q_COMPLETED, PDO::PARAM_STR);
		if ($this->Q_GRADING_STATUS != "") $stmt->bindValue(':q_grading_status', $this->Q_GRADING_STATUS, PDO::PARAM_STR);
		if ($this->Q_WORD_COUNT != "") $stmt->bindValue(':q_word_count', $this->Q_WORD_COUNT, PDO::PARAM_INT);
		if ($this->Q_SENTENCE_COUNT != "") $stmt->bindValue(':q_sentence_count', $this->Q_SENTENCE_COUNT, PDO::PARAM_INT);
		if ($this->Q_WORD_ERROR != "") $stmt->bindValue(':q_word_error', $this->Q_WORD_ERROR, PDO::PARAM_INT);
		if ($this->Q_SENTENCE_ERROR != "") $stmt->bindValue(':q_sentence_error', $this->Q_SENTENCE_ERROR, PDO::PARAM_INT);
		if ($this->Q_CIWS != "") $stmt->bindValue(':q_ciws', $this->Q_CIWS, PDO::PARAM_INT);
		if ($this->Q_WORD_ACCURACY != "") $stmt->bindValue(':q_word_accuracy', $this->Q_WORD_ACCURACY, PDO::PARAM_STR);
		if ($this->Q_SENTENCE_ACCURACY != "") $stmt->bindValue(':q_sentence_accuracy', $this->Q_SENTENCE_ACCURACY, PDO::PARAM_STR);
		if ($this->Q_WORD_COMPLEXITY != "") $stmt->bindValue(':q_word_complexity', $this->Q_WORD_COMPLEXITY, PDO::PARAM_STR);
		if ($this->Q_SENTENCE_COMPLEXITY != "") $stmt->bindValue(':q_sentence_complexity', $this->Q_SENTENCE_COMPLEXITY, PDO::PARAM_STR);
		if ($this->Q_TYPE_WORD_COUNT != "") $stmt->bindValue(':q_type_word_count', $this->Q_TYPE_WORD_COUNT, PDO::PARAM_INT);
		if ($this->Q_CHARACTER_COUNT != "") $stmt->bindValue(':q_character_count', $this->Q_CHARACTER_COUNT, PDO::PARAM_INT);
		if ($this->Q_TYPING_CORRECT != "") $stmt->bindValue(':q_typing_correct', $this->Q_TYPING_CORRECT, PDO::PARAM_INT);
		if ($this->Q_TYPING_WORDS != "") $stmt->bindValue(':q_typing_words', $this->Q_TYPING_WORDS, PDO::PARAM_INT);
		if ($this->Q_TYPING_CHARS != "") $stmt->bindValue(':q_typing_chars', $this->Q_TYPING_CHARS, PDO::PARAM_INT);
		if ($this->Q_GRADER_ID != "") $stmt->bindValue(':q_grader_id', $this->Q_GRADER_ID, PDO::PARAM_STR);
		if ($this->Q_PLANNING != "") $stmt->bindValue(':q_planning', $this->Q_PLANNING, PDO::PARAM_STR);

		// Added for fine-grained token counts
		$stmt->bindValue(':q_token_correct', $this->Q_TOKEN_CORRECT, PDO::PARAM_INT);
		$stmt->bindValue(':q_token_word', $this->Q_TOKEN_WORD, PDO::PARAM_INT);
		$stmt->bindValue(':q_token_sen_inacc', $this->Q_TOKEN_SEN_INACC, PDO::PARAM_INT);
		$stmt->bindValue(':q_token_sen_overlap', $this->Q_TOKEN_SEN_OVERLAP, PDO::PARAM_INT);
		$stmt->bindValue(':q_token_sen_nmae', $this->Q_TOKEN_SEN_NMAE, PDO::PARAM_INT);

		if ($Q_TIDE != "") $stmt->bindValue(':q_tide', $Q_TIDE, PDO::PARAM_STR);
		if ($Q_TIDE_SCORING != "") $stmt->bindValue(':q_tide_scoring', $Q_TIDE_SCORING, PDO::PARAM_STR);
		if ($this->Q_TIDE_T != "") $stmt->bindValue(':q_tide_t', $this->Q_TIDE_T, PDO::PARAM_STR);
		if ($this->Q_TIDE_I != "") $stmt->bindValue(':q_tide_i', $this->Q_TIDE_I, PDO::PARAM_STR);
		if ($this->Q_TIDE_D != "") $stmt->bindValue(':q_tide_d', $this->Q_TIDE_D, PDO::PARAM_STR);
		if ($this->Q_TIDE_E != "") $stmt->bindValue(':q_tide_e', $this->Q_TIDE_E, PDO::PARAM_STR);
		if ($this->Q_TIDE_C != "") $stmt->bindValue(':q_tide_c', $this->Q_TIDE_C, PDO::PARAM_STR);

		$stmt->bindValue(':q_modified_by', $this->Q_GRADER_ID, PDO::PARAM_STR);
		$stmt->bindValue(':q_modified_on', date('Y-m-d H:i:s'), PDO::PARAM_STR);
		$stmt->bindValue(':q_id', $quiz_id, PDO::PARAM_INT);

		$stmt->execute();
		return "";
	}

	// load single quiz
	public function load_quiz($db, $quiz_id)
	{
		$query = "SELECT * from quiz where Q_ID=:quiz_id";
		$stmt = $db->prepare($query);
		$stmt->bindValue(':quiz_id', $quiz_id, PDO::PARAM_INT);
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

			// Added for fine-grained token counts
			$this->Q_TOKEN_CORRECT = $row['Q_TOKEN_CORRECT'];
			$this->Q_TOKEN_WORD = $row['Q_TOKEN_WORD'];
			$this->Q_TOKEN_SEN_INACC = $row['Q_TOKEN_SEN_INACC'];
			$this->Q_TOKEN_SEN_OVERLAP = $row['Q_TOKEN_SEN_OVERLAP'];
			$this->Q_TOKEN_SEN_NMAE = $row['Q_TOKEN_SEN_NMAE'];

			$this->Q_TIDE = $row['Q_TIDE'];
			$this->Q_TIDE_SCORING = $row['Q_TIDE_SCORING'];
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
		$query = "SELECT Q_ID, Q_PROMPT_TITLE, Q_PROMPT_ID from quiz where Q_STUDENT_ID=:student_id and Q_COMPLETED is null ORDER BY Q_ID";
		$stmt = $db->prepare($query);
		$stmt->bindValue(':student_id', $student_id, PDO::PARAM_STR);
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
		$query = "SELECT Q_ID, Q_PROMPT_TITLE, Q_PROMPT_ID from quiz where Q_STUDENT_ID=:student_id and Q_COMPLETED is null ORDER BY Q_ID";
		$stmt = $db->prepare($query);
		$stmt->bindValue(':student_id', $student_id, PDO::PARAM_STR);
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
		if ($GLOBALS['USER_LEVEL'] == 'TEACHER') {
			$query = "SELECT Q_ID, Q_PROMPT_TITLE, Q_PROMPT_ID, USER_LAST_NAME, USER_FIRST_NAME, CLASS_NAME from quiz join v_students_in_class on Q_STUDENT_ID=USER_CODE where CLASS_TEACHER_ID=:teacherid and Q_COMPLETED is null and USER_STATUS='ACTIVE' ORDER BY USER_LAST_NAME, USER_FIRST_NAME";
		} else {
			$query = "SELECT Q_ID, Q_PROMPT_TITLE, Q_PROMPT_ID, USER_LAST_NAME, USER_FIRST_NAME, CLASS_NAME from quiz join v_students_in_class on Q_STUDENT_ID=USER_CODE where CLASS_TEACHER_ID in (select user_code from config_users where user_organization=:guo) and Q_COMPLETED is null and USER_STATUS='ACTIVE' ORDER BY USER_LAST_NAME, USER_FIRST_NAME";
		}
		//$quiz_list = $query;
		$stmt = $db->prepare($query);
		if ($GLOBALS['USER_LEVEL'] == 'TEACHER') {
			$stmt->bindValue(':teacherid', $teacherid, PDO::PARAM_STR);
		} else {
			$stmt->bindValue(':guo', $GLOBALS['USER_ORGANIZATION'], PDO::PARAM_STR);
		}
		$stmt->execute();
		$isfirstrow = true;
		while ($row = $stmt->fetch()) {
			$delete_link = '<a href="" onclick="delete_record(\'' . $row['Q_ID'] . '\');return false;"><img src="../images/icn_delete.png" height="16" width="16"></a>';
			$quiz_list .= "<tr><td>$delete_link</td><td>" . $row['USER_LAST_NAME'] . "</td><td>" . $row['USER_FIRST_NAME'] . "</td><td>" . $row['CLASS_NAME'] . "</td><td>" . $row['Q_PROMPT_TITLE'] . "</td></tr>";
		}
		$quiz_list .= "</table>";

		return $quiz_list;
	}

	public function load_completed_quizzes($db, $orgid, $comp)
	{
		switch ($comp) {
			case "LIVE":
				$score_title = "Score Quick-Writes";
				$stat_clause = " and Q_GRADING_STATUS in ('Submitted', 'In Progress')";
				break;
			case "PENDING":
				$score_title = "Pending Quick-Writes";
				$stat_clause = " and Q_GRADING_STATUS='Pending'";
				break;
			case "COMPLETED":
				$score_title = "Completed Quick-Writes";
				$stat_clause = " and Q_GRADING_STATUS='Completed'";
				break;
		}

		$quiz_list = "<table width='90%'><tr><td colspan='5'>&nbsp;&nbsp;<b>" . $score_title . "</b></td></tr><tr><td class='table_title'>Actions</td><td class='table_title'>Status</td><td class='table_title'>Last Name</td><td class='table_title'>First Name</td><td  class='table_title'>Class</td><td class='table_title'>Quick-Write</td></tr>";
		if ($orgid == "") $query = "SELECT Q_ID, Q_PROMPT_TITLE, Q_PROMPT_ID, Q_GRADING_STATUS, USER_LAST_NAME, USER_FIRST_NAME, CLASS_NAME, Q_VERSION from quiz join v_students_in_class on Q_STUDENT_ID=USER_CODE where Q_COMPLETED is not null and USER_STATUS='ACTIVE'" . $stat_clause . " ORDER BY USER_LAST_NAME, USER_FIRST_NAME";
		else {
			if ($GLOBALS['USER_LEVEL'] == 'TEACHER') {
				$query = "SELECT Q_ID, Q_PROMPT_TITLE, Q_PROMPT_ID, Q_GRADING_STATUS, USER_LAST_NAME, USER_FIRST_NAME, CLASS_NAME, Q_VERSION from quiz join v_students_in_class on Q_STUDENT_ID=USER_CODE where CLASS_TEACHER_ID=:guc and Q_COMPLETED is not null and USER_STATUS='ACTIVE'" . $stat_clause . " ORDER BY USER_LAST_NAME, USER_FIRST_NAME";
			} else {
				$query = "SELECT Q_ID, Q_PROMPT_TITLE, Q_PROMPT_ID, Q_GRADING_STATUS, USER_LAST_NAME, USER_FIRST_NAME, CLASS_NAME, Q_VERSION from quiz join v_students_in_class on Q_STUDENT_ID=USER_CODE where CLASS_TEACHER_ID in (select USER_CODE FROM config_users where USER_ORGANIZATION=:oid) and Q_COMPLETED is not null and USER_STATUS='ACTIVE'" . $stat_clause . " ORDER BY USER_LAST_NAME, USER_FIRST_NAME";
			}
		}
		//$quiz_list = $query;
		$stmt = $db->prepare($query);
		if ($orgid != "") {
			if ($GLOBALS['USER_LEVEL'] == 'TEACHER') {
				$stmt->bindValue(':guc', $GLOBALS['USER_CODE'], PDO::PARAM_STR);
			} else {
				$stmt->bindValue(':oid', $orgid, PDO::PARAM_STR);
			}
		}
		$stmt->execute();
		$isfirstrow = true;
		while ($row = $stmt->fetch()) {
			if ($comp == 'PENDING' && ($GLOBALS['USER_LEVEL'] == 'SCORER' || $GLOBALS['USER_LEVEL'] == 'ADMIN')) {
				$score_class = " class='gray'";
				$complete_link = '<a href="" onClick="complete_quiz(' . $row['Q_ID'] . ')"><img src="../images/icn_complete.png"></a>';
			} else {
				$score_class = "";
				$complete_link = "";
			}
			$edit_link = '<a href="score_quiz.php?sid=' . $row['Q_ID'] . '&id=' . $GLOBALS["SESSION_ID"] . '" target="scorequiz"><img src="../images/icn_grading.png"></a>';
			if ($row['Q_VERSION'] == '') {
				$duplicate_link = '<a href="" onClick="duplicate_quiz(' . $row['Q_ID'] . ')"><img src="../images/icn_duplicate.png"></a>';
			} else $duplicate_link = 'V' . $row['Q_VERSION'];
			$quiz_list .= "<tr><td align='center'>$edit_link $duplicate_link $complete_link</td><td nowrap>" . $row['Q_GRADING_STATUS'] . "</td><td " . $score_class . ">" . $row['USER_LAST_NAME'] . "</td><td " . $score_class . ">" . $row['USER_FIRST_NAME'] . "</td><td " . $score_class . ">" . $row['CLASS_NAME'] . "</td><td " . $score_class . ">" . $row['Q_PROMPT_TITLE'] . "</td></tr>";
		}
		$quiz_list .= "</table>";

		return $quiz_list;
	}


	// load quiz counts
	public function get_quiz_counts($db, $student_id)
	{
		$query = "SELECT count(Q_ID) as cnt from quiz where Q_STUDENT_ID=:student_id and Q_COMPLETED=1";
		$stmt = $db->prepare($query);
		$stmt->bindValue(':student_id', $student_id, PDO::PARAM_STR);
		$stmt->execute();
		$row = $stmt->fetch();
		$this->QUIZZES_TAKEN = $row['cnt'];

		$query = "SELECT count(Q_ID) as cnt from quiz where Q_STUDENT_ID=:student_id and Q_COMPLETED is null";
		$stmt = $db->prepare($query);
		$stmt->bindValue(':student_id', $student_id, PDO::PARAM_STR);
		$stmt->execute();
		$row = $stmt->fetch();
		$this->QUIZZES_REMAINING = $row['cnt'];
	}
}
