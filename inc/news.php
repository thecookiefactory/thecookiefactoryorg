<?php

if (!isset($r_c)) header("Location: /notfound.php");

require_once "inc/classes/forumthread.class.php";
require_once "inc/classes/news.class.php";

$newsArray = array();

$_SESSION["lp"] = "news";

if (!isset($_GET["id"]) || !vf($_GET["id"])) {

    if (!isset($_GET["page"]) || !is_numeric($_GET["page"]) || $_GET["page"] < 1) {

        $page = 1;

    } else {

        $page = strip($_GET["page"]);

    }

    $xo = ($page - 1) * 5;

    try {

        $selectNewsByPage = $con->prepare("SELECT `news`.`id` FROM `news` WHERE `news`.`live` = 1 ORDER BY `news`.`date` DESC LIMIT :xo, 5");
        $selectNewsByPage->bindValue("xo", $xo, PDO::PARAM_INT);
        $selectNewsByPage->execute();

    } catch (PDOException $e) {

        die("Failed to fetch the news.");

    }

    try {

        $liveNewsCount = $con->query("SELECT `news`.`id` FROM `news` WHERE `news`.`live` = 1")->rowCount();

    } catch (PDOException $e) {

        die("Failed to fetch live news.");

    }

    if (($selectNewsByPage->rowCount() == 0) && ($liveNewsCount != 0)) {

        header("Location: /news");

    }

    $pageCount = ceil($liveNewsCount / 5);

    while ($foundNews = $selectNewsByPage->fetch()) {

        $news = new news($foundNews["id"]);
        $newsArray[$foundNews["id"]] = $news->returnArray();

    }

    echo $twig->render("news.html", array("index_var" => $index_var, "news" => $newsArray, "pagecount" => $pageCount, "page" => $page, "main" => 1));

} else {

    $news = new news(strip($_GET["id"]), "stringid");

    if ($news->isReal()) {

        $new = $news->returnArray();

        if ($new["comments"] == 1) {

            try {

                $selectThreadId = $con->prepare("SELECT `forumthreads`.`id` FROM `forumthreads` WHERE `forumthreads`.`newsid` = :id");
                $selectThreadId->bindValue("id", $news->getId(), PDO::PARAM_INT);
                $selectThreadId->execute();

                $threadData = $selectThreadId->fetch();

                $tid = $threadData["id"];

                $thread = new forumthread($tid);

            } catch (PDOException $e) {

                echo "Failed to fetch comments.";

            }

        }

        echo $twig->render("news.html", array("index_var" => $index_var, "new" => $new, "main" => 0, "comments" => ($new["comments"] == 1), "thread" => $thread->returnArray("main"), "loggedin" => $user->isReal()));

    } else {

        header("Location: /news");

    }

}
