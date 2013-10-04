<?php

if (!isset($r_c)) header("Location: notfound.php");

include "analyticstracking.php";
include "markdown/markdown.php";

?>

<?php

if (isset($_POST["searchb"]) && vf($_POST["searchb"])) {

    $term = str_replace("%", "", $_POST["searchb"]);
    $term = strip($term);

    if (strlen($term) > 50) {
        ?>

        <div class='search-title'>Please enter a keyword shorter than 50 characters.</div>

        <?php
    } else if (strlen($term) >= 3) {

        if (isset($_POST["inn"]) || ($_SESSION["lp"] != "forums" && !isset($_POST["inf"]))) {

            $nsearch = true;

            $squery = $con->prepare("SELECT * FROM `news` WHERE `news`.`text` LIKE :termm or `news`.`title` LIKE :term ORDER BY `news`.`id` DESC");
            $squery->bindValue("termm", "%" . $term . "%", PDO::PARAM_STR);
            $squery->bindValue("term", "%" . $term . "%", PDO::PARAM_STR);
            $squery->execute();

            $nr = $squery->rowCount();

            $sss = ($nr == 1) ? "" : "s";

            if ($nr == 0) {

                if (strlen($term) > 23) {
                    ?>

                    <div class='search-title'>No <?php echo resultbutton(); ?>s found for your search term</div>

                    <?php
                } else {
                    ?>

                    <div class='search-title'>No <?php echo resultbutton(); ?>s found for <span class='search-term'><?php echo $term; ?></span></div>

                    <?php
                }

            } else {

                if (strlen($term) > 23) {
                    ?>

                    <div class='search-title'><?php echo $nr; ?> <?php echo resultbutton(); ?><?php echo $sss; ?> found for your search term</div>

                    <?php
                } else {
                    ?>

                    <div class='search-title'><?php echo $nr; ?> <?php echo resultbutton(); ?><?php echo $sss; ?> found for <span class='search-term'><?php echo $term; ?></span></div>

                    <?php
                }

                ?>

                <div class='search-results'>

                <?php

                while ($srow = $squery->fetch()) {
                    // TITLE, AUTHOR & DATE
                    ?>

                    <div class='article-header'>
                    <div class='article-title'><h1><a href='/news/<?php echo $srow["id"]; ?>'><?php echo $srow["title"]; ?></a></h1></div>
                    <div class='article-metadata'>

                    <?php

                    if ($srow["comments"] == 1) {

                        $ct = $con->prepare("SELECT `forumthreads`.`id` FROM `forumthreads` WHERE `forumthreads`.`newsid` = :id");
                        $ct->bindValue("id", $srow["id"], PDO::PARAM_INT);
                        $ct->execute();

                        $tid = $ct->fetch();

                        $tid = $tid["id"];

                        $cq = $con->prepare("SELECT `forumposts`.`id` FROM `forumposts` WHERE `forumposts`.`threadid` = :tid");
                        $cq->bindValue("tid", $tid, PDO::PARAM_INT);
                        $cq->execute();

                        $commnum = $cq->rowCount();
                        ?>

                        <span class='article-metadata-item'><a href='/news/<?php echo $srow["id"]; ?>#comments'><?php echo $commnum; ?> comments</a></span>

                        <?php

                    }

                    ?>

                    <span class='article-metadata-item'><span class='article-author'><?php echo getname($srow["authorid"]); ?></span></span><span class='article-metadata-item'><span class='article-date'><?php echo displaydate($srow["date"]); ?></span></span></div>

                    <?php

                    //if edited
                    if ($srow["editorid"] > 0 && $srow["editdate"] > $srow["date"]) {
                        ?>

                        <div class='article-edit-metadata'><span class='article-metadata-item'><span class='article-author'><?php echo getname($srow["editorid"]); ?></span></span><span class='article-metadata-item'><span class='article-date'><?php echo displaydate($srow["editdate"]); ?></span></span></div>

                        <?php
                    }

                    ?>
                    </div>

                    <?php

                    // BODY
                    ?>

                    <article>
                    <span class='article-text'><?php echo Markdown(substr($srow["text"], 0, 100)); ?></span>
                    </article>
                    <hr class='article-separator'>

                    <?php
                }
                ?>

                </div>

                <?php
            }

        } else {

            $squery1 = $con->prepare("SELECT `forumthreads`.`id` FROM `forumthreads` WHERE (`forumthreads`.`text` LIKE :term OR `forumthreads`.`title` LIKE :termm) AND `forumthreads`.`forumcategory` <> 0 ORDER BY `forumthreads`.`id` DESC");
            $squery1->bindValue("term", "%" . $term . "%", PDO::PARAM_STR);
            $squery1->bindValue("termm", "%" . $term . "%", PDO::PARAM_STR);
            $squery1->execute();

            $squery2 = $con->prepare("SELECT `forumposts`.`threadid` FROM `forumposts` WHERE `forumposts`.`text` LIKE :term ORDER BY `forumposts`.`id` DESC");
            $squery2->bindValue("term", "%" . $term . "%", PDO::PARAM_STR);
            $squery2->execute();

            $ra = array();

            while ($row = $squery1->fetch()) {

                if (!in_array($row["id"], $ra)) {

                    array_push($ra, $row["id"]);

                }

            }

            while ($row = $squery2->fetch()) {

                if (!in_array($row["threadid"], $ra)) {

                    array_push($ra, $row["threadid"]);

                }

            }

            if (!empty($ra)) {

                $squery = $con->query("SELECT * FROM `forumthreads` WHERE `forumthreads`.`forumcategory` <> 0 AND `forumthreads`.`id` IN (" . implode(',', array_map('intval', $ra)) . ") ORDER BY `forumthreads`.`id` DESC");

                $nr = $squery->rowCount();

            } else {

                $nr = 0;

            }

            $sss = ($nr == 1) ? "" : "s";

            if ($nr == 0) {

                if (strlen($term) > 21) {
                    ?>

                    <div class='search-title'>No <?php echo resultbutton(); ?>s found for your search term</div>

                    <?php
                } else {
                    ?>

                    <div class='search-title'>No <?php echo resultbutton(); ?>s found for <span class='search-term'><?php echo $term; ?></span></div>

                    <?php
                }

            } else {

                if (strlen($term) > 21) {
                    ?>

                    <div class='search-title'><?php echo $nr." ".resultbutton().$sss; ?> found for <span class='search-term'><?php echo $term; ?></span></div>

                    <?php
                } else {
                    ?>

                    <div class='search-title'><?php echo $nr." ".resultbutton().$sss; ?> found for <span class='search-term'><?php echo $term; ?></span></div>

                    <?php
                }
            ?>

            <div class='search-results'>

                <style type='text/css' scoped>

                    <?php
                    $cq = $con->query("SELECT * FROM `forumcategories`");

                    while ($cr = $cq->fetch()) {

                        echo ".forums-category-".$cr["name"]."         {background-color: #".$cr["hexcode"]."; }\n";
                        echo ".forums-category-".$cr["name"].":hover   {background-color: #".$cr["hoverhexcode"]."; }\n";

                    }
                    ?>

                </style>
                <table class='forums-table'>
                        <colgroup>
                            <col class='forums-column-category'>
                            <col class='forums-column-title'>
                            <col class='forums-column-modifydate'>
                            <col class='forums-column-postcount'>
                        </colgroup>
                    <tbody>

                <?php

                while ($row = $squery->fetch()) {

                    ?>
                    <tr class='forums-entry'>
                        <td class='forums-entry-category forums-category-<?php echo getcatname($row["forumcategory"]); ?>'>
                            <a href='/forums/category/<?php echo $row["forumcategory"]; ?>'>
                                <div class='forums-entry-category-text'>

                                    <?php echo getcatname($row["forumcategory"]); ?>

                                </div>
                            </a>
                        </td>
                        <td class='forums-entry-main <?php echo (($row["closed"] == 1) ? "forums-entry-closed" : ""); ?>'>
                            <a class='forums-entry-title' href='/forums/<?php echo $row["id"]; ?>'>

                                <?php echo $row["title"]; ?>

                            </a>
                            <br>
                            <span class='forums-entry-metadata'>

                                created by <?php echo getname($row["authorid"])." ".displaydate($row["date"]); ?>

                            </span>
                        </td>
                        <td class='forums-entry-modifydate'>
                            <span class='forums-entry-miniheader'>

                                <?php echo "Last reply posted"?>

                            </span>
                            <br>

                            <?php echo displaydate($row["lastdate"]); ?>

                        </td>
                        <td class='forums-entry-postcount'>
                            <span class='forums-entry-miniheader'>
                                Thread has
                            </span>
                            <br>

                            <?php
                                $q = $con->prepare("SELECT `forumposts`.`id` FROM `forumposts` WHERE `forumposts`.`threadid` = :id");
                                $q->bindValue("id", $row["id"], PDO::PARAM_INT);
                                $q->execute();
                                echo $q->rowCount().(($q->rowCount()) == 1 ? " reply" : " replies");
                            ?>

                        </td>
                    </tr>

                    <?php

                }

                ?>
                    </tbody>
                </table>

            </div>

             <?php

            }

        }

    } else {
        ?>

        <div class='search-title'>Please enter a keyword longer than 2 characters.</div>

        <?php
    }

} else {
    ?>

    <div class='search-title'>No keyword defined.</div>

    <?php
}

function resultbutton() {

    global $term;
    global $nsearch;

    $name = (isset($nsearch) && $nsearch == true) ? "inf" : "inn";
    $prettyname = ($name == "inf") ? "article" : "forum post";
    return "<form method='post' action='/search'><input type='hidden' value='".$term."' name='searchb'><input class='search-type' value='".$prettyname."' type='submit' name='".$name."'></form>";

}

function getcatname($x) {

    global $con;

    $fq = $con->prepare("SELECT `forumcategories`.`name` FROM `forumcategories` WHERE `forumcategories`.`id` = :x");
    $fq->bindValue("x", $x, PDO::PARAM_INT);
    $fq->execute();
    $fr = $fq->fetch();

    return $fr["name"];

}
