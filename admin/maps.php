<?php

session_start();

$r_c = 1;
require_once "../inc/functions.php";
require_once "../classes/forumthread.class.php";
require_once "../classes/user.class.php";

$user = new user((isset($_SESSION["userid"]) ? $_SESSION["userid"] : null));

if (!$user->isAdmin()) die("403");

$twig = twigInit();

if (isset($_GET["action"]) && ($_GET["action"] == "edit" || $_GET["action"] == "delete" || $_GET["action"] == "write")) {

    if ($_GET["action"] == "edit") {

        $mode = "edit";

        $status = "edit";

        $id = strip($_GET["id"]);

        try {

            $selectMapId = $con->prepare("SELECT `maps`.`id` FROM `maps` WHERE `maps`.`id` = :id");
            $selectMapId->bindValue("id", $id, PDO::PARAM_INT);
            $selectMapId->execute();

        } catch (PDOException $e) {

            die("Query failed.");

        }

        if (isset($_POST["submit"]) && vf($_POST["name"]) && vf($_POST["game"]) && vf($_POST["text"])) {

            $name = strip($_POST["name"]);
            $game = strip($_POST["game"]);
            $text = strip($_POST["text"], true);
            $download = strip($_POST["download"]);
            $link = strip($_POST["link"]);
            $authorid = strip($_POST["authorid"]);

            try {

                $updateMapData = $con->prepare("UPDATE `maps` SET `maps`.`name` = :name, `maps`.`authorid` = :authorid, `maps`.`gameid` = :game, `maps`.`text` = :text, `maps`.`dl` = :download, `maps`.`link` = :link WHERE `maps`.`id` = :id");
                $updateMapData->bindValue("id", $id, PDO::PARAM_INT);
                $updateMapData->bindValue("name", $name, PDO::PARAM_STR);
                $updateMapData->bindValue("authorid", $authorid, PDO::PARAM_INT);
                $updateMapData->bindValue("game", $game, PDO::PARAM_INT);
                $updateMapData->bindValue("text", $text, PDO::PARAM_STR);
                $updateMapData->bindValue("download", $download, PDO::PARAM_STR);
                $updateMapData->bindValue("link", $link, PDO::PARAM_STR);
                $updateMapData->execute();

            } catch (PDOException $e) {

                die("Query failed.");

            }

            if (isset($_POST["topicname"]) && vf($_POST["topicname"]) && vf($_POST["topiccat"]) && vf($_POST["topictext"])) {

                $authorid = $user->getId();
                $title = strip($_POST["topicname"]);
                $text = strip($_POST["topictext"]);
                $cat = strip($_POST["topiccat"]);

                try {

                    $createThread = $con->prepare("INSERT INTO `forumthreads` VALUES(DEFAULT, :title, :text, :authorid, DEFAULT, DEFAULT, DEFAULT, :cat, :id, DEFAULT, 0)");
                    $createThread->bindValue("authorid", $authorid, PDO::PARAM_INT);
                    $createThread->bindValue("title", $title, PDO::PARAM_STR);
                    $createThread->bindValue("text", $text, PDO::PARAM_STR);
                    $createThread->bindValue("cat", $cat, PDO::PARAM_INT);
                    $createThread->bindValue("id", $id, PDO::PARAM_INT);
                    $createThread->execute();

                } catch (PDOException $e) {

                    die("Query failed.");

                }

                try {

                    $enableComments = $con->prepare("UPDATE `maps` SET `maps`.`comments` = 1 WHERE `maps`.`id` = :id");
                    $enableComments->bindValue("id", $id, PDO::PARAM_INT);
                    $enableComments->execute();

                } catch (PDOException $e) {

                    die("Query failed.");

                }

            }

        }

        if ($selectMapId->rowCount() == 1) {

            try {

                //fetching the current data
                $selectMapData = $con->prepare("SELECT `maps`.`id`, `maps`.`name`, `maps`.`text`, `maps`.`authorid`, `maps`.`dl`, `maps`.`link`, `maps`.`comments`, `maps`.`gameid` FROM `maps` WHERE `maps`.`id` = :id");
                $selectMapData->bindValue("id", $id, PDO::PARAM_INT);
                $selectMapData->execute();

                $mapdata = $selectMapData->fetch();

            } catch (PDOException $e) {

                die("Query failed.");

            }

            try {

                $selectGame = $con->query("SELECT `games`.`id`, `games`.`name` FROM `games` ORDER BY `games`.`id` ASC");

                $games = array();

                while ($gameData = $selectGame->fetch()) {

                    $games[] = array("id" => $gameData["id"], "name" => $gameData["name"]);

                }

            } catch (PDOException $e) {

                die("Query failed.");

            }

            if ($mapdata["comments"] == 0) {

                try {

                    $selectCategories = $con->query("SELECT `forumcategories`.`id`, `forumcategories`.`name` FROM `forumcategories` ORDER BY `forumcategories`.`name` ASC");

                    $forumcategories = array();

                    while ($categoryData = $selectCategories->fetch()) {

                        $forumcategories[] = array("id" => $categoryData["id"], "name" => $categoryData["name"]);

                    }

                } catch (PDOException $e) {

                    die("Query failed.");

                }

            }

        } else {

            $status = "notfound";

        }

    } else if ($_GET["action"] == "delete") {

        $mode = "delete";

        if (isset($_GET["id"]) && is_numeric($_GET["id"])) {

            $id = strip($_GET["id"]);
            $currentid = $id;

            try {

                $selectMapId = $con->prepare("SELECT `maps`.`id` FROM `maps` WHERE `maps`.`id` = :id");
                $selectMapId->bindValue("id", $id, PDO::PARAM_INT);
                $selectMapId->execute();

            } catch (PDOException $e) {

                die("Query failed.");

            }

            if ($selectMapId->rowCount() == 1) {

                if (isset($_POST["delete"])) {

                    try {

                        //deleting images from the gallery
                        $selectPictures = $con->prepare("SELECT `pictures`.`id` FROM `pictures` WHERE `pictures`.`mapid` = :id");
                        $selectPictures->bindValue("id", $id, PDO::PARAM_INT);
                        $selectPictures->execute();

                        while ($pictureData = $selectPictures->fetch()) {

                            $picture = new picture($pictureData["id"]);
                            $picture->delete();

                        }

                    } catch (PDOException $e) {

                        die("Query failed.");

                    }

                    try {

                        //deleting the forum thread
                        $selectThreadData = $con->prepare("SELECT `forumthreads`.`id` FROM `forumthreads` WHERE `forumthreads`.`mapid` = :id");
                        $selectThreadData->bindValue("id", $id, PDO::PARAM_INT);
                        $selectThreadData->execute();

                        $threadData = $selectThreadData->fetch();

                        $mapthread = new forumthread($threadData["id"]);
                        $mapthread->delete("r_c");

                    } catch (PDOException $e) {

                        die("Query failed.");

                    }

                    try {

                        $deleteMap = $con->prepare("DELETE FROM `maps` WHERE `maps`.`id` = :id");
                        $deleteMap->bindValue("id", $id, PDO::PARAM_INT);
                        $deleteMap->execute();

                    } catch (PDOException $e) {

                        die("Query failed.");

                    }

                    $status = "success";

                } else {

                    $status = "confirm";

                }

            } else {

                $status = "notfound";

            }

        } else {

            $status = "notfound";

        }

    } else {

        $mode = "write";

        if (isset($_POST["submit"]) && vf($_POST["name"]) && vf($_POST["game"]) && vf($_POST["text"])) {

            //basic values
            $name = strip($_POST["name"]);
            $author = $user->getId();
            $game = strip($_POST["game"]);
            $text = strip($_POST["text"], true);
            $download = strip($_POST["download"]);

            if (isset($_POST["topicname"]) && vf($_POST["topicname"]) && vf($_POST["topiccat"]) && vf($_POST["topictext"])) {

                $comments = 1;

                $status = "topic";

            } else {

                $comments = 0;

                $status = "no-topic";

            }

            try {

                //inserting the basic data and returning the map id
                $createMap = $con->prepare("INSERT INTO `maps` VALUES(DEFAULT, :name, :text, :author, now(), DEFAULT, :download, :comments, :game, '', 0)");
                $createMap->bindValue("name", $name, PDO::PARAM_STR);
                $createMap->bindValue("text", $text, PDO::PARAM_STR);
                $createMap->bindValue("author", $author, PDO::PARAM_INT);
                $createMap->bindValue("download", $download, PDO::PARAM_STR);
                $createMap->bindValue("comments", $comments, PDO::PARAM_INT);
                $createMap->bindValue("game", $game, PDO::PARAM_INT);
                $createMap->execute();

            } catch (PDOException $e) {

                die("Query failed.");

            }

            $id = $con->lastInsertId();

            if (isset($_POST["topicname"]) && vf($_POST["topicname"]) && vf($_POST["topiccat"]) && vf($_POST["topictext"])) {

                $authorid = $user->getId();
                $title = strip($_POST["topicname"]);
                $text = strip($_POST["topictext"]);
                $cat = strip($_POST["topiccat"]);

                try {

                    $createThread = $con->prepare("INSERT INTO `forumthreads` VALUES(DEFAULT, :title, :text, :authorid, DEFAULT, DEFAULT, DEFAULT, :cat, :id, DEFAULT, 0)");
                    $createThread->bindValue("authorid", $authorid, PDO::PARAM_INT);
                    $createThread->bindValue("title", $title, PDO::PARAM_STR);
                    $createThread->bindValue("text", $text, PDO::PARAM_STR);
                    $createThread->bindValue("cat", $cat, PDO::PARAM_INT);
                    $createThread->bindValue("id", $id, PDO::PARAM_INT);
                    $createThread->execute();

                } catch (PDOException $e) {

                    die("Query failed.");

                }

            }

            $status .= "-success";

        } else {

            $status = "progress";

            try {

                $selectGame = $con->query("SELECT `games`.`id`, `games`.`name` FROM `games` ORDER BY `games`.`id` ASC");

                $games = array();

                while ($gameData = $selectGame->fetch()) {

                    $games[] = array("id" => $gameData["id"], "name" => $gameData["name"]);

                }

            } catch (PDOException $e) {

                die("Query failed.");

            }

            try {

                $selectCategories = $con->query("SELECT `forumcategories`.`id`, `forumcategories`.`name` FROM `forumcategories` ORDER BY `forumcategories`.`name` ASC");

                $forumcategories = array();

                while ($categoryData = $selectCategories->fetch()) {

                    $forumcategories[] = array("id" => $categoryData["id"], "name" => $categoryData["name"]);

                }

            } catch (PDOException $e) {

                die("Query failed.");

            }

        }

    }

} else {

    $mode = "manage";

    try {

        $selectMapData = $con->query("SELECT `maps`.`id`, `maps`.`name` FROM `maps` ORDER BY `maps`.`id` DESC");

        $maps = array();

        while ($mapdata = $selectMapData->fetch()) {

            $maps[] = array("id" => $mapdata["id"], "name" => $mapdata["name"]);

        }

    } catch (PDOException $e) {

        die("Query failed.");

    }

}

echo $twig->render("admin/maps.html", array("mode" => $mode,
                                            "status" => (isset($status) ? $status : null),
                                            "mapdata" => (isset($mapdata) ? $mapdata : null),
                                            "currentid" => (isset($currentid) ? $currentid : null),
                                            "maps" => (isset($maps) ? $maps : null),
                                            "games" => (isset($games) ? $games : null),
                                            "forumcategories" => (isset($forumcategories) ? $forumcategories : null),
                                            ));
