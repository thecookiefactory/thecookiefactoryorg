<?php

session_start();

$r_c = 1;
require_once "../inc/functions.php";
require_once "../classes/user.class.php";

$user = new user((isset($_SESSION["userid"]) ? $_SESSION["userid"] : null));

if (!$user->isAdmin()) die("403");

$twig = twigInit();

try {

    $selectUsers = $con->query("SELECT `users`.`id` FROM `users`");

    $users = array();

    while ($userData = $selectUsers->fetch()) {

        $aUser = new user($userData["id"]);
        $users[] = array("id" => $aUser->getId(), "name" => $aUser->getName());

    }

} catch (PDOException $e) {

    die("Failed to fetch users.");

}

echo $twig->render("admin/users.html", array("users" => $users));
