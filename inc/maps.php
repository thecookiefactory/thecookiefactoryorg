<?php

checkembed();
include "analyticstracking.php";

  echo "<script src='js/maps.js'></script>";

  $q = mysql_query("SELECT * FROM `maps` ORDER BY `id` DESC");

  while ($r = mysql_fetch_assoc($q)) {
	$gq = mysql_query("SELECT * FROM `gallery` WHERE `mapid`=".$r["id"]);
    echo "<div class='map-name'>".$r["name"]."</div>";
    echo "<div class='map-container'>";
      echo "<div class='map-leftarrow map-arrow-disabled' id='map-".$r["id"]."-left' onclick='startImagerollScrolling(this.id, -1);'></div>";
      echo "<div class='map-rightarrow map-arrow-disabled' id='map-".$r["id"]."-right' onclick='startImagerollScrolling(this.id, 1);'></div>";
      echo "<div class='map-imageroll' id='map-".$r["id"]."' onload='initialize(this.id);'>";
      echo "<script type='text/javascript'> lendict[\"map-".$r["id"]."\"] = ".(mysql_num_rows($gq)+1)."; initialize(\"map-".$r["id"]."\");</script>";
      
    //display the main image
        echo "<div class='map-image'>";
          echo "<img class='map-image' src='img/maps/".$r["id"].".".$r["ext"]."'>";
        echo "</div>";
    
    //display additional images
    while ($gr = mysql_fetch_assoc($gq)) {
    echo "<div class='map-image'>";
          echo "<img class='map-image' src='img/maps/".$r["id"]."/".$gr["filename"]."' title='".$gr["desc"]."'>";
        echo "</div>";
    }
    
      echo "</div>";
      echo "<div class='map-data'>";
        echo "<span class='map-author'>".$r["author"]."</span>";
        echo "<span class='map-game'>";
                switch($r["game"]) {
                  case 1: echo "Team Fortress 2"; break;
                  case 2: echo "Portal 2"; break;
                }
        echo "</span>";
        echo "<span class='map-desc'>".nl2br($r["desc"])."</span>";
        echo "<span class='map-dl'><a href='".$r["dl"]."' target='_blank'>DOWNLOAD</a></span>";
      echo "</div>";
	  
	  //comments
	  $cq = mysql_query("SELECT * FROM `mapscomments` WHERE `mapid`=".$r["id"]) or die(mysql_error());
	  echo "<div class='comments'>";
		if (mysql_num_rows($cq) > 0) {
		  while ($cr = mysql_fetch_assoc($cq)) {
	  	    echo $cr["author"]." said on ".$cr["date"].": <p>".nl2br($cr["text"])."</p>";
		  }
		} else {
		  echo "no comments yet";
		}
	  if (checkuser()) {
	    echo "<form>";
	    echo "<textarea></textarea>";
	    echo "<input type='submit'>";
	    echo "</form>";
	  } else {
	    echo "you have to be logged in to post comments";
	  }
	  echo "</div>";
    echo "</div>";

  }

?>
