<?php

session_start();

$r_c = 1;
require_once "../inc/functions.php";
require_once "../inc/classes/game.class.php";
require_once "../inc/classes/user.class.php";

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

        die("Failed to execute the query.");

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

            $updatedesc = $con->prepare("UPDATE `about`
                                         SET `about`.`website` = :website,
                                             `about`.`email` = :email,
                                             `about`.`github` = :github,
                                             `about`.`twitter` = :twitter,
                                             `about`.`twitch` = :twitch,
                                             `about`.`youtube` = :youtube,
                                             `about`.`steam` = :steam,
                                             `about`.`reddit` = :reddit
                                         WHERE `about`.`userid` = :id");
            $updatedesc->bindValue("website", $website, PDO::PARAM_STR);
            $updatedesc->bindValue("email", $email, PDO::PARAM_STR);
            $updatedesc->bindValue("github", $github, PDO::PARAM_STR);
            $updatedesc->bindValue("twitter", $twitter, PDO::PARAM_STR);
            $updatedesc->bindValue("twitch", $twitch, PDO::PARAM_STR);
            $updatedesc->bindValue("youtube", $youtube, PDO::PARAM_STR);
            $updatedesc->bindValue("steam", $steam, PDO::PARAM_STR);
            $updatedesc->bindValue("reddit", $reddit, PDO::PARAM_STR);
            $updatedesc->bindValue("id", $user->getId(), PDO::PARAM_INT);
            $updatedesc->execute();

            $status = "success";

        } catch (PDOException $e) {

            die("Failed to execute the query.");

        }

    }

}

try {

    $selectdata = $con->prepare("SELECT * FROM `about` WHERE `about`.`userid` = :id");
    $selectdata->bindValue("id", $user->getId(), PDO::PARAM_INT);
    $selectdata->execute();

    $aboutdata = $selectdata->fetch();

} catch (PDOException $e) {

    die("Failed to execute the query.");

}

if (isset($status)) {

    $aboutdata["status"] = $status;

}

echo $twig->render("admin/about.html", $aboutdata);
