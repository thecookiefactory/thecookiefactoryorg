<?php

if (!isset($r_c)) header("Location: /notfound.php");

include_once "analyticstracking.php";
require_once "inc/classes/forumthread.class.php";

$_SESSION["lp"] = $p;

$action = isset($_GET["action"]) ? strip($_GET["action"]) : "";

if ($action == "add" && $user->isLoggedIn()) {

    $thread = new forumthread();
    $thread->addnew();

} else if ($action == "edit" && $user->isLoggedIn() && isset($_GET["tid"]) && is_numeric($_GET["tid"])) {

    $tid = strip($_GET["tid"]);

    if (isset($_GET["pid"]) && is_numeric($_GET["pid"])) {

        $post = new forumpost(strip($_GET["pid"]));

        if ($post->isReal()) {

            $post->edit();

        }

    } else {

        $thread = new forumthread($tid);

        if ($thread->isReal()) {

            $thread->edit();

        }


    }

} else {

    if ((isset($_GET["id"]) && is_numeric($_GET["id"])) || (isset($tid) && is_numeric($tid))) {

        // SHOW ONE THREAD
        $thread = new forumthread(isset($tid) ? strip($tid) : strip($_GET["id"]));

        if (($thread->isReal() && !$thread->isNewsThread()) || ($thread->isNewsThread() && isset($tid))) {

            $thread->display();

        } else {

            header("Location: /forums");

        }

    } else {

        if ($user->isLoggedIn()) {

            ?>

            <a class='forums-createthread' href='/forums/add'>
                <span class='forums-createthread-sign'>+</span>
                <span class='forums-createthread-text'>create a new thread</span>
            </a>

            <?php

        }

        $cat = new forumcategory(isset($_GET["cat"]) ? strip($_GET["cat"]) : null);

        if ($cat->isReal()) {

            $query = $con->prepare("SELECT `forumthreads`.`id`
                                    FROM `forumthreads`
                                    WHERE `forumthreads`.`forumcategory` = :cat AND `forumthreads`.`forumcategory` <> 0
                                    ORDER BY `forumthreads`.`lastdate` DESC");
            $query->bindValue("cat", $cat->getId(), PDO::PARAM_INT);
            $query->execute();

            ?>
            <a class='forums-clearfilter' href='/forums'>&#x21A9; clear category filter</a>
            <?php

        } else {

            $query = $con->query("SELECT `forumthreads`.`id` FROM `forumthreads` WHERE `forumthreads`.`forumcategory` <> 0 ORDER BY `forumthreads`.`lastdate` DESC");

        }

        ?>

        <style type='text/css' scoped>

            <?php
            $cq = $con->query("SELECT * FROM `forumcategories`");

            while ($cr = $cq->fetch()) {

                echo ".forums-category-" . $cr["name"] . "         {background-color: #" . $cr["hexcode"] . "; }\n";
                echo ".forums-category-" . $cr["name"] . ":hover   {background-color: #" . $cr["hoverhexcode"]. "; }\n";

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

        if ($query->rowCount() != 0) {

            while ($row = $query->fetch()) {

                $thread = new forumthread($row["id"]);
                $thread->displayRow();

            }

        } else {

            echo "There are no forum threads. Why don't you create one?";

        }

        ?>

            </tbody>
        </table>

        <?php

    }

}
