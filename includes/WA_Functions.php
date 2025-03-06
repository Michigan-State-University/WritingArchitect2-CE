<?php
function create_drop_menu($db, $MENU_NAME, $ITEM_VAL, $ITEM_NAME)
{
	$cdm = '<select id="' . $ITEM_NAME . '" name="' . $ITEM_NAME . '" size="1" required class="form-control"><option value=""' . check_selected("", $ITEM_VAL) . '>Select</option>';

	$query = "SELECT CM_MENU_ITEM,CM_MENU_VALUE from config_menu where CM_MENU_NAME=:menuname order by CM_SORT ";
	$stmt = $db->prepare($query);
	$stmt->bindValue(':menuname', $MENU_NAME, PDO::PARAM_STR);
	$stmt->execute();
	while ($row = $stmt->fetch()) {
		$CM_MENU_ITEM = $row['CM_MENU_ITEM'];
		$CM_MENU_VALUE = $row['CM_MENU_VALUE'];

		$cdm .= '<option value="' . $CM_MENU_VALUE . '"' . check_selected($CM_MENU_VALUE, $ITEM_VAL) . '>' . $CM_MENU_ITEM . '</option>';
	}
	$cdm .= "</select>";
	echo $cdm;
}

function create_drop_menu_small($db, $MENU_NAME, $ITEM_VAL, $ITEM_NAME)
{
	$cdm = '<select id="' . $ITEM_NAME . '" name="' . $ITEM_NAME . '" size="1" ><option value=""' . check_selected("", $ITEM_VAL) . '></option>';

	$query = "SELECT CM_MENU_ITEM,CM_MENU_VALUE from config_menu where CM_MENU_NAME=:menuname order by CM_SORT ";
	$stmt = $db->prepare($query);
	$stmt->bindValue(':menuname', $MENU_NAME, PDO::PARAM_STR);
	$stmt->execute();
	while ($row = $stmt->fetch()) {
		$CM_MENU_ITEM = $row['CM_MENU_ITEM'];
		$CM_MENU_VALUE = $row['CM_MENU_VALUE'];

		$cdm .= '<option value="' . $CM_MENU_VALUE . '"' . check_selected($CM_MENU_VALUE, $ITEM_VAL) . '>' . $CM_MENU_ITEM . '</option>';
	}
	$cdm .= "</select>";
	echo $cdm;
}

function create_school_menu($db, $ITEM_VAL, $ITEM_NAME)
{
	$csm = '<select id="' . $ITEM_NAME . '" name="' . $ITEM_NAME . '" size="1" required class="form-control"><option value=""' . check_selected("", $ITEM_VAL) . '>Select</option>';

	$query = "SELECT SCHOOL_NAME from config_schools where SCHOOL_STATUS='ACTIVE' order by SCHOOL_NAME ";
	$stmt = $db->prepare($query);
	$stmt->execute();
	while ($row = $stmt->fetch()) {
		$SCHOOL_NAME = $row['SCHOOL_NAME'];

		$csm .= '<option value="' . $SCHOOL_NAME . '"' . check_selected($SCHOOL_NAME, $ITEM_VAL) . '>' . $SCHOOL_NAME . '</option>';
	}
	$csm .= "</select>";
	echo $csm;
}

function create_class_menu($db, $ITEM_VAL, $ITEM_NAME)
{
	$csm = '<select id="' . $ITEM_NAME . '" name="' . $ITEM_NAME . '" size="1" required class="form-control"><option value=""' . check_selected("", $ITEM_VAL) . '>Select</option>';

	$query = "SELECT CLASS_ID, CLASS_NAME, USER_LAST_NAME from config_classes join config_users on CLASS_TEACHER_ID=USER_CODE where CLASS_SCHOOL_ID=:guss order by CLASS_NAME";
	$stmt = $db->prepare($query);
	$stmt->bindValue(':guss', $GLOBALS['USER_SCHOOL_SN'], PDO::PARAM_STR);
	$stmt->execute();
	while ($row = $stmt->fetch()) {
		$CLASS_NAME = $row['CLASS_NAME'];
		$CLASS_ID = $row['CLASS_ID'];
		$TEACHER_LAST_NAME = $row['USER_LAST_NAME'];
		$csm .= '<option value="' . $CLASS_ID . '"' . check_selected($CLASS_ID, $ITEM_VAL) . '>' . $TEACHER_LAST_NAME . ' ' . $CLASS_NAME . '</option>';
	}
	$csm .= "</select>";
	echo $csm;
}

function create_prompt_menu($db, $ITEM_VAL, $ITEM_NAME)
{
	$cpm = '<select id="' . $ITEM_NAME . '" name="' . $ITEM_NAME . '" size="1" required class="form-control"><option value=""' . check_selected("", $ITEM_VAL) . '>Select</option>';

	$query = "SELECT PROMPT_SHORT_TITLE from quiz_prompts where PROMPT_STATUS='ACTIVE' order by PROMPT_SHORT_TITLE ";
	$stmt = $db->prepare($query);
	$stmt->execute();
	while ($row = $stmt->fetch()) {
		$PROMPT_SHORT_TITLE = $row['PROMPT_SHORT_TITLE'];

		$cpm .= '<option value="' . $PROMPT_SHORT_TITLE . '"' . check_selected($PROMPT_SHORT_TITLE, $ITEM_VAL) . '>' . $PROMPT_SHORT_TITLE . '</option>';
	}
	$cpm .= "</select>";
	echo $cpm;
}

function menu_school_teacher($db, $ITEM_VAL, $ITEM_NAME)
{
	$mst = '<select id="' . $ITEM_NAME . '" name="' . $ITEM_NAME . '" size="1" required class="form-control"><option value=""' . check_selected("", $ITEM_VAL) . '>Select</option>';

	$query = "SELECT USER_FIRST_NAME, USER_LAST_NAME, USER_CODE from config_users where USER_STATUS='ACTIVE' and USER_LEVEL='TEACHER' and USER_ORGANIZATION in (select SCHOOL_NAME from config_schools where SCHOOL_SN=:guss) order by USER_LAST_NAME";
	$stmt = $db->prepare($query);
	$stmt->bindValue(':guss', $GLOBALS['USER_SCHOOL_SN'], PDO::PARAM_STR);
	$stmt->execute();
	while ($row = $stmt->fetch()) {
		$USER_FIRST_NAME = $row['USER_FIRST_NAME'];
		$USER_LAST_NAME = $row['USER_LAST_NAME'];
		$USER_CODE = $row['USER_CODE'];

		$mst .= '<option value="' . $USER_CODE . '"' . check_selected($USER_CODE, $ITEM_VAL) . '>' . $USER_LAST_NAME . ', ' . $USER_FIRST_NAME . '</option>';
	}
	$mst .= "</select>";
	echo $mst;
}


function check_selected($i1, $i2)
{
	if ($i1 == $i2) return " selected";
	else return "";
}
