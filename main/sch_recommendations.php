<!DOCTYPE html>
<?php
include_once '../includes/Database.php';
include_once '../includes/WA_Accounts.php';
include_once '../includes/WA_Security.php';
ini_set('display_errors', '1'); // DEVELOPMENT ONLY

//get incoming values
$database = new Database();
$db = $database->connect();

$secure_access = check_security($db);
$GLOBALS['page_title'] = "Recommendations";

if ($GLOBALS['USER_LEVEL'] == "STUDENT") die("Access Denied");

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

            </div>
        </div>
    </div>
    <!-- Bootstrap core JS-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Core theme JS-->
    <script src="../js/scripts.js"></script>
    <?php require '../includes/empty_footer.php';   ?>
</body>

</html>
