<?php
class Prompts
{
	private $conn;

	// Properties
	public $PROMPT_ID;
	public $PROMPT_SHORT_TITLE;
	public $PROMPT_TITLE;
	public $PROMPT_BODY;
	public $PROMPT_AUDIO_PROMPT;
	public $PROMPT_AUDIO_PASSAGE;
	public $PROMPT_SOURCE;
	public $PROMPT_INSTRUCTIONS;
	public $PROMPT_AUDIO_LEN;
	public $PROMPT_STATUS;
	// Constructor with DB
	public function __contruct($db)
	{
		$this->conn = $db;
	}

	// save single prompt
	public function save_prompt($db, $prompt_id)
	{
		$SQL_PROMPT_SHORT_TITLE = addslashes($this->PROMPT_SHORT_TITLE);
		$SQL_PROMPT_TITLE = addslashes($this->PROMPT_TITLE);
		$SQL_PROMPT_BODY = addslashes($this->PROMPT_BODY);
		$SQL_PROMPT_INSTRUCTIONS = addslashes($this->PROMPT_INSTRUCTIONS);

		$dataval = "PROMPT_SHORT_TITLE=:spst, 
			PROMPT_TITLE=:spt, 
			PROMPT_BODY=:spb, 
			PROMPT_AUDIO_PROMPT=:tpapr, 
			PROMPT_AUDIO_PASSAGE=:tpapa, 
			PROMPT_SOURCE=:tpsrc,
			PROMPT_INSTRUCTIONS=:tpins,
			PROMPT_STATUS=:tpst,
			PROMPT_AUDIO_LEN=:tpal,
			PROMPT_MODIFIED_BY=:tmb,
			PROMPT_MODIFIED_ON=UTC_TIMESTAMP()";

		$query = "select PROMPT_ID from quiz_prompts where PROMPT_ID<>:prompt_id and PROMPT_SHORT_TITLE=:spst";
		$stmt = $db->prepare($query);
		$stmt->bindValue(':prompt_id', $prompt_id, PDO::PARAM_INT);
		$stmt->bindValue(':spst', $SQL_PROMPT_SHORT_TITLE, PDO::PARAM_STR);
		$stmt->execute();
		if ($stmt->rowCount() > 0) {
			return "NOT UNIQUE";
		} else {
			if ($prompt_id == "0") {
				$createdtext = ", PROMPT_CREATED_BY=:guc, PROMPT_CREATED_AT=UTC_TIMESTAMP()";
				$query = "INSERT into quiz_prompts set " . $dataval . $createdtext;
				$stmt = $db->prepare($query);
				$stmt->bindValue(':spst', $SQL_PROMPT_SHORT_TITLE, PDO::PARAM_STR);
				$stmt->bindValue(':spt', $SQL_PROMPT_TITLE, PDO::PARAM_STR);
				$stmt->bindValue(':spb', $SQL_PROMPT_BODY, PDO::PARAM_STR);
				$stmt->bindValue(':tpapr', $this->PROMPT_AUDIO_PROMPT, PDO::PARAM_STR);
				$stmt->bindValue(':tpapa', $this->PROMPT_AUDIO_PASSAGE, PDO::PARAM_STR);
				$stmt->bindValue(':tpsrc', $this->PROMPT_SOURCE, PDO::PARAM_STR);
				$stmt->bindValue(':tpins', $SQL_PROMPT_INSTRUCTIONS, PDO::PARAM_STR);
				$stmt->bindValue(':tpst', $this->PROMPT_STATUS, PDO::PARAM_STR);
				$stmt->bindValue(':tpal', $this->PROMPT_AUDIO_LEN, PDO::PARAM_INT);
				$stmt->bindValue(':tmb', $GLOBALS['USER_CODE'], PDO::PARAM_STR);
				$stmt->bindValue(':guc', $GLOBALS['USER_CODE'], PDO::PARAM_STR);
				if ($stmt->execute()) {
					$query = "SELECT PROMPT_ID from quiz_prompts where PROMPT_SHORT_TITLE=:spst";
					$stmt = $db->prepare($query);
					$stmt->bindValue(':spst', $SQL_PROMPT_SHORT_TITLE, PDO::PARAM_STR);
					$stmt->execute();
					if ($stmt->rowCount() > 0) {
						$row = $stmt->fetch();
						$this->PROMPT_ID = $row['PROMPT_ID'];
						return $row['PROMPT_ID'];
					} else {
						return "";
					}
				}
				return "";
			} else {
				$query = "UPDATE quiz_prompts SET " . $dataval . " WHERE PROMPT_ID=:prompt_id";
				$stmt = $db->prepare($query);
				$stmt->bindValue(':spst', $SQL_PROMPT_SHORT_TITLE, PDO::PARAM_STR);
				$stmt->bindValue(':spt', $SQL_PROMPT_TITLE, PDO::PARAM_STR);
				$stmt->bindValue(':spb', $SQL_PROMPT_BODY, PDO::PARAM_STR);
				$stmt->bindValue(':tpapr', $this->PROMPT_AUDIO_PROMPT, PDO::PARAM_STR);
				$stmt->bindValue(':tpapa', $this->PROMPT_AUDIO_PASSAGE, PDO::PARAM_STR);
				$stmt->bindValue(':tpsrc', $this->PROMPT_SOURCE, PDO::PARAM_STR);
				$stmt->bindValue(':tpins', $SQL_PROMPT_INSTRUCTIONS, PDO::PARAM_STR);
				$stmt->bindValue(':tpst', $this->PROMPT_STATUS, PDO::PARAM_STR);
				$stmt->bindValue(':tpal', $this->PROMPT_AUDIO_LEN, PDO::PARAM_INT);
				$stmt->bindValue(':tmb', $GLOBALS['USER_CODE'], PDO::PARAM_STR);
				$stmt->bindValue(':prompt_id', $prompt_id, PDO::PARAM_INT);
				$stmt->execute();
				return "";
			}
		}
	}

	// load single prompt
	public function load_prompt($db, $prompt_id)
	{
		$query = "SELECT * from quiz_prompts where PROMPT_ID=:prompt_id";
		$stmt = $db->prepare($query);
		$stmt->bindValue(':prompt_id', $prompt_id, PDO::PARAM_INT);
		$stmt->execute();
		if ($stmt->rowCount() > 0) {
			$row = $stmt->fetch();
			$this->PROMPT_SHORT_TITLE = $row['PROMPT_SHORT_TITLE'];
			$this->PROMPT_TITLE = $row['PROMPT_TITLE'];
			$this->PROMPT_BODY = $row['PROMPT_BODY'];
			$this->PROMPT_AUDIO_PROMPT = $row['PROMPT_AUDIO_PROMPT'];
			$this->PROMPT_AUDIO_PASSAGE = $row['PROMPT_AUDIO_PASSAGE'];
			$this->PROMPT_SOURCE = $row['PROMPT_SOURCE'];
			$this->PROMPT_INSTRUCTIONS = $row['PROMPT_INSTRUCTIONS'];
			$this->PROMPT_STATUS = $row['PROMPT_STATUS'];
			$this->PROMPT_AUDIO_LEN = $row['PROMPT_AUDIO_LEN'];
			return true;
		} else {
			return false;
		}
	}
}

// list prompts
function list_prompts($db)
{
	$quiz_list = "<table><tr><td class='table_title'>Edit</td><td class='table_title'>Abbr. Name</td><td class='table_title'>Name</td><td class='table_title'>Status</td></tr>";
	$query = "SELECT * from quiz_prompts order by PROMPT_TITLE";
	$stmt = $db->prepare($query);
	$stmt->execute();
	while ($row = $stmt->fetch()) {
		$PROMPT_ID = $row['PROMPT_ID'];
		if ($GLOBALS['USER_LEVEL'] == 'ADMIN') {
			$delete_link = '<a href="" onclick="delete_record(\'' . $row['PROMPT_SHORT_TITLE'] . '\');return false;"><img src="../images/icn_delete.png" height="16" width="16"></a>';
		} else {
			$delete_link = "";
		}
		$edit_link = '<a href="edit_prompt.php?pid=' . $PROMPT_ID . '&id=' . $GLOBALS["SESSION_ID"] . '"><img src="../images/icn_edit.png"></a>';
		$quiz_list .= "<tr><td align='center'>$edit_link $delete_link</td><td class='table_row'>" . $row['PROMPT_SHORT_TITLE'] . "</td><td class='table_row'>" . $row['PROMPT_TITLE'] . "</td><td class='table_row'>" . $row['PROMPT_STATUS'] . "</td></tr>";
	}
	$quiz_list .= "</table>";
	return $quiz_list;
}
