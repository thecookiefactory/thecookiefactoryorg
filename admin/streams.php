<?php

session_start();

$r_c = 1;
require_once "../inc/functions.php";
require_once "../inc/classes/user.class.php";

$user = new user((isset($_SESSION["userid"]) ? $_SESSION["userid"] : null));

if (!$user->isAdmin()) die("403");

$twig = twigInit();

if (isset($_POST["submit"])) {

    $twitchname = strip($_POST["twitchname"]);
    $desc = strip($_POST["description"]);

    if (isset($_POST["active"]) && $_POST["active"] == "on") {

            $sq = $con->prepare("SELECT `streams`.`id` FROM `streams` WHERE `streams`.`authorid` = :userid");
            $sq->bindValue("userid", $user->getId(), PDO::PARAM_INT);
            $sq->execute();

            if ($sq->rowCount() == 0) {

                $cq = $con->prepare("INSERT INTO `streams` VALUES(DEFAULT, DEFAULT, '', :userid)");
                $cq ->bindValue("userid", $user->getId(), PDO::PARAM_INT);
                $cq->execute();

            }

        $uq = $con->prepare("UPDATE `streams` SET `streams`.`text` = :text WHERE `streams`.`authorid` = :userid");
        $uq->bindValue("text", $desc, PDO::PARAM_STR);
        $uq->bindValue("userid", $user->getId(), PDO::PARAM_INT);
        $uq->execute();

        $uq = $con->prepare("UPDATE `users` SET `users`.`twitchname` = :twitchname WHERE `users`.`id` = :userid");
        $uq->bindValue("twitchname", $twitchname, PDO::PARAM_STR);
        $uq->bindValue("userid", $user->getId(), PDO::PARAM_INT);
        $uq->execute();

        $status = "update";

    } else {

        $dq = $con->prepare("DELETE FROM `streams` WHERE `streams`.`authorid` = :userid");
        $dq->bindValue("userid", $user->getId(), PDO::PARAM_INT);
        $dq->execute();

        $status = "delete";

    }

} else {

    $sq = $con->prepare("SELECT * FROM `streams` WHERE `streams`.`authorid` = :userid");
    $sq ->bindValue("userid", $user->getId(), PDO::PARAM_INT);
    $sq->execute();

    $sr = $sq->fetch();

    $uq = $con->prepare("SELECT * FROM `users` WHERE `users`.`id` = :userid");
    $uq ->bindValue("userid", $user->getId(), PDO::PARAM_INT);
    $uq->execute();

    $ur = $uq->fetch();

    $data = array("twitchname" => $ur["twitchname"], "text" => $sr["text"]);

    $status = "form";

}

echo $twig->render("admin/stream.html", array("status" => $status, "data" => (isset($data) ? $data : 0)));
