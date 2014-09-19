<?php

session_start();

$r_c = 1;
require_once "../inc/functions.php";
require_once "../classes/user.class.php";

$user = new user((isset($_SESSION["userid"]) ? $_SESSION["userid"] : null));

if (!$user->isAdmin()) die("403");

$twig = twigInit();

if (isset($_GET["action"]) && ($_GET["action"] == "edit" || $_GET["action"] == "delete" || $_GET["action"] == "write")) {

    if ($_GET["action"] == "edit") {

        $mode = "edit";

        if (isset($_GET["id"]) && is_numeric($_GET["id"])) {

            $id = strip($_GET["id"]);

            try {

                $fetchNewsData = $con->prepare("SELECT `news`.`id`, `news`.`title`, `news`.`text`, `news`.`authorid`, `news`.`date`, `news`.`editorid`, `news`.`editdate`, BIN(`news`.`comments`), BIN(`news`.`live`)
                                     FROM `news`
                                     WHERE `news`.`id` = :id");
                $fetchNewsData->bindValue("id", $id, PDO::PARAM_INT);
                $fetchNewsData->execute();

            } catch (PDOException $e) {

                die("Failed to fetch news data.");

            }


            if ($fetchNewsData->rowCount() == 1) {

                $newsData = $fetchNewsData->fetch();

                if (isset($_POST["submit"])) {

                    $title = strip($_POST["title"]);
                    $editorid = $user->getId();
                    $text = strip($_POST["text"], true);

                    if (isset($_POST["comments"]) && $_POST["comments"] == "on") {

                        $comments = 0;

                    } else {

                        $comments = 1;

                    }

                    if (isset($_POST["live"]) && $_POST["live"] == "on") {

                        $live = 1;

                        if ($newsData["BIN(`news`.`live`)"] == 0) {

                            try {

                                $updateReleaseDate = $con->prepare("UPDATE `news` SET `news`.`date` = now() WHERE `news`.`id` = :id");
                                $updateReleaseDate->bindValue("id", $id, PDO::PARAM_INT);
                                $updateReleaseDate->execute();

                            } catch (PDOException $e) {

                                die("Failed to set news date.");

                            }

                            if (!validField($newsData["stringid"]))
                                generateStringid($id);

                        }

                    } else {

                        $live = 0;

                    }

                    try {

                        $updateNewsData = $con->prepare("UPDATE `news`
                                                         SET `news`.`title` = :title,
                                                             `news`.`editorid` = :editorid,
                                                             `news`.`text` = :text,
                                                             `news`.`comments` = :comments,
                                                             `news`.`live` = :live
                                                         WHERE `news`.`id` = :id");
                        $updateNewsData->bindValue("title", $title, PDO::PARAM_STR);
                        $updateNewsData->bindValue("editorid", $editorid, PDO::PARAM_INT);
                        $updateNewsData->bindValue("text", $text, PDO::PARAM_STR);
                        $updateNewsData->bindValue("comments", $comments, PDO::PARAM_INT);
                        $updateNewsData->bindValue("live", $live, PDO::PARAM_INT);
                        $updateNewsData->bindValue("id", $id, PDO::PARAM_INT);
                        $updateNewsData->execute();

                        $status = "success";

                    } catch (PDOException $e) {

                        $status = "failure";

                    }

                } else {

                    $articleData = array("id" => $newsData["id"],
                                         "title" => $newsData["title"],
                                         "text" => $newsData["text"],
                                         "comments" => $newsData["BIN(`news`.`comments`)"],
                                         "live" => $newsData["BIN(`news`.`live`)"]);

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

                $selectNewsId = $con->prepare("SELECT `news`.`id` FROM `news` WHERE `news`.`id` = :id");
                $selectNewsId->bindValue("id", $id, PDO::PARAM_INT);
                $selectNewsId->execute();

            } catch (PDOException $e) {

                die("Query failed.");

            }

            if ($selectNewsId->rowCount() == 1) {

                if (isset($_POST["delete"])) {

                    try {

                        $deleteNews = $con->prepare("DELETE FROM `news` WHERE `news`.`id` = :id");
                        $deleteNews->bindValue("id", $id, PDO::PARAM_INT);
                        $deleteNews->execute();

                        $deleteThread = $con->prepare("DELETE FROM `forumthreads` WHERE `forumthreads`.`newsid` = :id");
                        $deleteThread->bindValue("id", $id, PDO::PARAM_INT);
                        $deleteThread->execute();
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
            $text = strip($_POST["text"], true);

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

                $insertNews = $con->prepare("INSERT INTO `news` VALUES(DEFAULT, :title, :text, :author, now(), DEFAULT, DEFAULT, :comments, :live, '')");
                $insertNews->bindValue("title", $title, PDO::PARAM_STR);
                $insertNews->bindValue("text", $text, PDO::PARAM_STR);
                $insertNews->bindValue("author", $author, PDO::PARAM_INT);
                $insertNews->bindValue("comments", $comments, PDO::PARAM_INT);
                $insertNews->bindValue("live", $live, PDO::PARAM_INT);
                $insertNews->execute();

                $id = $con->lastInsertId();

                $createThread = $con->prepare("INSERT INTO `forumthreads` VALUES(DEFAULT, :title, :text, :author, now(), DEFAULT, DEFAULT, 0, DEFAULT, :id, 0)");
                $createThread->bindValue("title", $title, PDO::PARAM_STR);
                $createThread->bindValue("text", $text, PDO::PARAM_STR);
                $createThread->bindValue("author", $author, PDO::PARAM_INT);
                $createThread->bindValue("id", $id, PDO::PARAM_INT);
                $createThread->execute();

                generateStringid($id);

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

        $selectNewsData = $con->query("SELECT `news`.`id`, `news`.`title`, `news`.`text`
                              FROM `news`
                              WHERE `news`.`live` = 0
                              ORDER BY `news`.`id` DESC");

        $unpublishedNews = array();

        while ($newsData = $selectNewsData->fetch()) {

            $unpublishedNews[] = array("id" => $newsData["id"], "title" => $newsData["title"], "text" => substr($newsData["text"], 0, 100));

        }

    } catch (PDOException $e) {

        die("Query failed.");

    }

    try {

        $selectNewsData = $con->query("SELECT `news`.`id`, `news`.`title`, `news`.`text`, `news`.`stringid`
                              FROM `news`
                              WHERE `news`.`live` = 1
                              ORDER BY `news`.`id` DESC");

        $publishedNews = array();

        while ($newsData = $selectNewsData->fetch()) {

            if (!validField($newsData["stringid"])) {

                generateStringid($newsData["id"]);

            }

            $publishedNews[] = array("id" => $newsData["id"], "title" => $newsData["title"], "text" => substr($newsData["text"], 0, 100));

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

function generateStringid($x) {

    global $con;

    try {

        $fetchNewsData = $con->prepare("SELECT `news`.`id`, `news`.`title` FROM `news` WHERE `news`.`id` = :id");
        $fetchNewsData->bindValue("id", $x, PDO::PARAM_INT);
        $fetchNewsData->execute();

        $newsData = $fetchNewsData->fetch();

    } catch (PDOException $e) {

        die("Failed to fetch news data.");

    }

    $stringid = preg_replace("/[^A-Za-z0-9 ]/", "", $newsData["title"]);

    $stringid = str_replace(" ", "_", $stringid);

    try {

        $checkAvailability = $con->prepare("SELECT `news`.`id` FROM `news` WHERE `news`.`stringid` = :si");
        $checkAvailability->bindValue("si", $stringid, PDO::PARAM_STR);
        $checkAvailability->execute();

    } catch (PDOException $e) {

        die("Failed to check the availability of the stringid.");

    }

    if ($checkAvailability->rowCount() != 0) {

        $stringid .= "-".$x;

    }

    try {

        $setStringid = $con->prepare("UPDATE `news` SET `news`.`stringid` = :si WHERE `news`.`id` = :id");
        $setStringid->bindValue("si", $stringid, PDO::PARAM_STR);
        $setStringid->bindValue("id", $x, PDO::PARAM_INT);
        $setStringid->execute();

    } catch (PDOException $e) {

        die("Failed to set the stringid.");

    }

    return 0;

}
