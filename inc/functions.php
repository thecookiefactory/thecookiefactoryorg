<?php

if (!isset($r_c)) header("Location: notfound.php");

if ($r_c == 42)
    require "inc/config.php";

if ($r_c == 41)
    require "../inc/config.php";


function strip($x) {

    global $con;

    $x = trim($x);
    $x = htmlentities($x, ENT_QUOTES, "UTF-8");
    $x = mysqli_real_escape_string($con, $x);
    return $x;

}

function tformat($x) {

    //return preg_replace('/(<br[^>]*>\s*){3,}/', '<br><br>', nl2br($x, false));
    return nl2br($x, false);

}

function vf($x) {

    return (strip($x) != "" && strip($x) != null) ? true : false;

}

function ccookies() {

    global $con;

    if (isset($_COOKIE["userid"]) && !isset($_SESSION["userid"])) {

        $cv = $_COOKIE["userid"];

        $cq = mysqli_query($con, "SELECT `id` FROM `users` WHERE `cookieh`='".$cv."'");

        $cr = mysqli_fetch_assoc($cq);

        if (mysqli_num_rows($cq) == 1) {

            $_SESSION["userid"] = $cr["id"];

        }

    }

}

function checkuser() {

    if (isset($_SESSION["userid"])) return true; else return false;

}

function checkadmin() {

    global $con;

    if (isset($_SESSION["userid"])) {

        $x = $_SESSION["userid"];

        $cq = mysqli_query($con, "SELECT `admin` FROM `users` WHERE `id`=".$x);

        $cr = mysqli_fetch_assoc($cq);

        if ($cr["admin"] == 1) {

            return true;

        } else {

            return false;

        }

    } else {

        return false;

    }

}

function getname($id, $span = false) {

    global $con;

    $nq = mysqli_query($con, "SELECT `name`,`admin` FROM `users` WHERE `id`=".$id);
    $nr = mysqli_fetch_assoc($nq);

    if ($nr["admin"] == 0 || $span == false) {

        return $nr["name"];

    } else if (isset($span) && $span == true) {

        return "<span class='admin-name'>".$nr["name"]."</span>";

    }

}

function displaydate($x) {

    return "<time datetime='".date(DATE_W3C, $x)."' title='".date("Y-m-d H:i \C\E\T", $x)."'>".longago($x)."</time>";

}

function longago($x) {

    $diff = time() - $x;

    if ($diff < 10) {

        return "just now";

    } else if ($diff < 60) {

        return $diff." seconds ago";

    } else if ($diff < 120) {

        return "a minute ago";

    } else if ($diff < 60*60) {

        return ((int)($diff/60))." minutes ago";

    } else if ($diff < 60*60*2) {

        return "an hour ago";

    } else if ($diff < 60*60*24) {

        return ((int)($diff/(60*60)))." hours ago";

    } else {

        return ((int)($diff/(60*60*24)))." days ago";

    }

}

function islive($x) {

    global $con;

    $sq = mysqli_query($con, "SELECT `title` FROM `streams` WHERE `twitch`='".$x."'");
    $sr = mysqli_fetch_assoc($sq);

    if (vf($sr["title"])) {

        return true;

    } else {

        return false;

    }

}

function register($username) {

    global $redirect;
    global $con;

    $username = strip($username);

    //checking if the username has valid characters only and is of the specified length
    if (!ctype_alnum($username)) {

        echo "The specified username seems to have invalid characters. Only letters of the English alphabet and numbers are allowed.";
        return;

    }

    if (strlen($username) < 2 || strlen($username) > 10) {

        echo "The username must be between 2 and 10 charaters long.";
        return;

    }

    //checking if that user already exists
    $cq = mysqli_query($con, "SELECT `id` FROM `users` WHERE `name`='".$username."'");

    if (mysqli_num_rows($cq) != 0) {

        echo "We're sorry, that user already exists!";
        return;

    }

    $dt = time();

    $cookieh = cookieh();

    //registering the user and redirecting to the login form
    $query = mysqli_query($con, "INSERT INTO `users` VALUES('','".$username."','".$_SESSION["steamid"]."','0','".$cookieh."','".$dt."')");
    $id = mysqli_insert_id($con);

    $_SESSION["userid"] = $id;
    setcookie("userid", $cookieh, time() + 2592000);

    echo "Successfully registered! You will get redirected in 5 seconds. <a href='?p=news'>Click here if you don't want to wait.</a>";

    if (isset($_SESSION["lp"])) {

        $redirect = $_SESSION["lp"];

    } else {

        $redirect = "news";

    }

}

function login() {

    global $redirect;
    global $con;
    global $apikey;
    global $redirect;
    global $domain;

    if (!isset($OpenID)) {

        $OpenID = new LightOpenID($domain);

    }

    if (!$OpenID->mode) {

        if (isset($_GET["p"]) && $_GET["p"] == "login") {

            $OpenID->identity = "http://steamcommunity.com/openid";
            header("Location: {$OpenID->authUrl()}");

        }

        if (!isset($_SESSION["userid"])) {

            echo "<a class='menu-item' href='?p=login'><span class='login-text faux-link'>sign in via steam</span><img class='login-button login-button-image' src='http://cdn.steamcommunity.com/public/images/signinthroughsteam/sits_small.png' alt='login steam button'></a>";

        }

    } elseif ($OpenID->mode == "cancel") {

        echo "user canceled auth";

    } else {

        if (!isset($_SESSION["userid"])) {

            $_SESSION["steamauth"] = $OpenID->validate() ? $OpenID->identity : null;
            $_SESSION["steamid"] = str_replace("http://steamcommunity.com/openid/id/", "", $_SESSION["steamauth"]);

            //$profile = file_get_contents("http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=".$apikey."&steamids=".$_SESSION["steamid"]."&format=json");
            //$buffer = fopen("cache/{$_SESSION["steamid"]}.json", "w");
            //fwrite($buffer, $profile);
            //fclose($buffer);

            // checking if the user has an account
            $uq = mysqli_query($con, "SELECT `id` FROM `users` WHERE `steamid`='".$_SESSION["steamid"]."'");

            if (mysqli_num_rows($uq) == 1) {

                // yes
                $ua = mysqli_fetch_assoc($uq);

                $_SESSION["userid"] = $ua["id"];
                setcookie("userid", $ua["cookieh"], time() + 2592000);

                if (isset($_SESSION["lp"])) {

                    header("Location: ?p=".$_SESSION["lp"]);

                } else {

                    header("Location: ?p=news");

                }

            } else {

                // no
                header("Location: ?p=register");

            }

        }

    }

    if (isset($_SESSION["userid"])) {

        echo "<span class='menu-item' class='actionbar-logindata'>logged in as <span class='actionbar-username'> ".getname($_SESSION["userid"])."</span></span>";

        if (checkadmin()) {

            echo "<span class='menu-item'><a href='admin' target='_blank'>admin menu</a></span>";

        }

        echo "<span class='menu-item'><a href='?p=logout'>log out</a></span>";

    }

    if (isset($_GET["p"]) && $_GET["p"] == "logout") {

        unset($_SESSION["steamauth"]);
        unset($_SESSION["steamid"]);
        unset($_SESSION["userid"]);
        setcookie("userid", $ua["id"], time() - 100000);

        if (isset($_SESSION["lp"])) {

            header("Location: ?p=".$_SESSION["lp"]);

        } else {

            header("Location: ?p=news");

        }

    }

}

?>
