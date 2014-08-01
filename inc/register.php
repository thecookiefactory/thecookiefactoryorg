<?php

if (!isset($r_c)) header("Location: /notfound.php");


if (isset($_POST["submit"])) {

    $username = $_POST["username"];

    $user->register($username);

}

echo $twig->render("register.html", array("index_var" => $index_var, "sessionisset" => isset($_SESSION["steamid"]), "isloggedin" => $user->isReal()));
