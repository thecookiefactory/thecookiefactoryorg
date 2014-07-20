<?php

if (!isset($r_c)) header("Location: /notfound.php");

include_once "analyticstracking.php";
require_once "inc/classes/map.class.php";

$maps = array();

$_SESSION["lp"] = "maps";

?>

<script src='/js/maps.js'></script>

<?php

try {

    $selectMaps = $con->query("SELECT `maps`.`id` FROM `maps` ORDER BY `maps`.`id` DESC");

    while ($foundMap = $selectMaps->fetch()) {

        $map = new map($foundMap["id"]);

        $maps[] = $map->returnArray();

    }

    echo $twig->render("maps.html", array("maps" => $maps));

} catch (PDOException $e) {

    echo "An error occurred while trying to fetch the maps.";

}
