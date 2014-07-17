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

                    $deleteGame = $con->prepare("DELETE FROM `games` WHERE `games`.`id` = :id");
                    $deleteGame->bindValue("id", $id, PDO::PARAM_INT);
                    $deleteGame->execute();

                } catch (PDOException $e) {

                    die("Failed to delete games.");

                }

            } else {

                if (!vf($steamid)) {

                    try {

                        $updateGame = $con->prepare("UPDATE `games` SET `games`.`name` = :name, `games`.`steamid`= NULL WHERE `games`.`id` = :id");
                        $updateGame->bindValue("name", $name, PDO::PARAM_STR);
                        $updateGame->bindValue("id", $gameData["id"], PDO::PARAM_INT);
                        $updateGame->execute();

                    } catch (PDOException $e) {

                        die("Failed to update games.");

                    }

                } else {

                    try {

                        $updateGame = $con->prepare("UPDATE `games` SET `games`.`name` = :name, `games`.`steamid`= :steamid WHERE `games`.`id` = :id");
                        $updateGame->bindValue("name", $name, PDO::PARAM_STR);
                        $updateGame->bindValue("steamid", $steamid, PDO::PARAM_INT);
                        $updateGame->bindValue("id", $gameData["id"], PDO::PARAM_INT);
                        $updateGame->execute();

                    } catch (PDOException $e) {

                        die("Failed to update games.");

                    }

                }

            }

        }

    }

} catch (PDOException $e) {

    die("Failed to fetch games.");

}

if (isset($_POST["addnew"])) {

    $name = strip($_POST["name"]);
    $steamid = strip($_POST["steamid"]);

    if (!vf($steamid)) {

        try {

            $insertGame = $con->prepare("INSERT INTO `games` VALUES(DEFAULT, :name, DEFAULT, DEFAULT)");
            $insertGame->bindValue("name", $name, PDO::PARAM_STR);
            $insertGame->execute();

        } catch (PDOException $e) {

            die("Failed to add new game.");

        }

    } else {

        try {

            $insertGame = $con->prepare("INSERT INTO `games` VALUES(DEFAULT, :name, :steamid, DEFAULT)");
            $insertGame->bindValue("name", $name, PDO::PARAM_STR);
            $insertGame->bindValue("steamid", $steamid, PDO::PARAM_INT);
            $insertGame->execute();

        } catch (PDOException $e) {

            die("Failed to add new game.");

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
