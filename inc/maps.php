<?php

checkembed();
include "analyticstracking.php";

  $q = mysql_query("SELECT * FROM maps");

  while ($r = mysql_fetch_assoc($q)) {
    echo "<div class='map-container'>";
      echo "<div class='map-leftarrow' id='map-".$r["id"]."' onclick='startAnimation(this.id, -1);'></div>";
      echo "<div class='map-rightarrow' id='map-".$r["id"]."' onclick='startAnimation(this.id, 1);'></div>";
      echo "<div class='map-imageroll' id='map-".$r["id"]."' onclick='startAnimation(this.id);'>";
      
    //display the main image
        echo "<div class='map-image'>";
          echo "<img class='map-image' src='img/maps/".$r["name"].".jpg'>";
        echo "</div>";
    
    //display additional images
    if ($r["gallery"] == 1) {
      $gq = mysql_query("SELECT * FROM ".$r["name"]);
      while ($gr = mysql_fetch_assoc($gq)) {
        echo "<div class='map-image'>";
          echo "<img class='map-image' src='img/maps/".$r["name"]."/".$gr["id"].".jpg' title='".$gr["desc"]."'>";
        echo "</div>";
      }
    }
    
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

?>