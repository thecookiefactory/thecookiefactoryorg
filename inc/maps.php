<?php

checkembed($r_c);
include "analyticstracking.php";

  if (isset($_POST["submit"]) && trim($_POST["text"]) != "") {
    $text = mysql_real_escape_string($_POST["text"]);
	$m_id = mysql_real_escape_string($_POST["m_id"]);
	$username = $_SESSION["username"];
	$date = date("Y-m-d");
	$time = date("H:i");
	
	mysql_query("INSERT INTO `mapscomments` VALUES('','".$username."','".$text."','".$date."','".$time."','".$m_id."')");
  }

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
          echo "<img class='map-image' alt='".$r["name"]."' src='img/maps/".$r["id"].".".$r["ext"]."'>";
        echo "</div>";
    
    //display additional images
    while ($gr = mysql_fetch_assoc($gq)) {
    echo "<div class='map-image'>";
          echo "<img class='map-image' src='img/maps/".$r["id"]."/".$gr["filename"]."' alt='".$gr["desc"]."' title='".$gr["desc"]."'>";
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
        echo "<span class='map-dl'>";
		switch ($r["dltype"]) {
			case 0: echo "<a href='img/maps/".$r["dl"]."' target='_blank'>DOWNLOAD</a>"; break;
			case 1: echo "<a href='http://steamcommunity.com/sharedfiles/filedetails/?id=".$r["dl"]."' target='_blank'>DOWNLOAD</a>"; break;
			case 2: echo "No dowload available yet."; break;
		}
		echo "</span>";
      echo "</div>";
	  
	  //comments
	  $cq = mysql_query("SELECT * FROM `mapscomments` WHERE `mapid`=".$r["id"]) or die(mysql_error());
	  echo "<div class='comments'>";
		if (mysql_num_rows($cq) > 0) {
		  while ($cr = mysql_fetch_assoc($cq)) {
	  	    echo $cr["username"]." said on ".$cr["date"].": <p>".nl2br($cr["text"])."</p>";
		  }
		} else {
		  echo "no comments yet";
		}
	  if (checkuser()) {
	    echo "<form action='?p=maps' method='post'>";
	    echo "<textarea name='text' required></textarea>";
		echo "<input type='hidden' name='m_id' value='".$r["id"]."'>";
	    echo "<input type='submit' name='submit'>";
	    echo "</form>";
	  } else {
	    echo "you have to be logged in to post comments";
	  }
	  echo "</div>";
    echo "</div>";

  }

?>
