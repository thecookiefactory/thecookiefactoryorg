<?php

session_start();

$r_c = 0;
require_once "inc/functions.php";
require_once "inc/classes/master.class.php";
require_once "inc/classes/user.class.php";
require_once "inc/lightopenid/openid.php";
require_once "inc/twig/lib/Twig/Autoloader.php";

Twig_Autoloader::register();

$loader = new Twig_Loader_Filesystem("inc/templates");
$twig = new Twig_Environment($loader);

cookieCheck();

$user = new user((isset($_SESSION["userid"]) ? $_SESSION["userid"] : null));

?>

<!doctype html>
<html>
<head>
    <title>thecookiefactory</title>
    <meta http-equiv='Content-Type' content='text/html;charset=UTF-8'>
    <link rel='stylesheet' type='text/css' href='/base.css'>
    <link rel='stylesheet' type='text/css' href='http://fonts.googleapis.com/css?family=Bitter:700|Open+Sans:300,400,600|Roboto+Slab'>
    <link rel='shortcut icon' href='/favicon.ico' type='image/x-icon'>
    <script src='/js/main.js'></script>
</head>
<body>

<?php include_once("inc/analyticstracking.php") ?>

<a href='/'>
<header>

</header>
</a>

<div class='wrapper'>

<nav>
<span class='nav-menubar'>
<a class='menu-item' href='/news'>news</a><a class='menu-item' href='/maps'>maps</a><a class='menu-item' href='/streams'>streams<?php echo ((isAnyoneLive()) ? "<sup class='menu-live-indicator'>live</sup>" : "") ?></a><a class='menu-item' href='/forums'>forums</a>

<?php

try {

    $squery = $con->query("SELECT `custompages`.`title`, `custompages`.`stringid` FROM `custompages` WHERE BIN(`custompages`.`live`) = 1");

    $pages = Array();

    while ($srow = $squery->fetch()){

        $pages[] = $srow["stringid"];
        echo "<a class='menu-item' href='/" . $srow["stringid"] . "'>" . $srow["title"] . "</a>";

    }

} catch (PDOException $e) {

    echo "An error occurred while trying to fetch the custom pages.";

}

?>
</span>

<div class='nav-actionbar'>
<form class='menu-item' onsubmit='searchRedirect();'>
<input type='text' id='searchbox' name='term' style='display: inline;' class='searchbox' placeholder='search' onfocus='searchboxFocus();' onblur='searchboxBlur();' autocomplete='off' maxlength='50'>
</form>

<?php

$user->login();

?>

</div>

</nav>
<hr>

<section class='include-section'>

<?php

if (isset($_GET["p"]) && vf($_GET["p"])) {

    $p = strip($_GET["p"]);

    if (file_exists("inc/" . $p . ".php") && $p != "functions" && $p != "config") {

        require_once "inc/" . $p . ".php";

    } else if (in_array($p, $pages)) {

        require_once "inc/custom.php";

    } else if ($p != "login" && $p != "logout") {

        header("Location: /notfound.php");

    }

} else {

    require_once "inc/news.php";

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

function cookieCheck() {

    global $con;

    if (isset($_COOKIE["userid"]) && !isset($_SESSION["userid"])) {

        $cv = $_COOKIE["userid"];

        $squery = $con->prepare("SELECT `users`.`id` FROM `users` WHERE `users`.`cookieh` = :cv");
        $squery->bindValue("cv", hash("sha256", $cv),  PDO::PARAM_STR);
        $squery->execute();

        if ($squery->rowCount() == 1) {

            $srow = $squery->fetch();

            $_SESSION["userid"] = $srow["id"];

            $cookieh = cookieh();
            $uquery = $con->prepare("UPDATE `users` SET `users`.`cookieh` = :cookieh WHERE `users`.`id` = :id");
            $uquery->bindValue("cookieh", hash("sha256", $cookieh), PDO::PARAM_STR);
            $uquery->bindValue("id", $srow["id"], PDO::PARAM_INT);
            $uquery->execute();
            setcookie("userid", $cookieh, time() + 2592000, "/");

        }

    }

}

function isAnyoneLive() {

    global $con;

    try {

        $squery = $con->query("SELECT `streams`.`title` FROM `streams`");

        while ($srow = $squery->fetch()) {

            if (vf($srow["title"])) {

                return true;

            }

        }

    } catch (PDOException $e) {

        echo "An error occurred while trying to fetch the streams.";

    }

    return false;

}
