<?php

session_start();

$r_c = 1;
require_once "../inc/functions.php";
require_once "../inc/classes/game.class.php";
require_once "../inc/classes/user.class.php";

$user = new user((isset($_SESSION["userid"]) ? $_SESSION["userid"] : null));

if (!$user->isAdmin()) die("403");

$twig = twigInit();

try {

    $selectGames = $con->query("SELECT `games`.`id` FROM `games`");

    if (isset($_POST["update"])) {

        while ($gameData = $selectGames->fetch()) {

            $id = $gameData["id"];
            $name = strip($_POST[$id."name"]);
            $steamid = strip($_POST[$id."steamid"]);

            if ($name == "" && $steamid == "") {

                try {

                    $dq = $con->prepare("DELETE FROM `games` WHERE `games`.`id` = :id");
                    $dq->bindValue("id", $id, PDO::PARAM_INT);
                    $dq->execute();

                } catch (PDOException $e) {

                    die("Query failed.");

                }

            } else {

                if (!vf($steamid)) {

                    try {

                        $uq = $con->prepare("UPDATE `games` SET `games`.`name` = :name, `games`.`steamid`= NULL WHERE `games`.`id` = :id");
                        $uq->bindValue("name", $name, PDO::PARAM_STR);
                        $uq->bindValue("id", $gameData["id"], PDO::PARAM_INT);
                        $uq->execute();

                    } catch (PDOException $e) {

                        die("Query failed.");

                    }

                } else {

                    try {

                        $uq = $con->prepare("UPDATE `games` SET `games`.`name` = :name, `games`.`steamid`= :steamid WHERE `games`.`id` = :id");
                        $uq->bindValue("name", $name, PDO::PARAM_STR);
                        $uq->bindValue("steamid", $steamid, PDO::PARAM_INT);
                        $uq->bindValue("id", $gameData["id"], PDO::PARAM_INT);
                        $uq->execute();

                    } catch (PDOException $e) {

                        die("Query failed.");

                    }

                }

            }

        }

    }

} catch (PDOException $e) {

    die("Query failed.");

}

if (isset($_POST["addnew"])) {

    $name = strip($_POST["name"]);
    $steamid = strip($_POST["steamid"]);

    if (!vf($steamid)) {

        try {

            $iq = $con->prepare("INSERT INTO `games` VALUES(DEFAULT, :name, DEFAULT, DEFAULT)");
            $iq->bindValue("name", $name, PDO::PARAM_STR);
            $iq->execute();

        } catch (PDOException $e) {

            die("Query failed.");

        }

    } else {

        try {

            $iq = $con->prepare("INSERT INTO `games` VALUES(DEFAULT, :name, :steamid, DEFAULT)");
            $iq->bindValue("name", $name, PDO::PARAM_STR);
            $iq->bindValue("steamid", $steamid, PDO::PARAM_INT);
            $iq->execute();

        } catch (PDOException $e) {

            die("Query failed.");

        }

    }

}

try {

    $selectGames = $con->query("SELECT `games`.`id` FROM `games`");

    $games = array();

    while ($gameData = $selectGames->fetch()) {

        $game = new game($gameData["id"]);
        $games[] = $game->returnArray();

    }

} catch (PDOException $e) {

    die("Query failed.");

}

echo $twig->render("admin/games.html", array("games" => $games));
