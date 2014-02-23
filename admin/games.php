<?php

session_start();

$r_c = 1;
require_once "../inc/functions.php";
require_once "../inc/classes/game.class.php";
require_once "../inc/classes/user.class.php";

$user = new user((isset($_SESSION["userid"]) ? $_SESSION["userid"] : null));

if (!$user->isAdmin()) die("403");

$twig = twigInit();

$query = $con->query("SELECT * FROM `games`");

if (isset($_POST["update"])) {

    while ($r = $query->fetch()) {

        $id = $r["id"];
        $name = strip($_POST[$id."name"]);
        $steamid = strip($_POST[$id."steamid"]);

        if ($name == "" && $steamid == "") {

            $dq = $con->prepare("DELETE FROM `games` WHERE `games`.`id` = :id");
            $dq->bindValue("id", $id, PDO::PARAM_INT);
            $dq->execute();

        } else {

            if (!vf($steamid)) {

                $uq = $con->prepare("UPDATE `games` SET `games`.`name` = :name, `games`.`steamid`= NULL WHERE `games`.`id` = :id");
                $uq->bindValue("name", $name, PDO::PARAM_STR);
                $uq->bindValue("id", $r["id"], PDO::PARAM_INT);
                $uq->execute();

            } else {

                $uq = $con->prepare("UPDATE `games` SET `games`.`name` = :name, `games`.`steamid`= :steamid WHERE `games`.`id` = :id");
                $uq->bindValue("name", $name, PDO::PARAM_STR);
                $uq->bindValue("steamid", $steamid, PDO::PARAM_INT);
                $uq->bindValue("id", $r["id"], PDO::PARAM_INT);
                $uq->execute();

            }

        }

    }

}

if (isset($_POST["addnew"])) {

    $name = strip($_POST["name"]);
    $steamid = strip($_POST["steamid"]);

    if (!vf($steamid)) {

        $iq = $con->prepare("INSERT INTO `games` VALUES(DEFAULT, :name, DEFAULT, DEFAULT)");
        $iq->bindValue("name", $name, PDO::PARAM_STR);
        $iq->execute();

    } else {

        $iq = $con->prepare("INSERT INTO `games` VALUES(DEFAULT, :name, :steamid, DEFAULT)");
        $iq->bindValue("name", $name, PDO::PARAM_STR);
        $iq->bindValue("steamid", $steamid, PDO::PARAM_INT);
        $iq->execute();

    }

}

$query = $con->query("SELECT * FROM `games`");

$games = array();

while ($r = $query->fetch()) {

    $game = new game($r["id"]);
    $games[] = $game->returnArray();
    
}

echo $twig->render("admin/games.html", array("games" => $games));
