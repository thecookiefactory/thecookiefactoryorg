<?php

session_start();

$r_c = 1;
require_once "../inc/functions.php";
require_once "../inc/classes/user.class.php";

$user = new user((isset($_SESSION["userid"]) ? $_SESSION["userid"] : null));

if (!$user->isAdmin()) die("403");

$twig = twigInit();

if (isset($_GET["action"]) && ($_GET["action"] == "edit" || $_GET["action"] == "delete" || $_GET["action"] == "write")) {

    if ($_GET["action"] == "edit") {

        $mode = "edit";

        if (isset($_GET["id"]) && is_numeric($_GET["id"])) {

            $id = strip($_GET["id"]);

            try {

                $eq = $con->prepare("SELECT `news`.`id`, `news`.`title`, `news`.`text`, `news`.`authorid`, `news`.`date`, `news`.`editorid`, `news`.`editdate`, BIN(`news`.`comments`), BIN(`news`.`live`)
                                     FROM `news`
                                     WHERE `news`.`id` = :id");
                $eq->bindValue("id", $id, PDO::PARAM_INT);
                $eq->execute();

            } catch (PDOException $e) {

                die("Query failed.");

            }


            if ($eq->rowCount() == 1) {

                $er = $eq->fetch();

                if (isset($_POST["submit"])) {

                    $title = strip($_POST["title"]);
                    $editorid = $user->getId();
                    $text = strip($_POST["text"]);

                    if (isset($_POST["comments"]) && $_POST["comments"] == "on") {

                        $comments = 0;

                    } else {

                        $comments = 1;

                    }

                    if (isset($_POST["live"]) && $_POST["live"] == "on") {

                        $live = 1;

                        if ($er["BIN(`news`.`live`)"] == 0) {

                            try {

                                $uq = $con->prepare("UPDATE `news` SET `news`.`date` = now() WHERE `news`.`id` = :id");
                                $uq->bindValue("id", $id, PDO::PARAM_INT);
                                $uq->execute();

                            } catch (PDOException $e) {

                                die("Query failed.");

                            }

                            if (!vf($er["stringid"]))
                                generateid($id);

                        }

                    } else {

                        $live = 0;

                    }

                    try {

                        $uq = $con->prepare("UPDATE `news`
                                             SET `news`.`title` = :title,
                                                 `news`.`editorid` = :editorid,
                                                 `news`.`text` = :text,
                                                 `news`.`comments` = :comments,
                                                 `news`.`live` = :live
                                             WHERE `news`.`id` = :id");
                        $uq->bindValue("title", $title, PDO::PARAM_STR);
                        $uq->bindValue("editorid", $editorid, PDO::PARAM_INT);
                        $uq->bindValue("text", $text, PDO::PARAM_STR);
                        $uq->bindValue("comments", $comments, PDO::PARAM_INT);
                        $uq->bindValue("live", $live, PDO::PARAM_INT);
                        $uq->bindValue("id", $id, PDO::PARAM_INT);
                        $uq->execute();

                        $status = "success";

                    } catch (PDOException $e) {

                        $status = "failure";

                    }

                } else {

                    $articleData = array("id" => $er["id"],
                                         "title" => $er["title"],
                                         "text" => $er["text"],
                                         "comments" => $er["BIN(`news`.`comments`)"],
                                         "live" => $er["BIN(`news`.`live`)"]);

                    $status = "progress";

                }

            } else {

                $status = "notfound";

            }

        } else {

            $status = "notfound";

        }

    } else if ($_GET["action"] == "delete") {

        $mode = "delete";

        if (isset($_GET["id"]) && is_numeric($_GET["id"])) {

            $id = strip($_GET["id"]);

            try {

                $eq = $con->prepare("SELECT `news`.`id` FROM `news` WHERE `news`.`id` = :id");
                $eq->bindValue("id", $id, PDO::PARAM_INT);
                $eq->execute();

            } catch (PDOException $e) {

                die("Query failed.");

            }

            if ($eq->rowCount() == 1) {

                if (isset($_POST["delete"])) {

                    try {

                        $dq = $con->prepare("DELETE FROM `news` WHERE `news`.`id` = :id");
                        $dq->bindValue("id", $id, PDO::PARAM_INT);
                        $dq->execute();

                        $dq = $con->prepare("DELETE FROM `forumthreads` WHERE `forumthreads`.`newsid` = :id");
                        $dq->bindValue("id", $id, PDO::PARAM_INT);
                        $dq->execute();
                        // comments are not actually deleted at this point, but w/e

                        $status = "success";

                    } catch (PDOException $e) {

                        $status = "failure";

                    }

                } else {

                    $status = "confirm";

                    $currentId = $id;

                }

            } else {

                $status = "notfound";

            }

        } else {

            $status = "notfound";

        }

    } else {

        $mode = "write";

        if (isset($_POST["submit"])) {

            $title = strip($_POST["title"]);
            $author = $user->getId();
            $text = strip($_POST["text"]);

            if (isset($_POST["comments"]) && $_POST["comments"] == "on") {

                $comments = 0;

            } else {

                $comments = 1;

            }

            if (isset($_POST["live"]) && $_POST["live"] == "on") {

                $live = 1;

            } else {

                $live = 0;

            }

            try {

                $iq = $con->prepare("INSERT INTO `news` VALUES(DEFAULT, :title, :text, :author, now(), DEFAULT, DEFAULT, :comments, :live, '')");
                $iq->bindValue("title", $title, PDO::PARAM_STR);
                $iq->bindValue("text", $text, PDO::PARAM_STR);
                $iq->bindValue("author", $author, PDO::PARAM_INT);
                $iq->bindValue("comments", $comments, PDO::PARAM_INT);
                $iq->bindValue("live", $live, PDO::PARAM_INT);
                $iq->execute();

                $id = $con->lastInsertId();

                $iq = $con->prepare("INSERT INTO `forumthreads` VALUES(DEFAULT, :title, :text, :author, now(), DEFAULT, DEFAULT, 0, DEFAULT, :id, 0)");
                $iq->bindValue("title", $title, PDO::PARAM_STR);
                $iq->bindValue("text", $text, PDO::PARAM_STR);
                $iq->bindValue("author", $author, PDO::PARAM_INT);
                $iq->bindValue("id", $id, PDO::PARAM_INT);
                $iq->execute();

                generateid($id);

                $status = "success";

            } catch (PDOException $e) {

                $status = "failure";

            }

        } else {

            $status = "progress";

        }

    }

    exec($config["python"]["rss"], $output, $return);

    $rssResult = (!$return) ? "success" : "failure";

} else {

    $mode = "manage";

    try {

        $query = $con->query("SELECT `news`.`id`, `news`.`title`, `news`.`text`
                              FROM `news`
                              WHERE `news`.`live` = 0
                              ORDER BY `news`.`id` DESC");

        $unpublishedNews = array();

        while ($row = $query->fetch()) {

            $unpublishedNews[] = array("id" => $row["id"], "title" => $row["title"], "text" => substr($row["text"], 0, 100));

        }

    } catch (PDOException $e) {

        die("Query failed.");

    }

    try {

        $query = $con->query("SELECT `news`.`id`, `news`.`title`, `news`.`text`, `news`.`stringid`
                              FROM `news`
                              WHERE `news`.`live` = 1
                              ORDER BY `news`.`id` DESC");

        $publishedNews = array();

        while ($row = $query->fetch()) {

            if (!vf($row["stringid"])) {

                generateid($row["id"]);

            }

            $publishedNews[] = array("id" => $row["id"], "title" => $row["title"], "text" => substr($row["text"], 0, 100));

        }

    } catch (PDOException $e) {

        die("Query failed.");

    }

}

echo $twig->render("admin/news.html", array("mode" => $mode,
                                            "status" => (isset($status) ? $status : null),
                                            "articleData" => (isset($articleData) ? $articleData : null),
                                            "currentid" => (isset($currentId) ? $currentId : null),
                                            "unpublishedNews" => (isset($unpublishedNews) ? $unpublishedNews : null),
                                            "publishedNews" => (isset($publishedNews) ? $publishedNews : null)));

function generateid($x) {

    global $con;

    try {

        $gq = $con->prepare("SELECT `news`.`id`, `news`.`title` FROM `news` WHERE `news`.`id` = :id");
        $gq->bindValue("id", $x, PDO::PARAM_INT);
        $gq->execute();

        $gr = $gq->fetch();

    } catch (PDOException $e) {

        die("Query failed.");

    }

    $stringid = preg_replace("/[^A-Za-z0-9 ]/", "", $gr["title"]);

    $stringid = str_replace(" ", "_", $stringid);

    try {

        $cq = $con->prepare("SELECT `news`.`id` FROM `news` WHERE `news`.`stringid` = :si");
        $cq->bindValue("si", $stringid, PDO::PARAM_STR);
        $cq->execute();

    } catch (PDOException $e) {

        die("Query failed.");

    }

    if ($cq->rowCount() != 0) {

        $stringid .= "-".$x;

    }

    try {

        $uq = $con->prepare("UPDATE `news` SET `news`.`stringid` = :si WHERE `news`.`id` = :id");
        $uq->bindValue("si", $stringid, PDO::PARAM_STR);
        $uq->bindValue("id", $x, PDO::PARAM_INT);
        $uq->execute();

    } catch (PDOException $e) {

        die("Query failed.");

    }

    return 0;

}
