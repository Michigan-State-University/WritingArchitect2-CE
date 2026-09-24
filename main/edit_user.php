<!DOCTYPE html>
<?php
include_once '../includes/Database.php';
include_once '../includes/WA_Accounts.php';
include_once '../includes/WA_Security.php';
include_once '../includes/WA_Functions.php';
include_once '../includes/StudentBatchImport.php';
include_once '../includes/Spreadsheet.php';
ini_set('display_errors', '1'); // DEVELOPMENT ONLY

//get incoming values
$database = new Database();
$db = $database->connect();

function get_all_classes($db) {
    $sql = "SELECT CLASS_ID, CLASS_NAME 
            FROM config_classes
            WHERE CLASS_SCHOOL_ID=:school_id
            ORDER BY CLASS_ID ASC";

    $stmt = $db->prepare($sql);
    $stmt->bindValue(':school_id', $GLOBALS['USER_SCHOOL_SN'], PDO::PARAM_STR);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function student_batch_result_row(array $data, string $status): array {
    $row = [
        $data['USER_CODE'],
        $data['USER_STATUS'],
        $data['USER_FIRST_NAME'],
        $data['USER_LAST_NAME'],
        $data['USER_EMAIL'],
        $data['USER_CLASSID'],
        $status,
    ];

    return spreadsheet_safe_row($row);
}

$secure_access = check_security($db);

$U_LEVEL = isset($_REQUEST['USER_LEVEL']) ? $_REQUEST['USER_LEVEL'] : die();
$uid = isset($_REQUEST['uid']) ? $_REQUEST['uid'] : die();
$mode = isset($_POST['mode']) ? $_POST['mode'] : "";
$cntl_message = "";
$result = "";

if ($mode == 'UPLOAD_CSV') {
    if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == UPLOAD_ERR_OK) {
        $tmpName = $_FILES['csv_file']['tmp_name'];
        $header = [
            'USER_CODE',
            'USER_STATUS',
            'USER_FIRST_NAME',
            'USER_LAST_NAME',
            'USER_EMAIL',
            'USER_CLASSID',
        ];

        try {
            // Parse and validate the complete file before opening a transaction.
            $rows = StudentBatchImport::parseCsvFile($tmpName);
            $allowedClassIds = array_map(
                'strval',
                array_column(get_all_classes($db), 'CLASS_ID')
            );
            foreach ($rows as $index => $data) {
                if (!in_array((string) $data['USER_CLASSID'], $allowedClassIds, true)) {
                    $csvRow = $index + 2;
                    throw new InvalidArgumentException(
                        "CSV row {$csvRow} has a USER_CLASSID outside your school."
                    );
                }
            }
            $output = [array_merge($header, ['STATUS'])];
            $safeRequestBudgetSeconds = 180;
            @set_time_limit(220);

            try {
                $statuses = StudentBatchImport::importRows(
                    $db,
                    $rows,
                    $GLOBALS['USER_ORGANIZATION'],
                    $GLOBALS['USER_LEVEL'],
                    $safeRequestBudgetSeconds
                );
            } catch (Throwable $exception) {
                $failureStatus = strpos($exception->getMessage(), 'unique USER_CODE migration') !== false
                    ? 'BLOCKED_MIGRATION'
                    : 'ROLLED_BACK';
                $statuses = array_fill(0, count($rows), $failureStatus);
                error_log('Student CSV upload rolled back: ' . get_class($exception));
            }
            foreach ($rows as $index => $data) {
                $output[] = student_batch_result_row($data, $statuses[$index]);
            }

            // Return one result row per parsed input row.
            if (ob_get_length()) {
                ob_end_clean();
            }
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="upload_results.csv"');
            header('Pragma: no-cache');
            header('Expires: 0');
            ini_set('display_errors', '0');
            ini_set('log_errors', '1');
            error_reporting(E_ALL);

            $out = fopen('php://output', 'w');
            foreach ($output as $line) {
                fputcsv($out, $line, ",", '"', "\\");
            }
            fclose($out);
            exit;
        } catch (InvalidArgumentException $exception) {
            $cntl_message = htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8') .
                ' No student accounts were saved.';
        }
    }
}



$ed_acct = new Account($db);
$ed_acct->USER_CODE = $uid;
$ed_acct->USER_CODE = isset($_POST['USER_CODE']) ? $_POST['USER_CODE'] : "";
$ed_acct->USER_STATUS = isset($_POST['USER_STATUS']) ? $_POST['USER_STATUS'] : "";
$ed_acct->USER_FIRST_NAME = isset($_POST['USER_FIRST_NAME']) ? $_POST['USER_FIRST_NAME'] : "";
$ed_acct->USER_LAST_NAME = isset($_POST['USER_LAST_NAME']) ? $_POST['USER_LAST_NAME'] : "";
$ed_acct->USER_ORGANIZATION = isset($_POST['USER_ORGANIZATION']) ? $_POST['USER_ORGANIZATION'] : "";
$ed_acct->USER_EMAIL = isset($_POST['USER_EMAIL']) ? $_POST['USER_EMAIL'] : "";
$ed_acct->USER_PASSWORD = isset($_POST['USER_PASSWORD']) ? $_POST['USER_PASSWORD'] : "";
//$ed_acct->USER_AUTHORITY = isset($_POST['USER_AUTHORITY']) ? $_POST['USER_AUTHORITY'] : "";
$ed_acct->USER_CLASSID = isset($_POST['USER_CLASSID']) ? $_POST['USER_CLASSID'] : "";

if ($mode == 'SAVE') {
	switch ($GLOBALS['USER_LEVEL']) {
		case 'ADMIN':
			// Admin can create user of any level
			$result = $ed_acct->save_account($db, $uid, $U_LEVEL);
			break;
		case 'TEACHER':
			if ($U_LEVEL == 'STUDENT') {
				$result = $ed_acct->save_account($db, $uid, $U_LEVEL);
			} else {
				$result = "DENIED";
			}
			break;
		case 'SCORER':
			if ($U_LEVEL == 'TEACHER' || $U_LEVEL == 'STUDENT') {
				$result = $ed_acct->save_account($db, $uid, $U_LEVEL);
			} else {
				$result = "DENIED";
			}
			break;
		default:
			$result = "DENIED";
			break;
	}
	$cntl_message = "User account saved.";
	if ($result != "") {
		switch ($result) {
			case "NOT UNIQUE":
				$cntl_message = "Duplicate User. User account not saved.";
				break;
			case "DENIED":
				$cntl_message = "Access Denied. User account not saved.";
				break;
			default:
				$cntl_message = "User account created.";
				$uid  = $result;
				break;
		}
	}
} else {
	$result = $ed_acct->load_account($db, $uid);
}

switch ($U_LEVEL) {
	case "ADMIN":
		if ($GLOBALS['USER_LEVEL'] != "ADMIN") die("Access denied.");
		if ($uid == "0") $GLOBALS['page_title'] = "Create Administrator";
		else $GLOBALS['page_title'] = "Edit Administrator";
		break;
	case "TEACHER":
		if ($GLOBALS['USER_LEVEL'] != "ADMIN" && $GLOBALS['USER_LEVEL'] != "SCORER") die("Access denied.");
		if ($uid == "0") $GLOBALS['page_title'] = "Create Teacher";
		else $GLOBALS['page_title'] = "Edit Teacher";
		break;
	case "SCORER":
		if ($GLOBALS['USER_LEVEL'] != "ADMIN") die("Access denied.");
		if ($uid == "0") $GLOBALS['page_title'] = "Create Scorer";
		else $GLOBALS['page_title'] = "Edit Scorer";
		break;
	case "STUDENT":
		if ($GLOBALS['USER_LEVEL'] == 'STUDENT') die("Access denied.");
		if ($uid == "0") $GLOBALS['page_title'] = "Create Student";
		else $GLOBALS['page_title'] = "Edit Student";
		break;
}
?>

<html lang="en">

<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
	<meta name="description" content="" />
	<meta name="author" content="" />
	<link rel="icon" type="image/x-icon" href="/favicon.ico" />
	<title>Writing Architect</title>
	<!-- Favicon-->
	<link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
	<!-- Core theme CSS (includes Bootstrap)-->
	<link href="../css/styles.css" rel="stylesheet" />
	<link href="../css/WA.css" rel="stylesheet" />
	<link href="../css/parsley.css" rel="stylesheet" />
	<script src="https://code.jquery.com/jquery-3.5.0.js"></script>
	<script language="javascript">
		function check_duplicate() {
			var user_id = document.getElementById('uid').value;
			var user_code = document.getElementById('USER_CODE').value;
			if (user_code !== "") {
				$.ajax({
					url: '../ajax/universal_duplicate.php',
					type: "POST",
					data: ({
						INDEX_ID: user_id,
						FLD_VALUE: user_code,
						FLD_NAME: "USER_CODE",
						INDEX_NAME: "USER_ID",
						TBL_NAME: "config_users"
					}),
					success: function(data) {
						if (data.length > 10) {
							document.getElementById('dup_message').innerHTML = data;
							//	alert(data);
							return false;
						} else return true;

					},
					error: function(data) {
						alert('error: ' + data.responseText);
						return false;
					}
				});
			} else return false;
		}

		$(document).ready(function() {
			var cntl_msg = "<?php echo $cntl_message ?>";
			if (cntl_msg !== "") {
				$("#CTL_MESSAGE").show();
				setTimeout(function() {
					$("#CTL_MESSAGE").hide();
				}, 3800);
			}
		});
	</script>
</head>

<body>
	<div class="d-flex" id="wrapper">
		<!-- Sidebar-->
		<!-- Menu navigation-->
		<?php require '../includes/WA_menu.php';   ?>
		<!-- Page content wrapper-->
		<div id="page-content-wrapper">
			<!-- Top navigation-->
			<?php require '../includes/header.php';   ?>
			<!-- Page content-->
			<div class="container-fluid">
				<br>
				<div class="popup" id="CTL_MESSAGE">&nbsp;<?php echo $cntl_message ?></div>

				<form id="usereditor" action="edit_user.php?id=<?php echo $GLOBALS["SESSION_ID"]; ?>" method="post" name="usereditor" data-parsley-validate>
					<input type="hidden" id="mode" name="mode" value="SAVE">
					<input type="hidden" id="uid" name="uid" value="<?php echo $uid; ?>">
					<input type="hidden" id="USER_LEVEL" name="USER_LEVEL" value="<?php echo $U_LEVEL; ?>">
					<table border="0" cellspacing="4" cellpadding="2">
						<tr>
							<td class="EditTitle"><label for="USER_CODE">User ID:</label></td>
							<td><input id="USER_CODE" class="form-control" type="text" name="USER_CODE" maxlength="20" value="<?php echo account_html($ed_acct->USER_CODE); ?>" width="20" required></td>
							<td class="EditTitle"><label for="STATUS">Status:</label></td>
							<td><?php create_drop_menu($db, "STATUS", $ed_acct->USER_STATUS, "USER_STATUS"); ?></td>
						</tr>
						<tr>
							<td class="EditTitle"><label for="USER_FIRST_NAME">First Name:</label></td>
							<td><input id="USER_FIRST_NAME" value="<?php echo account_html($ed_acct->USER_FIRST_NAME); ?>" class="form-control" type="text" name="USER_FIRST_NAME" maxlength="50" required></td>
							<td class="EditTitle"><label for="USER_LAST_NAME">Last Name:</label></td>
							<td><input id="USER_LAST_NAME" value="<?php echo account_html($ed_acct->USER_LAST_NAME); ?>" class="form-control" type="text" name="USER_LAST_NAME" maxlength="50" required></td>
						</tr>
						<?php if (($GLOBALS['USER_LEVEL'] == 'ADMIN' || $GLOBALS['USER_LEVEL'] == 'SCORER' || $GLOBALS['USER_LEVEL'] == 'TEACHER') && $U_LEVEL == 'STUDENT') {
							// This handles class menu for the student
						?>
							<tr>
								<td class="EditTitle"><label for="USER_SCHOOL"><input type="hidden" id="USER_ORGANIZATION" name="USER_ORGANIZATION" value="<?php echo $GLOBALS['USER_ORGANIZATION']; ?>">Class:</label></td>
								<td colspan="3"><?php create_class_menu($db, $ed_acct->USER_CLASSID, "USER_CLASSID"); ?></td>
							</tr>
							<?php } else {
							if (($GLOBALS['USER_LEVEL'] == 'ADMIN' || $GLOBALS['USER_LEVEL'] == 'SCORER' || $GLOBALS['USER_LEVEL'] == 'TEACHER')) {
								// This handles MSU Level school menu
							?>
								<tr>
									<td class="EditTitle"><label for="USER_ORGANIZATION">School:</label></td>
									<td colspan="3"><?php create_school_menu($db, $ed_acct->USER_ORGANIZATION, "USER_ORGANIZATION"); ?></td>
								</tr>
							<?php } else {
								// This handles school (No menu since they cannot chnage the school)
							?>

								<tr>
									<td class="EditTitle"><label for="USER_SCHOOL"><input type="hidden" id="USER_ORGANIZATION" name="USER_ORGANIZATION" value="<?php echo $GLOBALS['USER_ORGANIZATION']; ?>">School:</label></td>
									<td colspan="3"><?php echo $GLOBALS['USER_ORGANIZATION']; ?></td>
								</tr>
						<?php 	 }
						}
						?>

						<tr>
							<td class="EditTitle"><label for="USER_EMAIL">Email:</label></td>
							<td colspan="3"><input id="USER_EMAIL" value="<?php echo account_html($ed_acct->USER_EMAIL); ?>" class="form-control" type="email" name="USER_EMAIL" maxlength="100" data-parsley-trigger="change" required></td>
						</tr>
						<tr>
							<td class="EditTitle"><label for="USER_PASSWORD">Password:</label></td>
							<td><input id="USER_PASSWORD" value="<?php echo account_html($ed_acct->USER_PASSWORD); ?>" class="form-control" type="text" name="USER_PASSWORD" maxlength="16"></td>
							<?php if ($U_LEVEL == "STUDENT HIDDEN") { ?>
								<td class="EditTitle"><label for="USER_AUTHORITY">Job Authority:</label></td>
								<td><input id="USER_AUTHORITY" value="<?php echo account_html($ed_acct->USER_AUTHORITY); ?>" class="form-control" type="text" name="USER_AUTHORITY" maxlength="100"></td>
							<?php  } else { ?>
								<td colspan="2" class="warning_text">Enter password ONLY if you are <br>intending to set or reset the password.</td>
							<?php } ?>
						</tr>
						<tr>
							<td>&nbsp;<br><br></td>
							<td>
								<input type="submit" class="waButton" value="Save">&nbsp;&nbsp;&nbsp;&nbsp;
								<?php
								if ($uid > 0 && $GLOBALS['USER_LEVEL'] == 'ADMIN') { ?>
									<a href="edit_user.php?uid=0&USER_LEVEL=<?php echo $U_LEVEL; ?>&id=<?php echo $GLOBALS["SESSION_ID"]; ?>" class="waButton">Create Another User</a>
								<?php
								}
								?>
							</td>
						</tr>
					</table>
				</form>

				<!-- Bulk Upload Students -->
				<?php if ($U_LEVEL == 'STUDENT') { ?>
					<hr>
					<h3>Bulk Upload Students</h3>
					<form id="csvupload" action="edit_user.php?id=<?php echo $GLOBALS["SESSION_ID"]; ?>&USER_LEVEL=<?php echo $U_LEVEL; ?>&uid=0" method="post" enctype="multipart/form-data">
						<input type="hidden" name="mode" value="UPLOAD_CSV">
						<input type="file" name="csv_file" accept=".csv" required>
						<input type="submit" class="waButtonSmall" value="Upload CSV">
					</form>
					<p><small>CSV must include columns: USER_CODE, USER_STATUS, USER_FIRST_NAME, USER_LAST_NAME, USER_EMAIL, USER_PASSWORD, USER_CLASSID. Upload at most 75 students (2 MB) at a time.</small></p>

					<p><b>NOTE: </b>You must use the class ID for input into the system, see the below section to find the ID of your class.</p>
					<hr><br>

						<button onclick="toggleClassTable()" class="waButtonSmall">
							Display Class IDs
						</button>

						<div id="classTable" style="display:none; margin-top:15px;">
							<h4>Class IDs</h4>
							<table border="1" cellpadding="8">
								<tr>
									<th>Class ID</th>
									<th>Class Name</th>
								</tr>

								<?php
								$classes = get_all_classes($db);
								foreach ($classes as $class) {
									echo "<tr>";
									echo "<td>" . htmlspecialchars($class['CLASS_ID']) . "</td>";
									echo "<td>" . htmlspecialchars($class['CLASS_NAME']) . "</td>";
									echo "</tr>";
								}
								?>
							</table>
						</div>

					<h5>Bulk Upload Templates</h5>
					<p>Download a CSV template for bulk uploading:</p>
					<ul>
						<li><a href="../templates/students_template.csv" download>Download Student Template</a></li>
					</ul>
				<?php } ?>

				<div id="dup_message"></div>
			</div>
		</div>
	</div>
	<!-- Bootstrap core JS-->
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js"></script>
	<!-- Core theme JS-->
	<script src="../js/scripts.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/7.3/highlight.min.js"></script>
	<script src="../js/parsley.min.js"></script>
	<?php require '../includes/empty_footer.php';   ?>
	<script type="text/javascript">
		$(function() {
			$('#usereditor').parsley().on('field:validated', function() {
					var ok = $('.parsley-error').length === 0;

				})
				.on('form:submit', function() {
					//return false; // Don't submit form for this demo
				});
		});
	</script>
	<script>
		function toggleClassTable() {
			const tbl = document.getElementById("classTable");
			tbl.style.display = (tbl.style.display === "none") ? "block" : "none";
		}
	</script>

</body>

</html>
