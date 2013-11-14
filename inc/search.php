<?php

if (!isset($r_c)) header("Location: /notfound.php");

include_once "analyticstracking.php";
require_once "inc/classes/forumthread.class.php";
require_once "inc/classes/news.class.php";
require_once "inc/markdown/markdown.php";

?>

<?php

if (isset($_GET["term"]) && vf($_GET["term"])) {

    $term = str_replace("%", "", $_GET["term"]);
    $term = strip($term);

    if (strlen($term) > 50) {
        ?>

        <div class='search-title'>Please enter a keyword shorter than 50 characters.</div>

        <?php
    } else if (strlen($term) >= 3) {

        if (isset($_POST["inn"]) || ($_SESSION["lp"] != "forums" && !isset($_POST["inf"]))) {

            $nsearch = true;

            $squery = $con->prepare("SELECT `news`.`id` FROM `news` WHERE (`news`.`text` LIKE :termm OR `news`.`title` LIKE :term) AND `news`.`live` = 1 ORDER BY `news`.`id` DESC");
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

                    $news = new news($srow["id"]);

                    $news->display("search");

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

                $squery = $con->query("SELECT `forumthreads`.`id` FROM `forumthreads` WHERE `forumthreads`.`forumcategory` <> 0 AND `forumthreads`.`id` IN (" . implode(',', array_map('intval', $ra)) . ") ORDER BY `forumthreads`.`id` DESC");

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

                    <div class='search-title'><?php echo $nr . " " . resultbutton() . $sss; ?> found for <span class='search-term'><?php echo $term; ?></span></div>

                    <?php
                } else {
                    ?>

                    <div class='search-title'><?php echo $nr . " " . resultbutton() . $sss; ?> found for <span class='search-term'><?php echo $term; ?></span></div>

                    <?php
                }
            ?>

            <div class='search-results'>

                <style type='text/css' scoped>

                    <?php
                    $cq = $con->query("SELECT * FROM `forumcategories`");

                    while ($cr = $cq->fetch()) {

                        echo ".forums-category-" . $cr["name"] . "         {background-color: #" . $cr["hexcode"] . "; }\n";
                        echo ".forums-category-" . $cr["name"] . ":hover   {background-color: #" . $cr["hoverhexcode"] . "; }\n";

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

                    $thread = new forumthread($row["id"]);
                    $thread->displayRow();

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
    return "<form method='post' action='/search/" . $term . "/'><input type='hidden' value='" . $term . "' name='searchb'><input class='search-type' value='" . $prettyname . "' type='submit' name='" . $name . "'></form>";

}
