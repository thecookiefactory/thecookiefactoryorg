<?php

session_start();

$r_c = 1;
require_once '../inc/functions.php';
require_once '../classes/about.class.php';
require_once '../classes/game.class.php';
require_once '../classes/user.class.php';

$user = new user((isset($_SESSION['userid']) ? $_SESSION['userid'] : null));

if (!$user->isAdmin()) die('403');

$twig = twigInit();

$userAbout = new about($user->getId());

$groupAbout = new about(1);

if (isset($_POST['submit'])) {

    $status = $userAbout->update($_POST['description'], array(
        'website' => $_POST['website'],
        'email'   => $_POST['email'],
        'github'  => $_POST['github'],
        'twitter' => $_POST['twitter'],
        'twitch'  => $_POST['twitch'],
        'youtube' => $_POST['youtube'],
        'steam'   => $_POST['steam'],
        'reddit'  => $_POST['reddit']
    ));

}

if (isset($_POST['submitdesc'])) {

    $status = $groupAbout->update($_POST['description']);

}

$aboutdata = $userAbout->returnArray();

$aboutdata['groupdesc'] = $groupAbout->returnArray()['description'];

if (isset($status)) {

    $aboutdata['status'] = $status;

}

echo $twig->render('admin/about.html', $aboutdata);
