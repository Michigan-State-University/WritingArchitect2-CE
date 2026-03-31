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
    <!-- Page title and style sheets -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Georgia&display=swap" rel="stylesheet">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="css/header.css" />
    <link rel="stylesheet" href="css/navbar.css" />
    <link rel="stylesheet" href="css/main.css" />
    <link rel="stylesheet" href="css/footer.css" />

    <!-- Unique to this page -->
    <title>Home - Writing Architect</title>
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
            <!-- Image of WA -->
            <div class="box"><img class="headshot" src="images/website-login.png" alt="WA Login Page" /></div>
            <div class="description">
                <div>
                    <span>The Writing Architect</span> assessment was developed to innovate Curriculum-Based Measurement in 
                    Written Expression for administering and scoring students’ informational writing. The innovations are:
                    <ul>
                        <li>
                           Using an informational passage as a prompt, which is the primary genre required for the 
                            standards and state tests in grades 5 and 8.
                        </li>
                        <li> 
                            Scoring that switches from a rubric score that is hard to interpret and see growth to 
                            frequency counts that make visible to both educators and students the goal-setting components of writing.
                        </li>
                        <li> 
                            Scoring that aligns directly and intuitively with theories of writing development 
                            (i.e., Direct and Indirect Effects of Writing model; Kim & Graham, 2022) and 
                            research-based instruction (Troia, 2014) for:
                            <ul>
                                <li>Text structure (TIDE)</li>
                                <li>Word Complexity (Vocabulary)</li>
                                <li>
                                    Word Accuracy (Spelling)
                                    <ul>
                                        <li>
                                            Do you need rationale to include spelling in your 
                                            school’s curriculum and your students’ IEPs? See 
                                            this <a class="highlight-link" target="_blank" href="https://www.youtube.com/watch?v=c18CAOeGcxY&t=38s">excellent webinar.</a>
                                        </li>
                                    </ul>
                                </li>
                                <li>Sentence Accuracy</li>
                                <li>Typing Fluency</li>
                            </ul>
                        </li>
                    </ul>
                    <p>
                        The development of a computer application to contain the administration and scoring of the Writing Architect 
                        2.0 was supported by grant #R305A210061 from the U.S. Department of Education, Institute for Education 
                        Sciences. The application was created using open source code so that any education agency can install 
                        the application on a server and run it. The open source application will be available on github in 2026. 
                        Instructions are available for launching the application; however, the installation, launch, and 
                        maintenance of the application may take a person with some expertise in computer programming.
                    </p>
                    <p>
                        Alternatively, some educators and researchers may wish to use the Writing Architect protocol on their 
                        own without setting up the application. To do this manually, follow
                        <a class="highlight-link" href="teacher_resources/Writing_Architect_without_the_app.pdf" target="_blank">these directions</a>
                    </p>
                    <p>
                        Our excellent computer programmer, Thomas Toaz, also created a mini web application to help educators 
                        score Text Structure and Word Complexity. Educators can access that <a class="highlight-link" href="/tideGradingArea.html">here</a>.
                    </p>
                </div>
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
