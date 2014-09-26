<?php

session_start();

$r_c = 1;
require_once '../inc/functions.php';
require_once '../classes/forumthread.class.php';
require_once '../classes/user.class.php';

$user = new user((isset($_SESSION['userid']) ? $_SESSION['userid'] : null));

if (!$user->isAdmin()) die('403');

$twig = twigInit();

$action = $_GET['action'];

if (isset($action) && ($action == 'edit' || $action == 'delete' || $action == 'write')) {

    if ($action == 'edit') {

        $mode = 'edit';

        $status = 'edit';

        $id = strip($_GET['id']);

        $map = new map($id);

        if (isset($_POST['submit']) && validField($_POST['name']) && validField($_POST['game']) && validField($_POST['text'])) {

            $map->update($_POST['name'], $_POST['authorid'], $_POST['gameid'], $_POST['text'], $_POST['download'], $_POST['link']);

            if (isset($_POST['topicname']) && validField($_POST['topicname']) && validField($_POST['topiccat']) && validField($_POST['topictext'])) {

                $authorid = $user->getId();
                $title = strip($_POST['topicname']);
                $text = strip($_POST['topictext']);
                $cat = strip($_POST['topiccat']);

                try {

                    $createThread = $con->prepare('INSERT INTO `forumthreads` VALUES(DEFAULT, :title, :text, :authorid, DEFAULT, DEFAULT, DEFAULT, :cat, :id, DEFAULT, 0)');
                    $createThread->bindValue('authorid', $authorid, PDO::PARAM_INT);
                    $createThread->bindValue('title', $title, PDO::PARAM_STR);
                    $createThread->bindValue('text', $text, PDO::PARAM_STR);
                    $createThread->bindValue('cat', $cat, PDO::PARAM_INT);
                    $createThread->bindValue('id', $id, PDO::PARAM_INT);
                    $createThread->execute();

                } catch (PDOException $e) {

                    die('Query failed.');

                }

                try {

                    $enableComments = $con->prepare('UPDATE `maps` SET `maps`.`comments` = 1 WHERE `maps`.`id` = :id');
                    $enableComments->bindValue('id', $id, PDO::PARAM_INT);
                    $enableComments->execute();

                } catch (PDOException $e) {

                    die('Query failed.');

                }

            }

        }

        if ($selectMapId->rowCount() == 1) {

            try {

                //fetching the current data
                $selectMapData = $con->prepare('SELECT `maps`.`id`, `maps`.`name`, `maps`.`text`, `maps`.`authorid`, `maps`.`dl`, `maps`.`link`, `maps`.`comments`, `maps`.`gameid` FROM `maps` WHERE `maps`.`id` = :id');
                $selectMapData->bindValue('id', $id, PDO::PARAM_INT);
                $selectMapData->execute();

                $mapdata = $selectMapData->fetch();

            } catch (PDOException $e) {

                die('Query failed.');

            }

            try {

                $selectGame = $con->query('SELECT `games`.`id`, `games`.`name` FROM `games` ORDER BY `games`.`id` ASC');

                $games = array();

                while ($gameData = $selectGame->fetch()) {

                    $games[] = array('id' => $gameData['id'], 'name' => $gameData['name']);

                }

            } catch (PDOException $e) {

                die('Query failed.');

            }

            if ($mapdata['comments'] == 0) {

                try {

                    $selectCategories = $con->query('SELECT `forumcategories`.`id`, `forumcategories`.`name` FROM `forumcategories` ORDER BY `forumcategories`.`name` ASC');

                    $forumcategories = array();

                    while ($categoryData = $selectCategories->fetch()) {

                        $forumcategories[] = array('id' => $categoryData['id'], 'name' => $categoryData['name']);

                    }

                } catch (PDOException $e) {

                    die('Query failed.');

                }

            }

        } else {

            $status = 'notfound';

        }

    } else if ($action == 'delete') {

        $mode = 'delete';

        if (isset($_GET['id']) && is_numeric($_GET['id'])) {

            $id = strip($_GET['id']);
            $currentid = $id;

            $map = new map($id);

            if (isset($_POST['delete'])) {

                $map->delete();

                $status = 'success';

            } else {

                $status = 'confirm';

            }

        } else {

            $status = 'notfound';

        }

    } else {

        $mode = 'write';

        if (isset($_POST['submit']) && validField($_POST['name']) && validField($_POST['game']) && validField($_POST['text'])) {

            //basic values
            $name = strip($_POST['name']);
            $author = $user->getId();
            $game = strip($_POST['game']);
            $text = strip($_POST['text'], true);
            $download = strip($_POST['download']);

            if (isset($_POST['topicname']) && validField($_POST['topicname']) && validField($_POST['topiccat']) && validField($_POST['topictext'])) {

                $comments = 1;

                $status = 'topic';

            } else {

                $comments = 0;

                $status = 'no-topic';

            }

            $map = new map();

            $map->create();

            if (isset($_POST['topicname']) && validField($_POST['topicname']) && validField($_POST['topiccat']) && validField($_POST['topictext'])) {

                $authorid = $user->getId();
                $title = strip($_POST['topicname']);
                $text = strip($_POST['topictext']);
                $cat = strip($_POST['topiccat']);

                try {

                    $createThread = $con->prepare('INSERT INTO `forumthreads` VALUES(DEFAULT, :title, :text, :authorid, DEFAULT, DEFAULT, DEFAULT, :cat, :id, DEFAULT, 0)');
                    $createThread->bindValue('authorid', $authorid, PDO::PARAM_INT);
                    $createThread->bindValue('title', $title, PDO::PARAM_STR);
                    $createThread->bindValue('text', $text, PDO::PARAM_STR);
                    $createThread->bindValue('cat', $cat, PDO::PARAM_INT);
                    $createThread->bindValue('id', $id, PDO::PARAM_INT);
                    $createThread->execute();

                } catch (PDOException $e) {

                    die('Query failed.');

                }

            }

            $status .= '-success';

        } else {

            $status = 'progress';

            try {

                $selectGame = $con->query('SELECT `games`.`id`, `games`.`name` FROM `games` ORDER BY `games`.`id` ASC');

                $games = array();

                while ($gameData = $selectGame->fetch()) {

                    $games[] = array('id' => $gameData['id'], 'name' => $gameData['name']);

                }

            } catch (PDOException $e) {

                die('Query failed.');

            }

            try {

                $selectCategories = $con->query('SELECT `forumcategories`.`id`, `forumcategories`.`name` FROM `forumcategories` ORDER BY `forumcategories`.`name` ASC');

                $forumcategories = array();

                while ($categoryData = $selectCategories->fetch()) {

                    $forumcategories[] = array('id' => $categoryData['id'], 'name' => $categoryData['name']);

                }

            } catch (PDOException $e) {

                die('Query failed.');

            }

        }

    }

} else {

    $mode = 'manage';

    try {

        $selectMapData = $con->query('SELECT `maps`.`id`, `maps`.`name` FROM `maps` ORDER BY `maps`.`id` DESC');

        $maps = array();

        while ($mapdata = $selectMapData->fetch()) {

            $maps[] = array('id' => $mapdata['id'], 'name' => $mapdata['name']);

        }

    } catch (PDOException $e) {

        die('Query failed.');

    }

}

echo $twig->render(
    'admin/maps.html',
    array(
        'mode' => $mode,
        'status' => (isset($status) ? $status : null),
        'mapdata' => (isset($mapdata) ? $mapdata : null),
        'currentid' => (isset($currentid) ? $currentid : null),
        'maps' => (isset($maps) ? $maps : null),
        'games' => (isset($games) ? $games : null),
        'forumcategories' => (isset($forumcategories) ? $forumcategories : null),
    )
);
