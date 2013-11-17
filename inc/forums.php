<?php

if (!isset($r_c)) header("Location: /notfound.php");

include_once "analyticstracking.php";
require_once "inc/classes/forumthread.class.php";

$_SESSION["lp"] = $p;

$action = isset($_GET["action"]) ? strip($_GET["action"]) : "";

if ($action == "add" && $user->isReal()) {

    $thread = new forumthread();
    $thread->addnew();

} else if ($action == "edit" && $user->isReal() && isset($_GET["tid"]) && is_numeric($_GET["tid"])) {

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

        $thread = new forumthread(isset($tid) ? strip($tid) : strip($_GET["id"]));

        if (($thread->isReal() && !$thread->isNewsThread()) || ($thread->isNewsThread() && isset($tid))) {

            $thread->display();

        } else {

            header("Location: /forums");

        }

    } else {

        if ($user->isReal()) {

            ?>

            <a class='forums-createthread' href='/forums/add'>
                <span class='forums-createthread-sign'>+</span>
                <span class='forums-createthread-text'>create a new thread</span>
            </a>

            <?php

        }

        $get = isset($_GET["cat"]) ? strip($_GET["cat"]) : null;
        $cat = new forumcategory($get, "name");

        if ($cat->isReal()) {

            try {

                $squery = $con->prepare("SELECT `forumthreads`.`id`
                                        FROM `forumthreads`
                                        WHERE `forumthreads`.`forumcategory` = :cat AND `forumthreads`.`forumcategory` <> 0
                                        ORDER BY `forumthreads`.`lastdate` DESC");
                $squery->bindValue("cat", $cat->getId(), PDO::PARAM_INT);
                $squery->execute();

            } catch (PDOException $e) {

                die("An error occurred while trying to fetch the threads.");

            }

            ?>
            <a class='forums-clearfilter' href='/forums'>&#x21A9; clear category filter</a>
            <?php

        } else {

            try {

                $squery = $con->query("SELECT `forumthreads`.`id` FROM `forumthreads` WHERE `forumthreads`.`forumcategory` <> 0 ORDER BY `forumthreads`.`lastdate` DESC");

            } catch (PDOException $e) {

                die("An error occurred while trying to fetch the threads.");

            }

        }

        ?>

        <style type='text/css' scoped>

            <?php

            try {

                $cq = $con->query("SELECT * FROM `forumcategories`");

                while ($cr = $cq->fetch()) {

                    echo ".forums-category-" . $cr["name"] . "         {background-color: #" . $cr["hexcode"] . "; }\n";
                    echo ".forums-category-" . $cr["name"] . ":hover   {background-color: #" . $cr["hoverhexcode"]. "; }\n";

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

        if ($squery->rowCount() != 0) {

            while ($srow = $squery->fetch()) {

                $thread = new forumthread($srow["id"]);
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
