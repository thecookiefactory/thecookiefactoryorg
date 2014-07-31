<?php

if (!isset($r_c)) header("Location: /notfound.php");

require_once "inc/classes/forumthread.class.php";
require_once "inc/classes/news.class.php";

$termlen = 0;
$term = 0;
$resultsFound = 0;
$categories = array();
$newsArray = array();
$threadArray = array();

if (isset($_GET["term"]) && vf($_GET["term"])) {

    $term = str_replace("%", "", $_GET["term"]);
    $term = strip($term);
    $termlen = strlen($term);

    if (strlen($term) < 50 && strlen($term) > 2) {

        if (isset($_POST["inn"]) || ($_SESSION["lp"] != "forums" && !isset($_POST["inf"]))) {

            $searchType = "news";

            try {

                $newsSearch = $con->prepare("SELECT `news`.`id` FROM `news` WHERE (`news`.`text` LIKE :termm OR `news`.`title` LIKE :term) AND `news`.`live` = 1 ORDER BY `news`.`id` DESC");
                $newsSearch->bindValue("termm", "%" . $term . "%", PDO::PARAM_STR);
                $newsSearch->bindValue("term", "%" . $term . "%", PDO::PARAM_STR);
                $newsSearch->execute();

                $resultsFound = $newsSearch->rowCount();

                while ($foundNews = $newsSearch->fetch()) {

                    $news = new news($foundNews["id"]);

                    $newsArray[] = $news->returnArray();

                }

            } catch (PDOException $e) {

                die("Failed to fetch the search results.");

            }

        } else {

            $searchType = "forums";

            try {

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

                        $ra[] = $foundThread["id"];

                    }

                }

                while ($foundPost = $postSearch->fetch()) {

                    if (!in_array($foundPost["threadid"], $ra)) {

                        $ra[] = $foundPost["threadid"];

                    }

                }

            } catch (PDOException $e) {

                die("Failed to fetch the search results.");

            }

            if (!empty($ra)) {

                $selectThreads = $con->query("SELECT `forumthreads`.`id` FROM `forumthreads` WHERE `forumthreads`.`forumcategory` <> 0 AND `forumthreads`.`id` IN (" . implode(',', array_map('intval', $ra)) . ") ORDER BY `forumthreads`.`id` DESC");

                $resultsFound = $selectThreads->rowCount();

                while ($foundThreads = $selectThreads->fetch()) {

                    $thread = new forumthread($foundThreads["id"]);
                    $threadArray[] = $thread->returnArray();

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

            } else {

                $resultsFound = 0;

            }

        }

    }

}

echo $twig->render("search.html", array("categories" => $categories, "news" => $newsArray, "resultbutton" => resultbutton(), "resultsfound" => $resultsFound, "searchtype" => $searchType, "term" => $term, "termlen" => $termlen, "threads" => $threadArray));

function resultbutton() {

    global $resultsFound;
    global $searchType;
    global $term;

    $sss = ($resultsFound == 1) ? "" : "s";
    $name = ($searchType == "news") ? "inf" : "inn";
    $prettyname = ($name == "inf") ? "article" : "forum post";
    return "<form method='post' action='/search/" . $term . "/'><input type='hidden' value='" . $term . "' name='searchb'><input class='search-type' value='" . $prettyname . $sss . "' type='submit' name='" . $name . "'></form>";

}
