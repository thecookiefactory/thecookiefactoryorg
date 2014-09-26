<?php

session_start();

$r_c = 1;
require_once "../inc/functions.php";
require_once "../classes/user.class.php";

$user = new user((isset($_SESSION["userid"]) ? $_SESSION["userid"] : null));

if (!$user->isAdmin()) die("403");

$twig = twigInit();

if (isset($_POST["submit"])) {

    $twitchname = strip($_POST["twitchname"]);
    $description = strip($_POST["description"], true);

    if (isset($_POST["active"]) && $_POST["active"] == "on") {

        try {

            $checkStream = $con->prepare("SELECT `streams`.`id` FROM `streams` WHERE `streams`.`authorid` = :userid");
            $checkStream->bindValue("userid", $user->getId(), PDO::PARAM_INT);
            $checkStream->execute();

        } catch (PDOException $e) {

            die("Failed to fetch your stream.");

        }

        if ($checkStream->rowCount() == 0) {

            try {

                $insertStream = $con->prepare("INSERT INTO `streams` VALUES(DEFAULT, DEFAULT, '', :userid)");
                $insertStream ->bindValue("userid", $user->getId(), PDO::PARAM_INT);
                $insertStream->execute();

            } catch (PDOException $e) {

                die("Failed to create your stream.");

            }

        }

        try {

            $updateStream = $con->prepare("UPDATE `streams` SET `streams`.`text` = :text WHERE `streams`.`authorid` = :userid");
            $updateStream->bindValue("text", $description, PDO::PARAM_STR);
            $updateStream->bindValue("userid", $user->getId(), PDO::PARAM_INT);
            $updateStream->execute();

        } catch (PDOException $e) {

            die("Failed to update your stream.");

        }

        try {

            $updateUser = $con->prepare("UPDATE `users` SET `users`.`twitchname` = :twitchname WHERE `users`.`id` = :userid");
            $updateUser->bindValue("twitchname", $twitchname, PDO::PARAM_STR);
            $updateUser->bindValue("userid", $user->getId(), PDO::PARAM_INT);
            $updateUser->execute();

        } catch (PDOException $e) {

            die("Failed to update your twitch name.");

        }

        $status = "update";

    } else {

        try {

            $deleteStream = $con->prepare("DELETE FROM `streams` WHERE `streams`.`authorid` = :userid");
            $deleteStream->bindValue("userid", $user->getId(), PDO::PARAM_INT);
            $deleteStream->execute();

        } catch (PDOException $e) {

            die("Failed to delete your stream.");

        }

        $status = "delete";

    }

} else {

    try {

        $selectText = $con->prepare("SELECT `streams`.`text` FROM `streams` WHERE `streams`.`authorid` = :userid");
        $selectText ->bindValue("userid", $user->getId(), PDO::PARAM_INT);
        $selectText->execute();

        $streamText = $selectText->fetch();

    } catch (PDOException $e) {

        die("Failed to fetch your stream's description.");

    }

    try {

        $selectTwitchName = $con->prepare("SELECT `users`.`twitchname` FROM `users` WHERE `users`.`id` = :userid");
        $selectTwitchName ->bindValue("userid", $user->getId(), PDO::PARAM_INT);
        $selectTwitchName->execute();

        $userTwitchName = $selectTwitchName->fetch();

    } catch (PDOException $e) {

        die("Failed to fetch your twitch name.");

    }

    $streamData = array("twitchname" => $userTwitchName["twitchname"], "text" => $streamText["text"]);

    $status = "form";

}

echo $twig->render("admin/stream.html", array("status" => $status, "data" => (isset($streamData) ? $streamData : 0)));
