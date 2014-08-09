<?php

session_start();

$r_c = 1;
require_once "../inc/functions.php";
require_once "../classes/game.class.php";
require_once "../classes/user.class.php";

$user = new user((isset($_SESSION["userid"]) ? $_SESSION["userid"] : null));

if (!$user->isAdmin()) die("403");

$twig = twigInit();

if (isset($_POST["submit"])) {

    $description = strip($_POST["description"]);

    try {

        $updatedesc = $con->prepare("UPDATE `about` SET `about`.`description` = :description WHERE `about`.`userid` = :id");
        $updatedesc->bindValue("description", $description, PDO::PARAM_STR);
        $updatedesc->bindValue("id", $user->getId(), PDO::PARAM_INT);
        $updatedesc->execute();

    } catch (PDOException $e) {

        die("Failed to update your description.");

    }

    $linkcount = 0;

    if (vf($_POST["website"])) {

        $website = strip($_POST["website"]);

        $linkcount++;

    } else {

        $website = "";

    }

    if (vf($_POST["email"])) {

        $email = strip($_POST["email"]);

        $linkcount++;

    } else {

        $email = "";

    }

    if (vf($_POST["github"])) {

        $github = strip($_POST["github"]);

        $linkcount++;

    } else {

        $github = "";

    }

    if (vf($_POST["twitter"])) {

        $twitter = strip($_POST["twitter"]);

        $linkcount++;

    } else {

        $twitter = "";

    }

    if (vf($_POST["twitch"])) {

        $twitch = strip($_POST["twitch"]);

        $linkcount++;

    } else {

        $twitch = "";

    }

    if (vf($_POST["youtube"])) {

        $youtube = strip($_POST["youtube"]);

        $linkcount++;

    } else {

        $youtube = "";

    }

    if (vf($_POST["steam"])) {

        $steam = strip($_POST["steam"]);

        $linkcount++;

    } else {

        $steam = "";

    }

    if (vf($_POST["reddit"])) {

        $reddit = strip($_POST["reddit"]);

        $linkcount++;

    } else {

        $reddit = "";

    }

    if ($linkcount > 6) {

        $status = "toomuch";

    } else {

        try {

            $updatelinks = $con->prepare("UPDATE `about`
                                         SET `about`.`website` = :website,
                                             `about`.`email` = :email,
                                             `about`.`github` = :github,
                                             `about`.`twitter` = :twitter,
                                             `about`.`twitch` = :twitch,
                                             `about`.`youtube` = :youtube,
                                             `about`.`steam` = :steam,
                                             `about`.`reddit` = :reddit
                                         WHERE `about`.`userid` = :id");
            $updatelinks->bindValue("website", $website, PDO::PARAM_STR);
            $updatelinks->bindValue("email", $email, PDO::PARAM_STR);
            $updatelinks->bindValue("github", $github, PDO::PARAM_STR);
            $updatelinks->bindValue("twitter", $twitter, PDO::PARAM_STR);
            $updatelinks->bindValue("twitch", $twitch, PDO::PARAM_STR);
            $updatelinks->bindValue("youtube", $youtube, PDO::PARAM_STR);
            $updatelinks->bindValue("steam", $steam, PDO::PARAM_STR);
            $updatelinks->bindValue("reddit", $reddit, PDO::PARAM_STR);
            $updatelinks->bindValue("id", $user->getId(), PDO::PARAM_INT);
            $updatelinks->execute();

            $status = "success";

        } catch (PDOException $e) {

            die("Failed to update your links.");

        }

    }

}

if (isset($_POST["submitdesc"])) {

    try {

        $checkquery = $con->prepare("SELECT `about`.`id` FROM `about` WHERE `about`.`userid` = 1");
        $checkquery->execute();

    } catch (PDOException $e) {

        die("Failed to fetch the previous group description.");

    }

    $description = strip($_POST["description"]);

    if ($checkquery->rowCount() == 0) {

        try {

            $insertdesc = $con->prepare("INSERT INTO `about` VALUES(DEFAULT, 1, '', '', :description, DEFAULT, DEFAULT, DEFAULT, DEFAULT, DEFAULT, DEFAULT, DEFAULT, DEFAULT)");
            $insertdesc->bindValue("description", $description, PDO::PARAM_STR);
            $insertdesc->execute();

            $status = "descsuccess";

        } catch (PDOException $e) {

            die("Failed to insert the group description.");

        }

    } else {

        try {

            $updatedesc = $con->prepare("UPDATE `about` SET `about`.`description` = :description WHERE `about`.`userid` = 1");
            $updatedesc->bindValue("description", $description, PDO::PARAM_STR);
            $updatedesc->execute();

            $status = "descsuccess";

        } catch (PDOException $e) {

            die("Failed to update the group description.");

        }

    }

}

try {

    $selectdata = $con->prepare("SELECT * FROM `about` WHERE `about`.`userid` = :id");
    $selectdata->bindValue("id", $user->getId(), PDO::PARAM_INT);
    $selectdata->execute();

    $aboutdata = $selectdata->fetch();

} catch (PDOException $e) {

    die("Failed to fetch your data.");

}

try {

    $selectdesc = $con->query("SELECT `about`.`description` FROM `about` WHERE `about`.`userid` = 1");
    $desc = $selectdesc->fetch();

    $aboutdata["groupdesc"] = $desc["description"];

} catch (PDOException $e) {

    die("Failed to fetch the group description.");

}

if (isset($status)) {

    $aboutdata["status"] = $status;

}

echo $twig->render("admin/about.html", $aboutdata);
