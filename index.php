<?php

ob_start("ob_gzhandler");

session_start();

$r_c = 0;
require_once "inc/functions.php";
require_once "inc/classes/master.class.php";
require_once "inc/classes/user.class.php";

$twig = twigInit();

use Aptoma\Twig\Extension\MarkdownExtension;
use Aptoma\Twig\Extension\MarkdownEngine;

$engine = new MarkdownEngine\MichelfMarkdownEngine();

$twig->addExtension(new MarkdownExtension($engine));

cookieCheck();

$user = new user((isset($_SESSION["userid"]) ? $_SESSION["userid"] : null));

include_once("inc/analyticstracking.php");

$someoneIsLive = isAnyoneLive();

try {

    $squery = $con->query("SELECT `custompages`.`title`, `custompages`.`stringid` FROM `custompages` WHERE BIN(`custompages`.`live`) = 1");

    $pages = array();

    while ($srow = $squery->fetch()){

        $pages[] = array("title" => $srow["title"], "stringid" => $srow["stringid"]);
        $pageids[] = $srow["stringid"];

    }

} catch (PDOException $e) {

    echo "An error occurred while trying to fetch the custom pages.";

}

$loginReturn = $user->login();

echo $twig->render("index-top.html", array("canonical" => canonical(), "pages" => $pages, "someoneislive" => $someoneIsLive, "loginreturn" => $loginReturn));

if (isset($_GET["p"]) && vf($_GET["p"])) {

    $p = strip($_GET["p"]);

    if (file_exists("inc/" . $p . ".php") && $p != "functions" && $p != "config") {

        require_once "inc/" . $p . ".php";

    } else if (in_array($p, $pageids)) {

        require_once "inc/custom.php";

    } else if ($p != "login" && $p != "logout") {

        header("Location: /notfound.php");

    }

} else {

    require_once "inc/maps.php";

}

echo $twig->render("index-bottom.html");

function cookieCheck() {

    global $con;

    if (isset($_COOKIE["userid"]) && !isset($_SESSION["userid"])) {

        $cv = $_COOKIE["userid"];

        try {

            $squery = $con->prepare("SELECT `users`.`id` FROM `users` WHERE `users`.`cookieh` = :cv");
            $squery->bindValue("cv", hash("sha256", $cv),  PDO::PARAM_STR);
            $squery->execute();

        } catch(PDOException $e) {

            echo "Failed to fetch cookie info.";

        }

        if ($squery->rowCount() == 1) {

            $srow = $squery->fetch();

            $_SESSION["userid"] = $srow["id"];

            $cookieh = cookieh();

            try {

                $uquery = $con->prepare("UPDATE `users` SET `users`.`cookieh` = :cookieh WHERE `users`.`id` = :id");
                $uquery->bindValue("cookieh", hash("sha256", $cookieh), PDO::PARAM_STR);
                $uquery->bindValue("id", $srow["id"], PDO::PARAM_INT);
                $uquery->execute();

            } catch (PDOException $e) {

                echo "Failed to update cookie info.";

            }

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

ob_end_flush();
