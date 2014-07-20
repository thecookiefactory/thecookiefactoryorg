<?php

session_start();

$r_c = 1;
require_once "../inc/functions.php";
require_once "../inc/classes/forumthread.class.php";
require_once "../inc/classes/user.class.php";

$user = new user((isset($_SESSION["userid"]) ? $_SESSION["userid"] : null));

if (!$user->isAdmin()) die("403");

$twig = twigInit();

if (isset($_GET["action"]) && ($_GET["action"] == "edit" || $_GET["action"] == "delete" || $_GET["action"] == "write")) {

    if ($_GET["action"] == "edit") {

        $mode = "edit";

        $status = "edit";

        $id = strip($_GET["id"]);

        try {

            $eq = $con->prepare("SELECT `maps`.`id` FROM `maps` WHERE `maps`.`id` = :id");
            $eq->bindValue("id", $id, PDO::PARAM_INT);
            $eq->execute();

        } catch (PDOException $e) {

            die("Query failed.");

        }

        if (isset($_POST["submit"]) && vf($_POST["name"]) && vf($_POST["game"]) && vf($_POST["text"])) {

            $name = strip($_POST["name"]);
            $game = strip($_POST["game"]);
            $text = strip($_POST["text"]);
            $download = strip($_POST["download"]);
            $link = strip($_POST["link"]);

            try {

                $uq = $con->prepare("UPDATE `maps` SET `maps`.`name` = :name, `maps`.`gameid` = :game, `maps`.`text` = :text, `maps`.`dl` = :download, `maps`.`link` = :link WHERE `maps`.`id` = :id");
                $uq->bindValue("id", $id, PDO::PARAM_INT);
                $uq->bindValue("name", $name, PDO::PARAM_STR);
                $uq->bindValue("game", $game, PDO::PARAM_INT);
                $uq->bindValue("text", $text, PDO::PARAM_STR);
                $uq->bindValue("download", $download, PDO::PARAM_STR);
                $uq->bindValue("link", $link, PDO::PARAM_STR);
                $uq->execute();

            } catch (PDOException $e) {

                die("Query failed.");

            }

            if (isset($_POST["topicname"]) && vf($_POST["topicname"]) && vf($_POST["topiccat"]) && vf($_POST["topictext"])) {

                $authorid = $user->getId();
                $title = strip($_POST["topicname"]);
                $text = strip($_POST["topictext"]);
                $cat = strip($_POST["topiccat"]);

                try {

                    $iq = $con->prepare("INSERT INTO `forumthreads` VALUES(DEFAULT, :title, :text, :authorid, DEFAULT, DEFAULT, DEFAULT, :cat, :id, DEFAULT, 0)");
                    $iq->bindValue("authorid", $authorid, PDO::PARAM_INT);
                    $iq->bindValue("title", $title, PDO::PARAM_STR);
                    $iq->bindValue("text", $text, PDO::PARAM_STR);
                    $iq->bindValue("cat", $cat, PDO::PARAM_INT);
                    $iq->bindValue("id", $id, PDO::PARAM_INT);
                    $iq->execute();

                } catch (PDOException $e) {

                    die("Query failed.");

                }

                try {

                    $uq = $con->prepare("UPDATE `maps` SET `maps`.`comments` = 1 WHERE `maps`.`id` = :id");
                    $uq->bindValue("id", $id, PDO::PARAM_INT);
                    $uq->execute();

                } catch (PDOException $e) {

                    die("Query failed.");

                }

            }

        }

        if ($eq->rowCount() == 1) {

            try {

                //fetching the current data
                $eq = $con->prepare("SELECT `maps`.`id`, `maps`.`name`, `maps`.`text`, `maps`.`dl`, `maps`.`link`, `maps`.`comments`, `maps`.`gameid` FROM `maps` WHERE `maps`.`id` = :id");
                $eq->bindValue("id", $id, PDO::PARAM_INT);
                $eq->execute();

                $mr = $eq->fetch();

            } catch (PDOException $e) {

                die("Query failed.");

            }

            $mapdata = array("id" => $mr["id"],
                             "name" => $mr["name"],
                             "text" => $mr["text"],
                             "dl" => $mr["dl"],
                             "link" => $mr["link"],
                             "comments" => $mr["comments"],
                             "gameid" => $mr["gameid"]);

            try {

                $gq = $con->query("SELECT `games`.`id`, `games`.`name` FROM `games` ORDER BY `games`.`id` ASC");

                $games = array();

                while ($gr = $gq->fetch()) {

                    $games[] = array("id" => $gr["id"], "name" => $gr["name"]);

                }

            } catch (PDOException $e) {

                die("Query failed.");

            }

            if ($mr["comments"] == 0) {

                try {

                    $cq = $con->query("SELECT `forumcategories`.`id`, `forumcategories`.`name` FROM `forumcategories` ORDER BY `forumcategories`.`name` ASC");

                    $forumcategories = array();

                    while ($cr = $cq->fetch()) {

                        $forumcategories[] = array("id" => $cr["id"], "name" => $cr["name"]);

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

                $eq = $con->prepare("SELECT `maps`.`id` FROM `maps` WHERE `maps`.`id` = :id");
                $eq->bindValue("id", $id, PDO::PARAM_INT);
                $eq->execute();

            } catch (PDOException $e) {

                die("Query failed.");

            }

            if ($eq->rowCount() == 1) {

                $er = $eq->fetch();

                if (isset($_POST["delete"])) {

                    try {

                        //deleting images from the gallery
                        $gq = $con->prepare("SELECT `pictures`.`id` FROM `pictures` WHERE `pictures`.`mapid` = :id");
                        $gq->bindValue("id", $id, PDO::PARAM_INT);
                        $gq->execute();

                        while ($gr = $gq->fetch()) {

                            $picture = new picture($gr["id"]);
                            $picture->delete();

                        }

                    } catch (PDOException $e) {

                        die("Query failed.");

                    }

                    try {

                        //deleting the forum thread
                        $dq = $con->prepare("SELECT `forumthreads`.`id` FROM `forumthreads` WHERE `forumthreads`.`mapid` = :id");
                        $dq->bindValue("id", $id, PDO::PARAM_INT);
                        $dq->execute();

                        $fi = $dq->fetch();

                        $mapthread = new forumthread($fi["id"]);
                        $mapthread->delete("r_c");

                    } catch (PDOException $e) {

                        die("Query failed.");

                    }

                    try {

                        $dq = $con->prepare("DELETE FROM `maps` WHERE `maps`.`id` = :id");
                        $dq->bindValue("id", $id, PDO::PARAM_INT);
                        $dq->execute();

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
            $text = strip($_POST["text"]);
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
                $iq = $con->prepare("INSERT INTO `maps` VALUES(DEFAULT, :name, :text, :author, now(), DEFAULT, :download, :comments, :game, '', 0)");
                $iq->bindValue("name", $name, PDO::PARAM_STR);
                $iq->bindValue("text", $text, PDO::PARAM_STR);
                $iq->bindValue("author", $author, PDO::PARAM_INT);
                $iq->bindValue("download", $download, PDO::PARAM_STR);
                $iq->bindValue("comments", $comments, PDO::PARAM_INT);
                $iq->bindValue("game", $game, PDO::PARAM_INT);
                $iq->execute();

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

                    $iq = $con->prepare("INSERT INTO `forumthreads` VALUES(DEFAULT, :title, :text, :authorid, DEFAULT, DEFAULT, DEFAULT, :cat, :id, DEFAULT, 0)");
                    $iq->bindValue("authorid", $authorid, PDO::PARAM_INT);
                    $iq->bindValue("title", $title, PDO::PARAM_STR);
                    $iq->bindValue("text", $text, PDO::PARAM_STR);
                    $iq->bindValue("cat", $cat, PDO::PARAM_INT);
                    $iq->bindValue("id", $id, PDO::PARAM_INT);
                    $iq->execute();

                } catch (PDOException $e) {

                    die("Query failed.");

                }

            }

            $status .= "-success";

        } else {

            $status = "progress";

            try {

                $gq = $con->query("SELECT `games`.`id`, `games`.`name` FROM `games` ORDER BY `games`.`id` ASC");

                $games = array();

                while ($gr = $gq->fetch()) {

                    $games[] = array("id" => $gr["id"], "name" => $gr["name"]);

                }

            } catch (PDOException $e) {

                die("Query failed.");

            }

            try {

                $cq = $con->query("SELECT `forumcategories`.`id`, `forumcategories`.`name` FROM `forumcategories` ORDER BY `forumcategories`.`name` ASC");

                $forumcategories = array();

                while ($cr = $cq->fetch()) {

                    $forumcategories[] = array("id" => $cr["id"], "name" => $cr["name"]);

                }

            } catch (PDOException $e) {

                die("Query failed.");

            }

        }

    }

} else {

    $mode = "manage";

    try {

        $query = $con->query("SELECT `maps`.`id`, `maps`.`name` FROM `maps` ORDER BY `maps`.`id` DESC");

        $maps = array();

        while ($row = $query->fetch()) {

            $maps[] = array("id" => $row["id"], "name" => $row["name"]);

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
