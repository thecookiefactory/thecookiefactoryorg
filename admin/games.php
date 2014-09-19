<?php

session_start();

$r_c = 1;
require_once '../inc/functions.php';
require_once '../classes/game.class.php';
require_once '../classes/user.class.php';

$user = new user((isset($_SESSION['userid']) ? $_SESSION['userid'] : null));

if (!$user->isAdmin()) die('403');

$twig = twigInit();

try {

    $selectGames = $con->prepare('SELECT `games`.`id` FROM `games`');

} catch (PDOException $e) {

    die('Failed to fetch games.');

}

$selectGames->execute();

if (isset($_POST['update'])) {

    while ($gameData = $selectGames->fetch()) {

        $id = $gameData['id'];

        $game = new game($id);
        $game->update($_POST[$id . 'name'], $_POST[$id . 'steamid']);

    }

}

if (isset($_POST['addnew'])) {

    $game = new game();
    $game->create($_POST['name'], $_POST['steamid']);

}

$selectGames->execute();

$games = array();

while ($gameData = $selectGames->fetch()) {

    $game = new game($gameData['id']);
    $games[] = $game->returnArray();

}

echo $twig->render('admin/games.html', array('games' => $games));
