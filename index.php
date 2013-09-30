<?php

session_start();

$r_c = 0;
require "inc/functions.php";
include "inc/lightopenid/openid.php";

ccookies();

?>

<!doctype html>
<html>
<head>
    <title>thecookiefactory</title>
    <meta http-equiv='Content-Type' content='text/html;charset=UTF-8'>
    <link rel='stylesheet' type='text/css' href='/base.css'>
    <link rel='stylesheet' type='text/css' href='http://fonts.googleapis.com/css?family=Bitter:700|Open+Sans:300,400,600|Roboto+Slab'>
    <link rel='shortcut icon' href='favicon.ico' type='image/x-icon'>
    <script src='/js/main.js'></script>
</head>
<body>

<?php include_once("inc/analyticstracking.php") ?>

<header>

</header>

<div class='wrapper'>

<nav>
<span class='nav-menubar'>
<a class='menu-item' href='/news'>news</a><a class='menu-item' href='/maps'>maps</a><a class='menu-item' href='/streams'>streams</a><a class='menu-item' href='/forums'>forums</a>

<?php

// fetching the custom pages' names
$pagesquery = $con->query("SELECT `custompages`.`title` FROM `custompages` WHERE BIN(`custompages`.`live`) = 1");

$pages = Array();

while ($pagesrow = $pagesquery->fetch()){

    // and storing them in an array
    $pages[] = $pagesrow["title"];
    ?>
    <a class='menu-item' href='/<?php echo $pagesrow["title"]; ?>'><?php echo $pagesrow["title"]; ?></a>
    <?php

}

if (IsAnyoneLive()) {

    ?>
    Someone is streaming!!
    <?php

}

?>
</span>

<div class='nav-actionbar'>
<form class='menu-item' action='/search' method='get'>
<input type='text' name='term' style='display: inline;' class='searchbox' placeholder='search' onfocus='searchboxFocus();' onblur='searchboxBlur();' autocomplete='off' maxlength='50'>
</form>
<?php

// see description in functions.php
login();

?>
</div>

</nav>
<hr>

<section class='include-section'>

<?php

if (isset($_GET["p"]) && vf($_GET["p"])) {

    $p = strip($_GET["p"]);

    if (file_exists("inc/".$p.".php") && $p != "functions" && $p != "config") {

        require "inc/".$p.".php";

    } else if (in_array($p, $pages)) {

        require "inc/custom.php";

    } else if ($p != "login" && $p != "logout") {

        header("Location: /notfound.php");

    }

} else {

    require "inc/news.php";

}

?>

</section>

</div>

<footer>
2013 thecookiefactory.org<br>
<div class='contact-us'>
    <span class='contact-us-link'><a href='http://steamcommunity.com/groups/thecookiefactory' target='_blank'>Steam</a></span>
    <span class='contact-us-link'><a href='http://gplus.to/thecookiefactory' target='_blank'>Google+</a></span>
    <span class='contact-us-link'><a href='http://facebook.com/thecookiefactoryorg' target='_blank'>Facebook</a></span>
    <span class='contact-us-link'><a href='http://youtube.com/thecookiefactoryorg' target='_blank'>YouTube</a></span>
    <span class='contact-us-link'><a href='http://github.com/thecookiefactory' target='_blank'>GitHub</a></span>
</div>
</footer>

</body>
</html>

<?php

function IsAnyoneLive() {

    global $con;

    $streamsquery = $con->query("SELECT `streams`.`title` FROM `streams`");

    while ($streamsrow = $streamsquery->fetch()) {
        if (vf($streamsrow["title"])) {

            return true;

        }
    }

    return false;

}
