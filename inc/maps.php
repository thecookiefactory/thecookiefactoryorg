<?php

checkembed();
include "analyticstracking.php";

if (isset($_GET["id"])) {
  echo "display map id: ".$_GET["id"];
} else {
  $q = mysql_query("SELECT * FROM maps");

  while ($r = mysql_fetch_assoc($q)) {
    echo "<div class='map-container'>";
      echo "<div class='map-imageroll' id='map-".$r["id"]."' onclick='mapsAnimation(this.id);'>";
        echo "<div class='map-image'>";
          echo "<img class='map-image' src='img/maps/cp_1.jpg'>";
        echo "</div>";
        echo "<div class='map-image'>";
          echo "<img class='map-image' src='img/maps/mp_coop_wat.jpg'>";
        echo "</div>";
        echo "<div class='map-image'>";
          echo "<img class='map-image' src='img/maps/cp_1.jpg'>";
        echo "</div>";
      echo "</div>";
      echo "<div class='map-data'>";
        echo "<span class='map-name'>".$r["name"]."</span>";
        echo "<span class='map-author'>".$r["author"]."</span>";
        echo "<span class='map-game'>";
                switch($r["game"]) {
                  case 1: echo "Team Fortress 2"; break;
                  case 2: echo "Portal 2"; break;
                }
        echo "</span>";
        echo "<span class='map-desc'>".$r["desc"]."</span>";
        echo "<span class='map-dl'><a href='".$r["dl"]."' target='_blank'>DOWNLOAD</a></span>";
      echo "</div>";
    echo "</div>";

  }

}

?>