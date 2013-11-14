<?php

if (!isset($r_c)) header("Location: /notfound.php");

include_once "analyticstracking.php";
require_once "inc/classes/map.class.php";
require_once "inc/markdown/markdown.php";

$_SESSION["lp"] = $p;

?>

<script src='/js/maps.js'></script>

<?php

$q = $con->query("SELECT `maps`.`id` FROM `maps` ORDER BY `maps`.`id` DESC");

if ($q->rowCount() != 0) {

    $iii = 0;

    while ($r = $q->fetch()) {

        $iii++;

        $map = new map($r["id"]);

        echo $map->display();

        if ($iii == 1) {

            ?>
            <div class='map-ad'>
                  <script async src='//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js'></script>
                  <!-- Maps Inline -->
                  <ins class='adsbygoogle'
                       style='display:inline-block;width:728px;height:90px'
                       data-ad-client='ca-pub-8578399795841431'
                       data-ad-slot='8918199475'></ins>
                  <script>
                  (adsbygoogle = window.adsbygoogle || []).push({});
                  </script>
            </div>
            <?php

        }
    }

} else {

    echo "The are no maps.";

}
