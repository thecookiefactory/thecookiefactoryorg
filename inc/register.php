<?php

if (!isset($r_c)) header("Location: /notfound.php");

include_once "analyticstracking.php";

if (isset($_POST["submit"])) {

    $username = $_POST["username"];

    $user->register($username);

}

echo $twig->render("register.html", array("sessionisset" => isset($_SESSION["steamid"]), "isloggedin" => $user->isReal()));
