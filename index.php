<?php

session_start();

// BETATEST CHECK
if (!isset($_SESSION["beta"])) {
    header("Location: betalogin.php");
    die();
} else echo "beta";
// /

if (isset($_COOKIE["userid"]) && !isset($_SESSION["userid"]))
    $_SESSION["userid"] = $_COOKIE["userid"];

$r_c = 42;
require "inc/essential.php";

?>

<!doctype html>
<html>
<head>
    <title>thecookiefactory.org</title>
    <meta http-equiv='Content-Type' content='text/html;charset=UTF-8'>
    <link rel='stylesheet' type='text/css' href='base.css'>
    <link rel='stylesheet' type='text/css' href='http://fonts.googleapis.com/css?family=Enriqueta:400,700|Open+Sans:400,300,600'>
    <script src='js/main.js'></script>
</head>
<body>
    
<?php include_once("inc/analyticstracking.php") ?>

<header>

</header>

<div id='wrapper'>

<nav>
<span id='nav-menubar'>
<a class='menu-item' href='?p=news'>news</a><a class='menu-item' href='?p=maps'>maps</a><a class='menu-item' href='?p=streams'>streams</a><a class='menu-item' href='?p=projects'>projects</a>
</span>

<div id='nav-actionbar'>
<form class='menu-item' action='?p=search' method='post'>
<input type='text' name='searchb' style='display: inline;' id='searchbox' placeholder='search' onfocus='searchboxFocus();' onblur='searchboxBlur();' autocomplete='off'>
</form>
<?php

if (isset($_SESSION["userid"])) {
    echo "<span class='menu-item' id='actionbar-logindata'>logged in as <span id='actionbar-username'>".getname($_SESSION["userid"])."</span></span><span class='menu-item'><a href='?p=logout'>log out</a></span>";
} else {
    echo "<span class='menu-item faux-link' onclick='showLoginBar();'>log in</span><a class='menu-item' href='?p=register'>register</a>";
}


?>
</div>

<div id='nav-loginbar'>
  <form class='menu-item' action='?p=login' method='post'>
    <span class='menu-item faux-link' onclick='hideLoginBar();'>&laquo; </span>
    <span class='input-wrapper'><input class='account-input' pattern='\w{2,10}' type='text' name='username' placeholder='username' required='required' autocomplete='off' oninput='checkInputBox(this);'></span>
    <span class='input-wrapper'><input class='account-input' pattern='.{6,30}' type='password' name='password' placeholder='password' required='required' autocomplete='off' oninput='checkInputBox(this);'></span>
    <span class='input-wrapper'><input class='account-input account-button' type='submit' value='go!' name='submit'></span>
  </form>
</div>

</nav>
<hr>

<section>

<?php

if (isset($_GET["p"]) && $_GET["p"] != null && $_GET["p"] != "" && $_GET["p"] != "essential") {
    if (file_exists("inc/".$_GET["p"].".php"))
        require "inc/".$_GET["p"].".php";
    else
        echo "404";
} else {
    require "inc/news.php";
}

?>

</section>

</div>

<footer>
2012 thecookiefactory.org<br>
<?php
if (checkadmin()) {
    echo "<a href='admin' target='_blank'>admin</a><br>";
}
?>
YYYY-MM-DD H:i (CET/CEST)
</footer>
<!-- ezt persze majd nem ide-->
<div id='contact-us'>
<a href='steam://url/GroupSteamIDPage/103582791433434721' target='_blank'>Steam</a>&nbsp;
<a href='http://facebook.com/thecookiefactoryorg' target='_blank'>Facebook</a>&nbsp;
<a href='http://youtube.com/thecookiefactoryorg' target='_blank'>YouTube</a>&nbsp;
<a href='http://github.com/thecookiefactory' target='_blank'>GitHub</a>
</div>
<?php
if (isset($redirect))
    echo "<script type='text/javascript'>
    <!--
    setTimeout('window.location = \"?p=".$redirect."\"', 5000)
    //-->
    </script>";
?>
</body>
</html>
