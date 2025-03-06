<!DOCTYPE html>
<?php
$msg = isset($_GET['msg']) ? $_GET['msg'] : "";
$long_msg = "";
switch ($msg) {
    case "LO":
        $long_msg = "You successfully logged out";
        break;
    case "TO":
        $long_msg = "System timed out";
        break;
}

?>

<head>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Georgia&display=swap" rel="stylesheet">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Home - Writing Architect</title>
    <link rel="stylesheet" href="css/header.css" />
    <link rel="stylesheet" href="css/navbar.css" />
    <link rel="stylesheet" href="css/main.css" />
    <link rel="stylesheet" href="css/footer.css" />
</head>

<body class="banner">
    <header> <!--Banner image for the top of the web page-->
        <img class="header" src="images/banner.jpg" alt="Rocky Background" />
    </header>
    <nav> <!--Navigation bar with links to other pages and project title-->
        <ul class="fullscreen">
            <li class="left"><a>Writing Architect</a></li>
            <li><a class="link" href="/">Home</a></li>
            <li><a class="link" href="/research.html">Research</a></li>
            <li><a class="link" href="/resourceLogger.html">For Educators</a></li>
            <li><a class="link" href="/main/login.php?em=f">Login</a></li>
            <li><a class="link" href="/main/login.php?em=f" target="_blank"><img src="images/WA-logo-computer.png" alt="Writing Architect" /></a></li>
        </ul>
        <ul class="menubar">
            <li class="left"><a>Writing Architect</a></li>
            <li><!--Checkbox to make menu button toggle on/off dropdown menu visibility-->
                <label class="switch">
                    <a class="link"><img class="menuicon" src="images/menu-bar.png" alt="Menu" /></a>
                    <input type="checkbox">
                    <span class="dropdown-menu">
                        <ul>
                            <li><a class="link" href="/">Home</a></li>
                            <li><a class="link" href="/research.html">Research</a></li>
                            <li><a class="link" href="/resourceLogger.html">For Educators</a></li>
                            <li><a class="link" href="/main/login.php?em=f">Login</a></li>
                        </ul>
                    </span>
                </label>
            </li>
        </ul>
    </nav>
    <main>
        <section class="wrapper">
            <div class="box"><img class="headshot" src="images/website-login.png" alt="Picture of Thomas Toaz" /></div>
            <div class="description">
                <p>
                    <span>The Writing Architect</span> was designed as an instructional tool to match research-based
                    writing instruction with a students’ current level of development in critical areas of writing.
                    This involves several steps. First, students will log into the Writing Architect Quick Write web application
                    to plan and write a 15-minute essay and a 90-second typing fluency check. This web application also has a
                    scoring and reporting system for the research team to score the students’ writing and for teachers to access
                    the reports of the scores. There are different score types for two different purposes. The first purpose is
                    determining instructional content needs. There are scores for 7 different facets of writing, which show where
                    students have strengths and areas of instructional need. The 7 instructional scores are linked to research-based
                    instructional materials located in this repository. The second score type is a general outcome measure,
                    which is called correct minus incorrect writing sequences (CIWS). The purpose of the CIWS score is to monitor
                    student progress from the beginning to the middle to the end of the year. CIWS does this well because it most
                    highly related to student performance on the state’s ELA test and nationally-normed assessments of writing
                    (Truckenmiller, McKindles, Petscher, Eckert, & Tock, 2020).
                    <!-- <a href="https://writing-architect.netlify.app/#/" target="_blank">Writing Architect.</a> -->

                </p>
            </div>
        </section>
        <hr>
        <!-- <p class="funFactTitle">Potential Blurb Title</p>
        <p class="funfact">Insert blurb or message!</p> -->
    </main>
    <footer>
        <p>©Copyright 2024</p>
    </footer>
</body>

</html>