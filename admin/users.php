<?php

session_start();

$r_c = 1;
require_once "../inc/functions.php";
require_once "../inc/classes/user.class.php";

$user = new user((isset($_SESSION["userid"]) ? $_SESSION["userid"] : null));

if (!$user->isAdmin()) die("403");

$twig = twigInit();

$q = $con->query("SELECT `users`.`id` FROM `users`");

$users = array();

while ($r = $q->fetch()) {

    $u = new user($r["id"]);
    $users[] = $u->getName();

}

echo $twig->render("admin/users.html", array("users" => $users));
