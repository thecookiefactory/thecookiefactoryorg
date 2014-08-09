<?php

if (!isset($r_c)) header("Location: /notfound.php");

require_once "classes/map.class.php";

$maps = array();

$_SESSION["lp"] = "maps";

try {

    $selectMaps = $con->query("SELECT `maps`.`id` FROM `maps` ORDER BY `maps`.`id` DESC");

    while ($foundMap = $selectMaps->fetch()) {

        $map = new map($foundMap["id"]);

        $maps[] = $map->returnArray();

    }

    echo $twig->render("maps.html", array("index_var" => $index_var, "maps" => $maps));

} catch (PDOException $e) {

    echo "An error occurred while trying to fetch the maps.";

}
