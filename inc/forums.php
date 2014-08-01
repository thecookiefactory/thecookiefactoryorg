<?php

if (!isset($r_c)) header("Location: /notfound.php");

require_once "inc/classes/forumthread.class.php";

$_SESSION["lp"] = $p;

$action = isset($_GET["action"]) ? strip($_GET["action"]) : "";

if ($action == "add" && $user->isReal()) {

    $thread = new forumthread();
    $thread->addNew();

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

            $thread->commentProcess();

            echo $twig->render("forums.html", array("index_var" => $index_var, "thread" => $thread->returnArray("main"), "loggedin" => $user->isReal(), "main" => false));

        } else {

            header("Location: /forums");

        }

    } else {

        $categories = array();
        $threads = array();

        $get = isset($_GET["cat"]) ? strip($_GET["cat"]) : null;
        $cat = new forumcategory($get, "name");

        if ($cat->isReal()) {

            try {

                $selectThreads = $con->prepare("SELECT `forumthreads`.`id`
                                        FROM `forumthreads`
                                        WHERE `forumthreads`.`forumcategory` = :cat AND `forumthreads`.`forumcategory` <> 0
                                        ORDER BY `forumthreads`.`lastdate` DESC");
                $selectThreads->bindValue("cat", $cat->getId(), PDO::PARAM_INT);
                $selectThreads->execute();

                $categoryFilter = 1;

            } catch (PDOException $e) {

                die("An error occurred while trying to fetch the threads.");

            }

        } else {

            try {

                $selectThreads = $con->query("SELECT `forumthreads`.`id` FROM `forumthreads` WHERE `forumthreads`.`forumcategory` <> 0 ORDER BY `forumthreads`.`lastdate` DESC");

                $categoryFilter = 0;

            } catch (PDOException $e) {

                die("An error occurred while trying to fetch the threads.");

            }

        }

        try {

            $selectCategories = $con->query("SELECT `forumcategories`.`id` FROM `forumcategories`");

            while ($foundCategory = $selectCategories->fetch()) {

                $category = new forumcategory($foundCategory["id"]);
                $categories[] = $category->returnArray();

            }

        } catch (PDOException $e) {

            die("An error occurred while trying to fetch the forum categories.");

        }

        while ($foundThread = $selectThreads->fetch()) {

            $thread = new forumthread($foundThread["id"]);
            $threads[] = $thread->returnArray();

        }

        echo $twig->render("forums.html", array("index_var" => $index_var, "categories" => $categories, "loggedin" => $user->isReal(), "categoryfilter" => $categoryFilter, "threads" => $threads, "main" => true));

    }

}
