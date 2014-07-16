<?php

session_start();

$r_c = 1;
require_once "../inc/functions.php";
require_once "../inc/classes/user.class.php";

$user = new user((isset($_SESSION["userid"]) ? $_SESSION["userid"] : null));

if (!$user->isAdmin()) die("403");

$twig = twigInit();

try {

    $selectUsers = $con->query("SELECT `users`.`id` FROM `users`");

    $users = array();

    while ($userData = $selectUsers->fetch()) {

        $aUser = new user($userData["id"]);
        $users[] = $aUser->getName();

    }

} catch (PDOException $e) {

    die("Query failed.");

}

echo $twig->render("admin/users.html", array("users" => $users));
