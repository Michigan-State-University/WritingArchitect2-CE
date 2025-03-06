<?php
switch ($GLOBALS['USER_LEVEL']) {
	case "ADMIN":
		echo '<div class="border-end bg-white" id="sidebar-wrapper"> <img src="../images/wa logo small copy.png" alt="" ><div class="sidebar-heading border-bottom bg-light"></div><div class="list-group list-group-flush">';
		$r = write_menu_item('adm_dashboard.php', 'icn_dashboard', 'Dashboard');
		$r = write_menu_item('adm_admins.php', 'icn_admin', 'Administrators');
		$r = write_menu_item('adm_schools.php', 'icn_school', 'Schools');
		$r = write_menu_item('adm_teachers.php', 'icn_teacher', 'Teachers');
		$r = write_menu_item('adm_scorers.php', 'icn_teacher', 'Scorers');
		$r = write_menu_item('sch_classes.php', 'icn_students', 'Classes');
		$r = write_menu_item('adm_students.php', 'icn_students', 'Students');
		$r = write_menu_item('adm_prompt_setup.php', 'icn_quiz', 'Prompts');
		$r = write_menu_item('adm_quiz_setup.php', 'icn_quiz', 'Quick-Writes');
		$r = write_menu_item('adm_scoring.php', 'icn_scoring', 'Scoring');
		$r = write_menu_item('adm_reports.php', 'icn_reports', 'Reports');
		$r = write_menu_item('adm_recommendations.php', 'icn_recommendations', 'Recommendations');
		$r = write_help_item();
		echo '</div></div>';
		break;
	case "TEACHER":
		echo '<div class="border-end bg-white" id="sidebar-wrapper" style="valign=middle;background:white"><img src="../images/wa logo sm.png" alt=""><div class="sidebar-heading border-bottom bg-light"></div><div class="list-group list-group-flush">';
		$r = write_menu_item('sch_dashboard.php', 'icn_dashboard', 'Dashboard');
		$r = write_menu_item('adm_teachers.php', 'icn_teacher', 'Teachers');
		$r = write_menu_item('sch_classes.php', 'icn_students', 'Classes');
		$r = write_menu_item('sch_students.php', 'icn_students', 'Students');
		$r = write_menu_item('adm_prompt_setup.php', 'icn_quiz', 'Prompts');
		$r = write_menu_item('sch_quizzes.php', 'icn_quiz', 'Quick-Writes');
		$r = write_menu_item('sch_scoring.php', 'icn_scoring', 'Scoring');
		$r = write_menu_item('sch_recommendations.php', 'icn_recommendations', 'Recommendations');
		$r = write_menu_item('sch_reports.php', 'icn_reports', 'Reports');
		$r = write_help_item();
		echo '</div></div>';
		break;
	case "SCORER":
		echo '<div class="border-end bg-white" id="sidebar-wrapper" style="valign=middle;background:white"><img src="../images/wa logo sm.png" alt=""><div class="sidebar-heading border-bottom bg-light"></div><div class="list-group list-group-flush">';
		$r = write_menu_item('sch_dashboard.php', 'icn_dashboard', 'Dashboard');
		$r = write_menu_item('adm_teachers.php', 'icn_teacher', 'Teachers');
		$r = write_menu_item('adm_scorers.php', 'icn_teacher', 'Scorers');
		$r = write_menu_item('sch_classes.php', 'icn_students', 'Classes');
		$r = write_menu_item('sch_students.php', 'icn_students', 'Students');
		$r = write_menu_item('sch_quizzes.php', 'icn_quiz', 'Quick-Writes');
		$r = write_menu_item('sch_scoring.php', 'icn_scoring', 'Scoring');
		$r = write_menu_item('sch_recommendations.php', 'icn_recommendations', 'Recommendations');
		$r = write_menu_item('sch_reports.php', 'icn_reports', 'Reports');
		$r = write_help_item();
		echo '</div></div>';
		break;
	case "STUDENT":
		break;
}

function write_menu_item($url, $icon, $name)
{
	echo '<a class="list-group-item list-group-item-action list-group-item-light p-3" href="' . $url . '?id=' . $GLOBALS["SESSION_ID"] . '"><img src="../images/' . $icon . '.png">&nbsp;<span style="color:#18453B">' . $name . '</span></a>';
}


function write_help_item()
{
	$url = "https://baserow.io/form/aUp5khDlD_ULALZSLDOfDN2RJoDLOvSAQQSGgr1pves?hide_Status&prefill_Status=New&hide_Submitted%20By&prefill_Submitted%20By=" . $GLOBALS["USER_EMAIL"];
	echo '<a class="list-group-item list-group-item-action list-group-item-light p-3" href="' . $url . '" target="_blank"><img src="../images/icn_help.png">&nbsp;<span style="color:#18453B">Help Desk</span></a>';
}
