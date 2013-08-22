<?php

if (!isset($r_c)) header("Location: notfound.php");

include "analyticstracking.php";
include "markdown/markdown.php";

$_SESSION["lp"] = "news";

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
// DISPLAY ALL THE NEWS

    if (!isset($_GET["page"]) || !is_numeric($_GET["page"]) || $_GET["page"] < 1) {

        $page = 1;

    } else {

        $page = strip($_GET["page"]);

    }

    $xo = ($page - 1) * 5;
    $query = $con->prepare("SELECT * FROM `news` WHERE `news`.`live` = 1 ORDER BY `news`.`id` DESC LIMIT :xo, 5");
    $query->bindValue("xo", $xo, PDO::PARAM_INT);
    $query->execute();

    if (($query->rowCount() == 0) && ($con->query("SELECT * FROM `news` WHERE `news`.`live` = 1")->rowCount() != 0)) {

        header("Location: ?p=news");

    }

    if ($query->rowCount() == 0) {
        ?>

        There are no news posts.

        <?php
    } else {

        $iii = 0;

        while ($row = $query->fetch()) {

            $iii++;

            // TITLE, AUTHOR & DATE
            ?>

            <div class='article-header'>
            <div class='article-title'><h1><a href='?p=news&amp;id=<?php echo $row["id"]; ?>'><?php echo $row["title"]; ?></a></h1></div>
            <div class='article-metadata'>

            <?php

            if ($row["comments"] == 1) {

                $ct = $con->prepare("SELECT `forumthreads`.`id` FROM `forumthreads` WHERE `forumthreads`.`newsid` = :id");
                $ct->bindValue("id", $row["id"], PDO::PARAM_INT);
                $ct->execute();

                $tid = $ct->fetch();

                $tid = $tid["id"];

                $cq = $con->prepare("SELECT `forumposts`.`id` FROM `forumposts` WHERE `forumposts`.`threadid` = :tid");
                $cq->bindValue("tid", $tid, PDO::PARAM_INT);
                $cq->execute();

                $commnum = $cq->rowCount();
                ?>

                <?php
                if ($commnum != 1) {
                    ?>
                    <span class='article-metadata-item'><a href='?p=news&amp;id=<?php echo $row["id"]; ?>#comments'><?php echo $commnum; ?> comments</a></span>
                    <?php
                } else {
                    ?>
                    <span class='article-metadata-item'><a href='?p=news&amp;id=<?php echo $row["id"]; ?>#comment'><?php echo $commnum; ?> comment</a></span>
                    <?php
                }

            }

            ?>

            <span class='article-metadata-item'><span class='article-author'><?php echo getname($row["authorid"]); ?></span></span><span class='article-metadata-item'><span class='article-date'><?php echo displaydate($row["date"]); ?></span></span></div>

            <?php

            //if edited
            if ($row["editorid"] > 0 && $row["editdate"] > $row["date"]) {
                ?>

                <div class='article-edit-metadata'><span class='article-metadata-item'><span class='article-author'><?php echo getname($row["editorid"]); ?></span></span><span class='article-metadata-item'><span class='article-date'><?php echo displaydate($row["editdate"]); ?></span></span></div>

                <?php
            }

            ?>

            </div>

            <?php

            // BODY
            ?>

            <article>
            <span class='article-text'><?php echo Markdown($row["text"]); ?></span>
            </article>
            <hr class='article-separator'>

            <?php

            if ($iii == 1) {

                ?>
                <div class='ads'>
                do sutff
                </div>
                <hr class='article-separator'>
                <?php

            }

        }
    }

    //page links
    $nr = $con->query("SELECT * FROM `news` WHERE `news`.`live` = 1")->rowCount();

    echo "<div class='news-pages'>";

    for ($i = 1; $i <= ceil($nr / 5); $i++) {

        if ($page == $i) {

            echo "<div class='news-page-number'>" . $i . "</div>";

        } else {

            echo "<a class='news-page-number' href='?p=news&amp;page=" . $i . "'>" . $i . "</a>";

        }

    }

    echo "</div>";
    echo "<a class='news-rsslink' href='../rss.xml'>RSS</a>";
} else {

    // DISPLAY ONE PIECE OF NEWS

    $id = strip($_GET["id"]);

    $query = $con->prepare("SELECT * FROM `news` WHERE `news`.`id` = :id");
    $query->bindValue("id", $id, PDO::PARAM_INT);
    $query->execute();

    if ($query->rowCount() == 1) {

        $row = $query->fetch();

        ?>

        <div class='article-header'>
        <div class='article-title'><h1><?php echo $row["title"]; ?></h1></div><div class='article-metadata'>

        <span class='article-metadata-item'><span class='article-author'><?php echo getname($row["authorid"]); ?></span></span><span class='article-metadata-item'><span class='article-date'><?php echo displaydate($row["date"]); ?></span></span></div>

        <?php
        //if edited
        if ($row["editorid"] > 0 && $row["editdate"] > $row["date"]) {
            ?>

            <div class='article-edit-metadata'><span class='article-metadata-item'><span class='article-author'><?php echo getname($row["editorid"]); ?></span></span><span class='article-metadata-item'><span class='article-date'><?php echo displaydate($row["editdate"]); ?></span></span></div>

            <?php
        }
        ?>

        </div><article>
        <span class='article-text'><?php echo Markdown($row["text"]); ?></span>
        </article><hr id='comments'>

        <?php

        if ($row["comments"] == 1) {

            $ct = $con->prepare("SELECT `forumthreads`.`id` FROM `forumthreads` WHERE `forumthreads`.`newsid` = :id");
            $ct->bindValue("id", $row["id"], PDO::PARAM_INT);
            $ct->execute();

            $tid = $ct->fetch();

            $tid = $tid["id"];
            require_once "forums.php";

            } else {
                ?>

                <h1 class='comments-title'>Commenting disabled</h1></div>

                <?php
            }

    } else {

        // redirecting to the main page instead of giving an error message
        header("Location: ?p=news");

    }

}
