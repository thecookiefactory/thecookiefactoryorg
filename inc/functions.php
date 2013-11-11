<?php

if (!isset($r_c)) header("Location: /notfound.php");

require_once str_repeat("../", $r_c) . "inc/config.php";

$con = new PDO("mysql:host=" . $config["db"]["host"] . ";dbname=" . $config["db"]["dbname"] . ";charset=utf8", $config["db"]["username"], $config["db"]["password"]);
$con->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
$con->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function strip($x) {

    global $con;

    $x = trim($x);
    $x = htmlspecialchars($x, ENT_QUOTES, "UTF-8");
    return $x;

}

function tformat($x) {

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
        $cq->bindValue("cv", hash("sha256", $cv),  PDO::PARAM_STR);
        $cq->execute();

        $cr = $cq->fetch();

        if ($cq->rowCount() == 1) {

            $_SESSION["userid"] = $cr["id"];

            $cookieh = cookieh();
            $uq = $con->prepare("UPDATE `users` SET `users`.`cookieh` = :cookieh WHERE `users`.`id` = :id");
            $uq->bindValue("cookieh", hash("sha256", $cookieh), PDO::PARAM_STR);
            $uq->bindValue("id", $cr["id"], PDO::PARAM_INT);
            $uq->execute();
            setcookie("userid", $cookieh, time() + 2592000, "/");

        }

    }

}

function getname($id, $span = false) {

    global $con;

    $nq = $con->prepare("SELECT `users`.`name`, `users`.`admin` FROM `users` WHERE `users`.`id` = :id");
    $nq->bindValue("id", $id, PDO::PARAM_INT);
    $nq->execute();

    $nr = $nq->fetch();

    if ($nr["admin"] == 0 || $span == false) {

        return $nr["name"];

    } else if (isset($span) && $span == true) {

        return "<span class='admin-name'>".$nr["name"]."</span>";

    }

}

function displaydate($x) {

    $x = strtotime($x);
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

    } else if ($diff < 2*60*60*24) {

        return "a day ago";

    } else {

        return ((int)($diff/(60*60*24)))." days ago";

    }

}

function cookieh() {

    return str_shuffle(hash("sha256", microtime()));

}

function register($username) {

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

    $cookieh = cookieh();

    //registering the user and redirecting to the login form
    $query = $con->prepare("INSERT INTO `users` VALUES(DEFAULT, :username, :steamid, 0, :cookieh, now(), '')");
    $query->bindValue("username", $username, PDO::PARAM_STR);
    $query->bindValue("steamid", $_SESSION["steamid"], PDO::PARAM_INT);
    $query->bindValue("cookieh", hash("sha256", $cookieh), PDO::PARAM_STR);
    $query->execute();

    $id = $con->lastInsertId();

    $_SESSION["userid"] = $id;
    setcookie("userid", $cookieh, time() + 2592000, "/");

    if (isset($_SESSION["lp"])) {

        header("Location: /".$_SESSION["lp"]);

    } else {

        header("Location: /news");

    }

}

function login() {

    global $con;
    global $config;
    global $user;

    if (!isset($OpenID)) {

        $OpenID = new LightOpenID($config["domain"]);

    }

    if (!$OpenID->mode) {

        if (isset($_GET["p"]) && $_GET["p"] == "login") {

            $OpenID->identity = "http://steamcommunity.com/openid";
            header("Location: {$OpenID->authUrl()}");

        }

        if (!isset($_SESSION["userid"])) {

            echo "<a class='menu-item' href='/login'><span class='login-text faux-link'>sign in via steam</span><img class='login-button login-button-image' src='http://cdn.steamcommunity.com/public/images/signinthroughsteam/sits_small.png' alt='login steam button'></a>";

        }

    } elseif ($OpenID->mode == "cancel") {

        echo "user canceled auth";

    } else {

        if (!isset($_SESSION["userid"])) {

            $_SESSION["steamauth"] = $OpenID->validate() ? $OpenID->identity : null;
            $_SESSION["steamid"] = str_replace("http://steamcommunity.com/openid/id/", "", $_SESSION["steamauth"]);

            // checking if the user has an account
            $uq = $con->prepare("SELECT `users`.`id` FROM `users` WHERE `users`.`steamid` = :steamid");
            $uq->bindValue("steamid", $_SESSION["steamid"], PDO::PARAM_STR);
            $uq->execute();

            if ($uq->rowCount() == 1) {

                // yes
                $ua = $uq->fetch();

                $_SESSION["userid"] = $ua["id"];

                $cookieh = cookieh();

                $uq = $con->prepare("UPDATE `users` SET `users`.`cookieh` = :cookieh WHERE `users`.`id` = :id");
                $uq->bindValue("cookieh", hash("sha256", $cookieh), PDO::PARAM_STR);
                $uq->bindValue("id", $ua["id"], PDO::PARAM_INT);
                $uq->execute();

                setcookie("userid", $cookieh, time() + 2592000, "/");

                if (isset($_SESSION["lp"])) {

                    header("Location: /".$_SESSION["lp"]);

                } else {

                    header("Location: /news");

                }

            } else {

                // no
                header("Location: /register");

            }

        }

    }

    if (isset($_SESSION["userid"])) {

        echo "<span class='menu-item' class='actionbar-logindata'>logged in as <span class='actionbar-username'> ".getname($_SESSION["userid"])."</span></span>";

        if ($user->isAdmin()) {

            echo "<span class='menu-item'><a href='/admin/index.php' target='_blank'>admin menu</a></span>";

        }

        echo "<span class='menu-item'><a href='/logout'>log out</a></span>";

    }

    if (isset($_GET["p"]) && $_GET["p"] == "logout") {

        setcookie("userid", "", time() - 100000);
        unset($_SESSION["steamauth"]);
        unset($_SESSION["steamid"]);
        unset($_SESSION["userid"]);

        if (isset($_SESSION["lp"])) {

            header("Location: /".$_SESSION["lp"]);

        } else {

            header("Location: /news");

        }

    }

}

?>
