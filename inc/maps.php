<?php

if (!isset($r_c)) header("Location: /notfound.php");

include_once "analyticstracking.php";
require_once "inc/classes/map.class.php";
require_once "inc/markdown/markdown.php";
require_once "h2o/h2o.php";

$h2o = new h2o("inc/templates/maps.html");

$maps = array();

$_SESSION["lp"] = $p;

?>

<script src='/js/maps.js'></script>

<?php

try {

    $selectMaps = $con->query("SELECT `maps`.`id` FROM `maps` ORDER BY `maps`.`id` DESC");

    if ($selectMaps->rowCount() != 0) {

        while ($foundMap = $selectMaps->fetch()) {

            $map = new map($foundMap["id"]);

            array_push($maps, $map->returnArray());

        }

        echo $h2o->render(compact('maps'));

    } else {

        echo "The are no maps.";

    }

} catch (PDOException $e) {

    echo "An error occurred while trying to fetch the maps.";

}
