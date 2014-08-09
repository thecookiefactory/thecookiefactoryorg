<?php

if (!isset($r_c)) header("Location: /notfound.php");

require_once "classes/about.class.php";

$abouts = array();

$_SESSION["lp"] = $p;

try {

    $selectdesc = $con->query("SELECT `about`.`description` FROM `about` WHERE `about`.`userid` = 1");
    $desc = $selectdesc->fetch();

    $description = $desc["description"];

} catch (PDOException $e) {

    die("Failed to execute the query.");

}

try {

    $selectAbout = $con->query("SELECT `users`.`id` FROM `users` WHERE `users`.`admin` = 1 AND `users`.`id` <> 1 ORDER BY `users`.`name` ASC");

    while ($foundUsers = $selectAbout->fetch()) {

        $about = new about($foundUsers["id"]);

        $abouts[] = $about->returnArray();

    }

    echo $twig->render("about.html", array("index_var" => $index_var, "description" => $description, "abouts" => $abouts));

} catch (PDOException $e) {

    echo "An error occurred while trying to fetch data.";

}
