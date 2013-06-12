<?php

session_start();

if (isset($_COOKIE["userid"]) && !isset($_SESSION["userid"]))
    $_SESSION["userid"] = $_COOKIE["userid"];

$r_c = 42;
require "inc/essential.php";
include "inc/lightopenid/openid.php";

?>

<!doctype html>
<html>
<head>
    <title>thecookiefactory.org</title>
    <meta http-equiv='Content-Type' content='text/html;charset=UTF-8'>
    <link rel='stylesheet' type='text/css' href='base.css'>
    <link rel='stylesheet' type='text/css' href='http://fonts.googleapis.com/css?family=Bitter:400,700|Open+Sans:400,300,600'>
    <link rel='shortcut icon' href='favicon.ico' type='image/x-icon'>
    <script src='js/main.js'></script>
</head>
<body>

<?php include_once("inc/analyticstracking.php") ?>

<header>

</header>

<div class='wrapper'>

<nav>
<span class='nav-menubar'>
<a class='menu-item' href='?p=news'>news</a><a class='menu-item' href='?p=maps'>maps</a><a class='menu-item' href='?p=streams'>streams</a><a class='menu-item' href='?p=forums'>forums</a>

<?php
$q = mysqli_query($con, "SELECT `name` FROM `cpages`");
$cpages = Array();
while ($row = mysqli_fetch_assoc($q)) {
$cpages[] = $row["name"];
?>
<a class='menu-item' href='?p=<?php echo $row["name"]; ?>'><?php echo $row["name"]; ?></a>
<?php
}

/*if (IsAnyoneLive()) {
?>
Someone is streaming!!
<?php
}*/
?>
</span>

<div class='nav-actionbar'>
<form class='menu-item' action='?p=search' method='post'>
<input type='text' name='searchb' style='display: inline;' class='searchbox' placeholder='search' onfocus='searchboxFocus();' onblur='searchboxBlur();' autocomplete='off'>
</form>
<?php

login();

?>
</div>

</nav>
<hr>

<section class='include-section'>

<?php

if (isset($_GET["p"]) && strip($_GET["p"]) != null && strip($_GET["p"]) != "" && strip($_GET["p"] != "essential")) {

    $p = strip($_GET["p"]);

    if (file_exists("inc/".$p.".php") || $p == "login" || $p == "logout")
        require "inc/".$p.".php";
    elseif (in_array($p, $cpages))
        require "inc/custom.php";
    else
        header("Location: notfound.php");

} else {

    require "inc/news.php";

}

?>

</section>

</div>

<footer>
2013 thecookiefactory.org<br>
<div class='contact-us'>
    <span class='contact-us-link'><a href='steam://url/GroupSteamIDPage/103582791433434721' target='_blank'>Steam</a></span>
    <span class='contact-us-link'><a href='http://facebook.com/thecookiefactoryorg' target='_blank'>Facebook</a></span>
    <span class='contact-us-link'><a href='http://youtube.com/thecookiefactoryorg' target='_blank'>YouTube</a></span>
    <span class='contact-us-link'><a href='http://github.com/thecookiefactory' target='_blank'>GitHub</a></span>
</div>
</footer>


<?php
if (isset($redirect))
    echo "<script type='text/javascript'>
    <!--
    setTimeout('window.location = \"?p=".$redirect."\"')
    //-->
    </script>";
?>
</body>
</html>
<?php
function IsAnyoneLive() {

    global $con;

    $fquery = mysqli_query($con, "SELECT `twitch` FROM `streams` WHERE `active`=1");

    while ($frow = mysqli_fetch_assoc($fquery)) {
        if (islive($frow["twitch"])) {
            return true;
        }
    }

    return false;

}
