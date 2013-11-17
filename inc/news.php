<?php

if (!isset($r_c)) header("Location: /notfound.php");

include_once "analyticstracking.php";
require_once "inc/classes/news.class.php";
require_once "inc/markdown/markdown.php";

$_SESSION["lp"] = "news";

if (!isset($_GET["id"]) || !vf($_GET["id"])) {

    if (!isset($_GET["page"]) || !is_numeric($_GET["page"]) || $_GET["page"] < 1) {

        $page = 1;

    } else {

        $page = strip($_GET["page"]);

    }

    $xo = ($page - 1) * 5;
    $query = $con->prepare("SELECT `news`.`id` FROM `news` WHERE `news`.`live` = 1 ORDER BY `news`.`id` DESC LIMIT :xo, 5");
    $query->bindValue("xo", $xo, PDO::PARAM_INT);
    $query->execute();

    if (($query->rowCount() == 0) && ($con->query("SELECT `news`.`id` FROM `news` WHERE `news`.`live` = 1")->rowCount() != 0)) {

        header("Location: /news");

    }

    if ($query->rowCount() == 0) {
        ?>

        There are no news posts.

        <?php
    } else {

        $iii = 0;

        while ($row = $query->fetch()) {

            $iii++;

            $news = new news($row["id"]);
            $news->display("front");

            if ($iii == 1) {
                ?>
                <div class='news-ad'>
                    <script async src='//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js'></script>
                    <!-- News Articles Inline -->
                    <ins class='adsbygoogle'
                         style='display:inline-block;width:728px;height:90px'
                         data-ad-client='ca-pub-8578399795841431'
                         data-ad-slot='5270925477'></ins>
                    <script>
                    (adsbygoogle = window.adsbygoogle || []).push({});
                    </script>
                </div>
                <hr class='article-separator'>
                <?php

            }

        }
    }

    //page links
    $nr = $con->query("SELECT `news`.`id` FROM `news` WHERE `news`.`live` = 1")->rowCount();

    echo "<div class='news-pages'>";

    for ($i = 1; $i <= ceil($nr / 5); $i++) {

        if ($page == $i) {

            echo "<div class='news-page-number'>" . $i . "</div>";

        } else {

            echo "<a class='news-page-number' href='/news/page/" . $i . "'>" . $i . "</a>";

        }

    }

    echo "</div>";
    echo "<a class='news-rsslink' href='/rss.xml'>RSS</a>";

} else {

    // DISPLAY ONE PIECE OF NEWS

    $news = new news(strip($_GET["id"]), "stringid");

    if ($news->isReal()) {

        $news->display("main");

    } else {

        header("Location: /news");

    }

}
