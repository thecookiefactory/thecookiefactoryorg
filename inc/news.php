<?php

if (!isset($r_c)) header("Location: /notfound.php");

include_once "analyticstracking.php";
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
    $selectNewsByPage = $con->prepare("SELECT `news`.`id` FROM `news` WHERE `news`.`live` = 1 ORDER BY `news`.`date` DESC LIMIT :xo, 5");
    $selectNewsByPage->bindValue("xo", $xo, PDO::PARAM_INT);
    $selectNewsByPage->execute();

    if (($selectNewsByPage->rowCount() == 0) && ($con->query("SELECT `news`.`id` FROM `news` WHERE `news`.`live` = 1")->rowCount() != 0)) {

        header("Location: /news");

    }

    $pageCount = ceil($con->query("SELECT `news`.`id` FROM `news` WHERE `news`.`live` = 1")->rowCount() / 5);

    while ($foundNews = $selectNewsByPage->fetch()) {

        $news = new news($foundNews["id"]);
        $newsArray[$foundNews["id"]] = $news->returnArray();

    }

    echo $twig->render("news.html", array("news" => $newsArray, "pagecount" => $pageCount, "page" => $page, "main" => 0));

} else {

    $news = new news(strip($_GET["id"]), "stringid");

    if ($news->isReal()) {

        $new = $news->returnArray();

        echo $twig->render("news-box.html", array("new" => $new, "main" => 1));

        if ($new["comments"] == 1) {

            $selectThreadId = $con->prepare("SELECT `forumthreads`.`id` FROM `forumthreads` WHERE `forumthreads`.`newsid` = :id");
            $selectThreadId->bindValue("id", $news->getId(), PDO::PARAM_INT);
            $selectThreadId->execute();

            $threadData = $selectThreadId->fetch();

            $tid = $threadData["id"];

            require_once "inc/forums.php";

        }

    } else {

        header("Location: /news");

    }

}
