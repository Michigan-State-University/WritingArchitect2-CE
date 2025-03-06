<?php

// check security
function check_security($db)
{

	$GLOBALS["SESSION_ID"] = isset($_GET['id']) ? $_GET['id'] : die();
	$sess_id2 = $GLOBALS["SESSION_ID"];
	$date = new DateTime();
	$date->sub(new DateInterval('PT90M'));
	$sql_date = date_format($date, "Y-m-d H:i:s");
	// Get session info and user info
	$query = "select id,USER_LEVEL,USER_CODE,USER_LAST_NAME,USER_FIRST_NAME,USER_ORGANIZATION,USER_AUTHORITY, SCHOOL_SN, USER_EMAIL
			from man_sessions join config_users on session_userid=USER_CODE join config_schools on USER_ORGANIZATION=SCHOOL_NAME WHERE session_public_id=:gsid and session_end_time is null and session_last_updated>:sql_date";
	$stmt = $db->prepare($query);
	$stmt->bindValue(':gsid', $GLOBALS["SESSION_ID"], PDO::PARAM_STR);
	$stmt->bindParam(':sql_date', $sql_date, PDO::PARAM_STR);

	if ($stmt->execute()) {
		$result = $stmt->fetch();
		if (!empty($result['id'])) {
			$GLOBALS['USER_LEVEL'] = $result["USER_LEVEL"];
			$GLOBALS['USER_CODE'] = $result["USER_CODE"];
			$GLOBALS['USER_LAST_NAME'] = $result["USER_LAST_NAME"];
			$GLOBALS['USER_FIRST_NAME'] = $result["USER_FIRST_NAME"];
			$GLOBALS['USER_ORGANIZATION'] = $result["USER_ORGANIZATION"];
			$GLOBALS['USER_AUTHORITY'] = $result["USER_AUTHORITY"];
			$GLOBALS['USER_SCHOOL_SN'] = $result["SCHOOL_SN"];
			$GLOBALS['USER_EMAIL'] = $result["USER_EMAIL"];
			$sess_id = $result["id"];

			$ret_val = update_session_id($db, $sess_id);
			//echo $GLOBAL["SESSION_ID"];

			return true;
		} else header("Location: logout.php?id=$sess_id2");
	}
}

function update_session_id($db, $sess_id)
{
	$date = new DateTime();
	$sql_date = date_format($date, "Y-m-d H:i:s");
	$query = "update man_sessions set session_last_updated=:sql_date where id=:sess_id";

	$stmt = $db->prepare($query);
	$stmt->bindParam(':sql_date', $sql_date, PDO::PARAM_STR);
	$stmt->bindParam(':sess_id', $sess_id, PDO::PARAM_INT);
	$stmt->execute();
	return null;
}

// FIPS-140 compliant password hashing
function fips_password($password)
{
	$iterations = 210000;
	$salt = bin2hex(random_bytes(16));
	$hash = hash_pbkdf2("sha512", $password, $salt, $iterations);
	return [$hash, $salt];
}
