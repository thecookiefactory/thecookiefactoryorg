<?php

if (!isset($r_c)) header("Location: notfound.php");

require $_SERVER['DOCUMENT_ROOT']."/inc/config.php";

$con = new PDO("mysql:host=" . $config["db"]["host"] . ";dbname=" . $config["db"]["dbname"] . ";charset=utf8", $config["db"]["username"], $config["db"]["password"]);
$con->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

function strip($x) {

    global $con;

    $x = trim($x);
    $x = htmlentities($x, ENT_QUOTES, "UTF-8");
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

        $cq = $con->prepare("SELECT `users`.`id` FROM `users` WHERE `users`.`cookieh` = :cv");
        $cq->bindValue("cv", $cv,  PDO::PARAM_STR);
        $cq->execute();

        $cr = $cq->fetch(PDO::FETCH_ASSOC);

        if ($cq->rowCount() == 1) {

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

        $cq = $con->prepare("SELECT `users`.`admin` FROM `users` WHERE `users`.`id` = :x");
        $cq->bindValue("x", $x, PDO::PARAM_INT);
        $cq->execute();

        $cr = $cq->fetch(PDO::FETCH_ASSOC);

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

    $nq = $con->prepare("SELECT `users`.`name`, `users`.`admin` FROM `users` WHERE `users`.`id` = :id");
    $nq->bindValue("id", $id, PDO::PARAM_INT);
    $nq->execute();

    $nr = $nq->fetch(PDO::FETCH_ASSOC);

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

    $sq = $con->prepare("SELECT `streams`.`title` FROM `streams` WHERE `streams`.`twitch` = :x");
    $sq->bindValue("x", $x, PDO::PARAM_STR);
    $sq->execute();

    $sr = $sq->fetch(PDO::FETCH_ASSOC);

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
    $cq = $con->prepare("SELECT `users`.`id` FROM `users` WHERE `users`.`name` = :username");
    $cq->bindValue("username", $username, PDO::PARAM_STR);
    $cq->execute();

    if ($cq->rowCount() != 0) {

        echo "We're sorry, that user already exists!";
        return;

    }

    $date = time();

    //$cookieh = cookieh();

    //registering the user and redirecting to the login form
    $query = $con->prepare("INSERT INTO `users` VALUES('', :username, :steamid, 0, '', :date)");
    $query->bindValue("username", $username, PDO::PARAM_STR);
    $query->bindValue("steamid", $_SESSION["steamid"], PDO::PARAM_INT);
    $query->bindValue("date", $date, PDO::PARAM_INT);
    $query->execute();

    $id = $con->lastInsertId();

    $_SESSION["userid"] = $id;
    //setcookie("userid", $cookieh, time() + 2592000);

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
    global $config;

    if (!isset($OpenID)) {

        $OpenID = new LightOpenID($config["domain"]);

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

            //$profile = file_get_contents("http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=".$config["apikey"]."&steamids=".$_SESSION["steamid"]."&format=json");
            //$buffer = fopen("cache/{$_SESSION["steamid"]}.json", "w");
            //fwrite($buffer, $profile);
            //fclose($buffer);

            // checking if the user has an account
            $uq = $con->prepare("SELECT `users`.`id` FROM `users` WHERE `users`.`steamid` = :steamid");
            $uq->bindValue("steamid", $_SESSION["steamid"], PDO::PARAM_INT);
            $uq->execute();

            if ($uq->rowCount() == 1) {

                // yes
                $ua = $uq->fetch(PDO::FETCH_ASSOC);

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
