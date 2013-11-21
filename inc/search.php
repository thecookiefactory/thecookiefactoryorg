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

            $newsSearch = $con->prepare("SELECT `news`.`id` FROM `news` WHERE (`news`.`text` LIKE :termm OR `news`.`title` LIKE :term) AND `news`.`live` = 1 ORDER BY `news`.`id` DESC");
            $newsSearch->bindValue("termm", "%" . $term . "%", PDO::PARAM_STR);
            $newsSearch->bindValue("term", "%" . $term . "%", PDO::PARAM_STR);
            $newsSearch->execute();

            $nr = $newsSearch->rowCount();

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

                while ($foundNews = $newsSearch->fetch()) {

                    $news = new news($foundNews["id"]);

                    $news->display("search");

                }

                ?>

                </div>

                <?php
            }

        } else {

            $threadSearch = $con->prepare("SELECT `forumthreads`.`id` FROM `forumthreads` WHERE (`forumthreads`.`text` LIKE :term OR `forumthreads`.`title` LIKE :termm) AND `forumthreads`.`forumcategory` <> 0 ORDER BY `forumthreads`.`id` DESC");
            $threadSearch->bindValue("term", "%" . $term . "%", PDO::PARAM_STR);
            $threadSearch->bindValue("termm", "%" . $term . "%", PDO::PARAM_STR);
            $threadSearch->execute();

            $postSearch = $con->prepare("SELECT `forumposts`.`threadid` FROM `forumposts` WHERE `forumposts`.`text` LIKE :term ORDER BY `forumposts`.`id` DESC");
            $postSearch->bindValue("term", "%" . $term . "%", PDO::PARAM_STR);
            $postSearch->execute();

            $ra = array();

            while ($foundThread = $threadSearch->fetch()) {

                if (!in_array($foundThread["id"], $ra)) {

                    array_push($ra, $foundThread["id"]);

                }

            }

            while ($foundPost = $postSearch->fetch()) {

                if (!in_array($foundPost["threadid"], $ra)) {

                    array_push($ra, $foundPost["threadid"]);

                }

            }

            if (!empty($ra)) {

                $selectThreads = $con->query("SELECT `forumthreads`.`id` FROM `forumthreads` WHERE `forumthreads`.`forumcategory` <> 0 AND `forumthreads`.`id` IN (" . implode(',', array_map('intval', $ra)) . ") ORDER BY `forumthreads`.`id` DESC");

                $nr = $selectThreads->rowCount();

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

                try {

                    $selectCategories = $con->query("SELECT * FROM `forumcategories`");

                    while ($foundCategory = $selectCategories->fetch()) {

                        echo ".forums-category-" . $foundCategory["name"] . "         {background-color: #" . $foundCategory["hexcode"] . "; }\n";
                        echo ".forums-category-" . $foundCategory["name"] . ":hover   {background-color: #" . $foundCategory["hoverhexcode"]. "; }\n";

                    }

                } catch (PDOException $e) {

                    die("An error occurred while trying to fetch the forum categories.");

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

                while ($foundThreads = $selectThreads->fetch()) {

                    $thread = new forumthread($foundThreads["id"]);
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
