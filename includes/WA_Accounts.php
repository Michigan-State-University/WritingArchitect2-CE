<?php
include_once '../includes/WA_Security.php';

class Account
{
	private $conn;

	// Properties
	public $USER_ID;
	public $USER_CODE;
	public $USER_LEVEL;
	public $USER_STATUS;
	public $USER_ORGANIZATION;
	public $USER_LAST_NAME;
	public $USER_FIRST_NAME;
	public $USER_EMAIL;
	public $USER_AUTHORITY;
	public $USER_PASSWORD;
	public $USER_SALT;
	public $USER_TIMEOUT;
	public $USER_CLASSID;  // Pulled from a different table
	// Constructor with DB
	public function __contruct($db)
	{
		$this->conn = $db;
	}

	// log into account
	public function login_account($db)
	{
		// Get account information
		$query = "SELECT USER_ID, USER_PASSWORD, USER_SALT from config_users where USER_CODE=:uc";
		$stmt = $db->prepare($query);
		$stmt->bindValue(':uc', $this->USER_CODE, PDO::PARAM_STR);
		$stmt->execute();

		$db_user = $stmt->fetch(PDO::FETCH_ASSOC);

		// Check if user has a SALT
		if ($db_user['USER_SALT'] != "") {
			// User is using PBKDF2 password
			$this->USER_SALT = $db_user['USER_SALT'];

			// Verify password
			$hashed_password = hash_pbkdf2("sha512", $this->USER_PASSWORD, $this->USER_SALT, 210000);

			if ($db_user['USER_PASSWORD'] != $hashed_password) {
				return null;
			}

			return create_session($db, $this->USER_CODE);
		} else {
			// User is using old MD5 password
			$md5_password = md5($this->USER_PASSWORD);

			// Verify password
			if ($db_user['USER_PASSWORD'] != $md5_password) {
				return null;
			}

			// Update password to PBKDF2
			[$pbkdf2_hash, $salt] = fips_password($this->USER_PASSWORD);
			$query = "UPDATE config_users SET USER_PASSWORD=:pbkdf2_hash, USER_SALT=:salt WHERE USER_CODE=:uc";
			$stmt = $db->prepare($query);
			$stmt->bindValue(':pbkdf2_hash', $pbkdf2_hash, PDO::PARAM_STR);
			$stmt->bindValue(':salt', $salt, PDO::PARAM_STR);
			$stmt->bindValue(':uc', $this->USER_CODE, PDO::PARAM_STR);
			$stmt->execute();

			return create_session($db, $this->USER_CODE);
		}
	}

	// save single account
	public function save_account($db, $acct_id, $userlevel)
	{
		$SQL_USER_CODE = addslashes($this->USER_CODE);
		$SQL_USER_LAST_NAME = addslashes($this->USER_LAST_NAME);
		$SQL_USER_FIRST_NAME = addslashes($this->USER_FIRST_NAME);
		$SQL_USER_EMAIL = addslashes($this->USER_EMAIL);
		$this->USER_AUTHORITY = "";
		if ($this->USER_ORGANIZATION == "MSU Beta Testing" or $this->USER_ORGANIZATION == "GreenFlux") {
			$this->USER_AUTHORITY = "SA";
			$GLOBALS['USER_AUTHORITY'] = "SA";
		}

		$dataval = "USER_CODE=:suc, 
			USER_LEVEL=:userlevel, 
			USER_STATUS=:tuser_status, 
			USER_ORGANIZATION=:tuser_org, 
			USER_LAST_NAME=:sulast_name, 
			USER_FIRST_NAME=:sufirst_name, 
			USER_EMAIL=:suemail, 
			USER_AUTHORITY=:tuser_authority, 
			USER_MODIFIED_BY=:guc,
			USER_MODIFIED_ON=UTC_TIMESTAMP()";
		if ($this->USER_PASSWORD != "") {
			$dataval .= ", USER_PASSWORD=:pbkdf2_pass, USER_SALT=:pbkdf2_salt, USER_TIMEOUT=90";
		}

		$query = "select USER_ID from config_users where USER_ID<>:acct_id and USER_CODE=:suc";
		$stmt = $db->prepare($query);
		$stmt->bindValue(':acct_id', $acct_id, PDO::PARAM_INT);
		$stmt->bindValue(':suc', $SQL_USER_CODE, PDO::PARAM_STR);
		$stmt->execute();
		if ($stmt->rowCount() > 0) {
			return "NOT UNIQUE";
		} else {
			$ret_value = "";
			if ($acct_id == "0") {
				$createdtext = ", USER_CREATED_BY=:guc, USER_CREATED_AT=UTC_TIMESTAMP()";
				$query = "INSERT into config_users set " . $dataval . $createdtext;
				$stmt = $db->prepare($query);
				$stmt->bindValue(':suc', $SQL_USER_CODE, PDO::PARAM_STR);
				$stmt->bindValue(':userlevel', $userlevel, PDO::PARAM_STR);
				$stmt->bindValue(':tuser_status', $this->USER_STATUS, PDO::PARAM_STR);
				$stmt->bindValue(':tuser_org', $this->USER_ORGANIZATION, PDO::PARAM_STR);
				$stmt->bindValue(':sulast_name', $SQL_USER_LAST_NAME, PDO::PARAM_STR);
				$stmt->bindValue(':sufirst_name', $SQL_USER_FIRST_NAME, PDO::PARAM_STR);
				$stmt->bindValue(':suemail', $SQL_USER_EMAIL, PDO::PARAM_STR);
				$stmt->bindValue(':tuser_authority', $this->USER_AUTHORITY, PDO::PARAM_STR);
				$stmt->bindValue(':guc', $GLOBALS['USER_CODE'], PDO::PARAM_STR);
				if ($this->USER_PASSWORD != "") {
					[$pbkdf2_hash, $salt] = fips_password($this->USER_PASSWORD);
					$stmt->bindValue(':pbkdf2_pass', $pbkdf2_hash, PDO::PARAM_STR);
					$stmt->bindValue(':pbkdf2_salt', $salt, PDO::PARAM_STR);
				}
				if ($stmt->execute()) {
					$query = "SELECT USER_ID from config_users where USER_CODE=:tuc";
					$stmt = $db->prepare($query);
					$stmt->bindValue(':tuc', $this->USER_CODE, PDO::PARAM_STR);
					$stmt->execute();
					if ($stmt->rowCount() > 0) {
						$row = $stmt->fetch();
						$this->USER_ID = $row['USER_ID'];
						$acct_id = $this->USER_ID;
						$ret_value = $row['USER_ID'];
					}
				}
			} else {
				$query = "UPDATE config_users SET " . $dataval . " WHERE USER_ID=:acct_id";

				$stmt = $db->prepare($query);
				$stmt->bindValue(':suc', $SQL_USER_CODE, PDO::PARAM_STR);
				$stmt->bindValue(':userlevel', $userlevel, PDO::PARAM_STR);
				$stmt->bindValue(':tuser_status', $this->USER_STATUS, PDO::PARAM_STR);
				$stmt->bindValue(':tuser_org', $this->USER_ORGANIZATION, PDO::PARAM_STR);
				$stmt->bindValue(':sulast_name', $SQL_USER_LAST_NAME, PDO::PARAM_STR);
				$stmt->bindValue(':sufirst_name', $SQL_USER_FIRST_NAME, PDO::PARAM_STR);
				$stmt->bindValue(':suemail', $SQL_USER_EMAIL, PDO::PARAM_STR);
				$stmt->bindValue(':tuser_authority', $this->USER_AUTHORITY, PDO::PARAM_STR);
				$stmt->bindValue(':guc', $GLOBALS['USER_CODE'], PDO::PARAM_STR);
				if ($this->USER_PASSWORD != "") {
					[$pbkdf2_hash, $salt] = fips_password($this->USER_PASSWORD);
					$stmt->bindValue(':pbkdf2_pass', $pbkdf2_hash, PDO::PARAM_STR);
					$stmt->bindValue(':pbkdf2_salt', $salt, PDO::PARAM_STR);
				}
				$stmt->bindValue(':acct_id', $acct_id, PDO::PARAM_INT);
				$stmt->execute();
			}
			//save class if student
			if ($userlevel == "STUDENT" && ($GLOBALS['USER_LEVEL'] == 'ADMIN' || $GLOBALS['USER_LEVEL'] == 'SCORER')) {
				$query = "SELECT PUPIL_ID from config_pupils where PUPIL_STUDENTID=:acct_id";
				$stmt = $db->prepare($query);
				$stmt->bindValue(':acct_id', $acct_id, PDO::PARAM_INT);
				$stmt->execute();
				if ($stmt->rowCount() > 0) {
					$row = $stmt->fetch();
					$pupilid = $row['PUPIL_ID'];
				} else {
					$pupilid = "0";
				}
				$dataval = "PUPIL_CLASSID=:tuser_cid,
					PUPIL_STUDENTID=:acct_id, 
					PUPIL_MODIFIED_BY=:guc,
					PUPIL_MODIFIED_ON=UTC_TIMESTAMP()";

				if ($pupilid == "0") {
					$createdtext = ", PUPIL_CREATED_BY=:guc, PUPIL_CREATED_AT=UTC_TIMESTAMP()";
					$query = "INSERT into config_pupils set " . $dataval . $createdtext;
					$stmt = $db->prepare($query);
					$stmt->bindValue(':tuser_cid', $this->USER_CLASSID, PDO::PARAM_INT);
					$stmt->bindValue(':acct_id', $acct_id, PDO::PARAM_INT);
					$stmt->bindValue(':guc', $GLOBALS['USER_CODE'], PDO::PARAM_STR);
					$stmt->execute();
				} else {
					$query = "UPDATE config_pupils SET " . $dataval . " WHERE PUPIL_ID=:pupilid";
					$stmt = $db->prepare($query);
					$stmt->bindValue(':tuser_cid', $this->USER_CLASSID, PDO::PARAM_INT);
					$stmt->bindValue(':acct_id', $acct_id, PDO::PARAM_INT);
					$stmt->bindValue(':guc', $GLOBALS['USER_CODE'], PDO::PARAM_STR);
					$stmt->bindValue(':pupilid', $pupilid, PDO::PARAM_INT);
					$stmt->execute();
				}
			}
			return $ret_value;
		}
	}

	// load single account
	public function load_account($db, $acct_id)
	{
		$query = "SELECT * from config_users where USER_ID=:acct_id";
		$stmt = $db->prepare($query);
		$stmt->bindValue(':acct_id', $acct_id, PDO::PARAM_INT);
		$stmt->execute();
		if ($stmt->rowCount() > 0) {
			$row = $stmt->fetch();
			$this->USER_CODE = $row['USER_CODE'];
			$this->USER_LEVEL = $row['USER_LEVEL'];
			$this->USER_STATUS = $row['USER_STATUS'];
			$this->USER_ORGANIZATION = $row['USER_ORGANIZATION'];
			$this->USER_LAST_NAME = $row['USER_LAST_NAME'];
			$this->USER_FIRST_NAME = $row['USER_FIRST_NAME'];
			$this->USER_EMAIL = $row['USER_EMAIL'];
			$this->USER_AUTHORITY = $row['USER_AUTHORITY'];
			if ($this->USER_LEVEL == "STUDENT" && $GLOBALS['USER_LEVEL'] == 'TEACHER') {
				$query = "SELECT PUPIL_CLASSID from config_pupils where PUPIL_STUDENTID=:acct_id";
				$stmt = $db->prepare($query);
				$stmt->bindValue(':acct_id', $acct_id, PDO::PARAM_INT);
				$stmt->execute();
				if ($stmt->rowCount() > 0) {
					$row = $stmt->fetch();
					$this->USER_CLASSID = $row['PUPIL_CLASSID'];
				} else {
					$this->USER_CLASSID = "0";
				}
			}
			return true;
		} else {
			return false;
		}
	}

	public function loadAccountByUserCode($db)
	{
		$query = "SELECT * from config_users where USER_CODE=:uc";
		$stmt = $db->prepare($query);
		$stmt->bindValue(':uc', $this->USER_CODE, PDO::PARAM_STR);
		$stmt->execute();
		if ($stmt->rowCount() > 0) {
			$row = $stmt->fetch();
			$this->USER_ID = $row['USER_ID'];
			$this->USER_CODE = $row['USER_CODE'];
			$this->USER_LEVEL = $row['USER_LEVEL'];
			$this->USER_STATUS = $row['USER_STATUS'];
			$this->USER_ORGANIZATION = $row['USER_ORGANIZATION'];
			$this->USER_LAST_NAME = $row['USER_LAST_NAME'];
			$this->USER_FIRST_NAME = $row['USER_FIRST_NAME'];
			$this->USER_EMAIL = $row['USER_EMAIL'];
			$this->USER_AUTHORITY = $row['USER_AUTHORITY'];
			if ($this->USER_LEVEL == "STUDENT" && $GLOBALS['USER_LEVEL'] == 'TEACHER') {
				$query = "SELECT PUPIL_CLASSID from config_pupils where PUPIL_STUDENTID=:acct_id";
				$stmt = $db->prepare($query);
				$stmt->bindValue(':acct_id', $this->USER_ID, PDO::PARAM_INT);
				$stmt->execute();
				if ($stmt->rowCount() > 0) {
					$row = $stmt->fetch();
					$this->USER_CLASSID = $row['PUPIL_CLASSID'];
				} else {
					$this->USER_CLASSID = "0";
				}
			}
			return true;
		} else {
			return false;
		}
	}
}

// CREATE Session
function create_session($db, $uid)
{
	$date = new DateTime();
	$thetime = $date->getTimestamp();
	$sql_date = date_format($date, "Y-m-d H:i:s");
	$C_userid = $uid;
	$C_public_id = md5($C_userid . $thetime);
	$C_start_time = $sql_date;
	$C_last_update = $sql_date;
	$query = "INSERT into man_sessions set
			session_public_id='$C_public_id',
			session_start_time='$C_start_time',
			session_last_updated='$C_last_update',
			session_userid='$C_userid'";
	$stmt = $db->prepare($query);

	if ($stmt->execute()) {
		return $C_public_id;
	}
	return null;
}
// CREATE Session
function close_session($db, $uid)
{
	$date = new DateTime();
	$thetime = $date->getTimestamp();
	$sql_date = date_format($date, "Y-m-d H:i:s");
	$C_userid = $uid;
	$C_last_update = $sql_date;

	$query = "INSERT into man_sessions set
			session_public_id='$C_public_id',
			session_start_time='$C_start_time',
			session_last_updated='$C_last_update',
			session_userid='$C_userid'";
	$stmt = $db->prepare($query);

	if ($stmt->execute()) {
		return $C_public_id;
	}
	return null;
}

// list admin users
function list_admins($db)
{
	$admin_list = "<table><tr><td class='table_title'>Actions</td><td class='table_title'>User ID</td><td class='table_title'>Last Name</td><td class='table_title'>First Name</td><td  class='table_title'>Organization</td><td class='table_title'>Status</td></tr>";
	$school = $GLOBALS['USER_ORGANIZATION'];
	if ($GLOBALS['USER_AUTHORITY'] == "SA") {
		$query = "SELECT * from config_users where USER_LEVEL='ADMIN' order by USER_LAST_NAME, USER_FIRST_NAME ";
	} else {
		$query = "SELECT * from config_users where USER_LEVEL='ADMIN' and USER_ORGANIZATION=:school order by USER_LAST_NAME, USER_FIRST_NAME ";
	}
	$stmt = $db->prepare($query);
	if ($GLOBALS['USER_AUTHORITY'] != "SA") {
		$stmt->bindValue(':school', $school, PDO::PARAM_STR);
	}
	$stmt->execute();
	while ($row = $stmt->fetch()) {
		$USER_ID = $row['USER_ID'];
		$delete_link = '<a href="" onclick="delete_record(\'' . $row['USER_CODE'] . '\');return false;"><img src="../images/icn_delete.png" height="16" width="16"></a>';
		$edit_link = '<a href="edit_user.php?USER_LEVEL=ADMIN&uid=' . $USER_ID . '&id=' . $GLOBALS["SESSION_ID"] . '"><img src="../images/icn_edit.png"></a>';
		$admin_list .= "<tr><td align='center'>$edit_link $delete_link</td><td class='table_row'>" . $row['USER_CODE'] . "</td><td class='table_row'>" . $row['USER_LAST_NAME'] . "</td><td class='table_row'>" . $row['USER_FIRST_NAME'] . "</td><td class='table_row'>" . $row['USER_ORGANIZATION'] . "</td><td class='table_row'>" . $row['USER_STATUS'] . "</td></tr>";
	}
	$admin_list .= "</table>";
	return $admin_list;
}

// list teachers
function list_teachers($db)
{
	$teacher_list = "<table><tr><td class='table_title'>Actions</td><td class='table_title'>User ID</td><td class='table_title'>Last Name</td><td class='table_title'>First Name</td><td  class='table_title'>School</td><td class='table_title'>Status</td></tr>";
	$school = $GLOBALS['USER_ORGANIZATION'];
	if ($GLOBALS['USER_AUTHORITY'] == "SA") {
		$query = "SELECT * from config_users where USER_LEVEL='TEACHER' order by USER_ORGANIZATION, USER_LAST_NAME, USER_FIRST_NAME ";
	} else {
		if ($GLOBALS['USER_LEVEL'] == "TEACHER") {
			$uc = $GLOBALS['USER_CODE'];
			$query = "SELECT * from config_users where USER_CODE=:uc";
		} else {
			$query = "SELECT * from config_users where USER_LEVEL='TEACHER' and USER_ORGANIZATION=:school order by USER_ORGANIZATION, USER_LAST_NAME, USER_FIRST_NAME ";
		}
	}
	$stmt = $db->prepare($query);
	if ($GLOBALS['USER_AUTHORITY'] != "SA") {
		if ($GLOBALS['USER_LEVEL'] == "TEACHER") {
			$stmt->bindValue(':uc', $uc, PDO::PARAM_STR);
		} else {
			$stmt->bindValue(':school', $school, PDO::PARAM_STR);
		}
	}
	$stmt->execute();
	while ($row = $stmt->fetch()) {
		$USER_ID = $row['USER_ID'];
		if ($GLOBALS['USER_LEVEL'] == "TEACHER") $delete_link = "";
		else $delete_link = '<a href="" onclick="delete_record(\'' . $row['USER_CODE'] . '\');return false;"><img src="../images/icn_delete.png" height="16" width="16"></a>';
		$edit_link = '<a href="edit_user.php?USER_LEVEL=TEACHER&uid=' . $USER_ID . '&id=' . $GLOBALS["SESSION_ID"] . '"><img src="../images/icn_edit.png"></a>';
		$teacher_list .= "<tr><td align='center'>$edit_link $delete_link</td><td class='table_row'>" . $row['USER_CODE'] . "</td><td class='table_row'>" . $row['USER_LAST_NAME'] . "</td><td class='table_row'>" . $row['USER_FIRST_NAME'] . "</td><td class='table_row'>" . $row['USER_ORGANIZATION'] . "</td><td class='table_row'>" . $row['USER_STATUS'] . "</td></tr>";
	}
	$teacher_list .= "</table>";
	return $teacher_list;
}

// list scorers
function list_scorers($db)
{
	$scorer_list = "<table><tr><td class='table_title'>Actions</td><td class='table_title'>User ID</td><td class='table_title'>Last Name</td><td class='table_title'>First Name</td><td  class='table_title'>School</td><td class='table_title'>Status</td></tr>";
	$school = $GLOBALS['USER_ORGANIZATION'];
	if ($GLOBALS['USER_AUTHORITY'] == "SA") {
		$query = "SELECT * from config_users where USER_LEVEL='SCORER' order by USER_ORGANIZATION, USER_LAST_NAME, USER_FIRST_NAME ";
	} else {
		$query = "SELECT * from config_users where USER_LEVEL='SCORER' and USER_ORGANIZATION=:school order by USER_ORGANIZATION, USER_LAST_NAME, USER_FIRST_NAME ";
	}
	$stmt = $db->prepare($query);
	if ($GLOBALS['USER_AUTHORITY'] != "SA") {
		$stmt->bindValue(':school', $school, PDO::PARAM_STR);
	}
	$stmt->execute();
	while ($row = $stmt->fetch()) {
		$USER_ID = $row['USER_ID'];
		$delete_link = '<a href="" onclick="delete_record(\'' . $row['USER_CODE'] . '\');return false;"><img src="../images/icn_delete.png" height="16" width="16"></a>';
		$edit_link = '<a href="edit_user.php?USER_LEVEL=SCORER&uid=' . $USER_ID . '&id=' . $GLOBALS["SESSION_ID"] . '"><img src="../images/icn_edit.png"></a>';
		$scorer_list .= "<tr><td align='center'>$edit_link $delete_link</td><td class='table_row'>" . $row['USER_CODE'] . "</td><td class='table_row'>" . $row['USER_LAST_NAME'] . "</td><td class='table_row'>" . $row['USER_FIRST_NAME'] . "</td><td class='table_row'>" . $row['USER_ORGANIZATION'] . "</td><td class='table_row'>" . $row['USER_STATUS'] . "</td></tr>";
	}
	$scorer_list .= "</table>";
	return $scorer_list;
}

// list students
function list_students($db, $org)
{
	$student_list = "<table><tr><td class='table_title'>Actions</td><td class='table_title'>User ID</td><td class='table_title'>Last Name</td><td class='table_title'>First Name</td><td  class='table_title'>School</td><td class='table_title'>Current Class</td><td class='table_title'>Status</td></tr>";
	if ($org != "") {
		if ($GLOBALS['USER_LEVEL'] == "TEACHER") {
			$teacher_id = $GLOBALS['USER_CODE'];
			$query = "SELECT * from config_users where USER_LEVEL='STUDENT' and USER_ORGANIZATION=:org and USER_CODE in (select USER_CODE from v_students_in_class where CLASS_TEACHER_ID=:teacher_id) order by USER_ORGANIZATION, USER_LAST_NAME, USER_FIRST_NAME ";
		} else {
			$query = "SELECT * from config_users where USER_LEVEL='STUDENT' and USER_ORGANIZATION=:org order by USER_ORGANIZATION, USER_LAST_NAME, USER_FIRST_NAME ";
		}
	} else $query = "SELECT * from config_users where USER_LEVEL='STUDENT' order by USER_ORGANIZATION, USER_LAST_NAME, USER_FIRST_NAME ";
	$stmt = $db->prepare($query);
	if ($org != "") {
		$stmt->bindValue(':org', $org, PDO::PARAM_STR);
		if ($GLOBALS['USER_LEVEL'] == "TEACHER") {
			$stmt->bindValue(':teacher_id', $teacher_id, PDO::PARAM_STR);
		}
	}
	$stmt->execute();
	while ($row = $stmt->fetch()) {
		$USER_ID = $row['USER_ID'];
		$delete_link = '<a href="" onclick="delete_record(\'' . $row['USER_CODE'] . '\');return false;"><img src="../images/icn_delete.png" height="16" width="16"></a>';
		$edit_link = '<a href="edit_user.php?USER_LEVEL=STUDENT&uid=' . $USER_ID . '&id=' . $GLOBALS["SESSION_ID"] . '"><img src="../images/icn_edit.png"></a>';
		$student_list .= "<tr><td align='center'>$edit_link $delete_link</td><td class='table_row'>" . $row['USER_CODE'] . "</td><td class='table_row'>" . $row['USER_LAST_NAME'] . "</td><td class='table_row'>" . $row['USER_FIRST_NAME'] . "</td><td class='table_row'>" . $row['USER_ORGANIZATION'] . "</td><td class='table_row'>" . get_current_class($db, $row['USER_ID']) . "</td><td class='table_row'>" . $row['USER_STATUS'] . "</td></tr>";
	}
	$student_list .= "</table>";
	return $student_list;
}

// get Class for student
function get_current_class($db, $student_code)
{
	$query = "SELECT CLASS_NAME from config_classes join config_pupils on PUPIL_CLASSID=CLASS_ID where PUPIL_STUDENTID=:student_code";
	//echo $query;
	$stmt = $db->prepare($query);
	$stmt->bindValue(':student_code', $student_code, PDO::PARAM_INT);
	$stmt->execute();
	if ($stmt->rowCount() > 0) {
		$row = $stmt->fetch();
		return $row['CLASS_NAME'];
	}
	return "";
}

// get person name
function get_account_name($db, $account_code)
{
	$query = "SELECT USER_FIRST_NAME, USER_LAST_NAME from config_users where USER_CODE=:account_code";
	//echo $query;
	$stmt = $db->prepare($query);
	$stmt->bindValue(':account_code', $account_code, PDO::PARAM_STR);
	$stmt->execute();
	if ($stmt->rowCount() > 0) {
		$row = $stmt->fetch();
		return $row['USER_FIRST_NAME'] . " " . $row['USER_LAST_NAME'];
	}
	return "";
}
