<?php

if (!isset($r_c)) header("Location: /notfound.php");

include_once "analyticstracking.php";
require_once "inc/classes/about.class.php";

$abouts = array();

$_SESSION["lp"] = $p;

try {

    $selectAbout = $con->query("SELECT `users`.`id` FROM `users` WHERE `users`.`admin` = 1 ORDER BY `users`.`name` ASC");

    while ($foundUsers = $selectAbout->fetch()) {

        $about = new about($foundUsers["id"]);

        $abouts[] = $about->returnArray();

    }

    echo $twig->render("about.html", array("abouts" => $abouts));

} catch (PDOException $e) {

    echo "An error occurred while trying to fetch data.";

}
