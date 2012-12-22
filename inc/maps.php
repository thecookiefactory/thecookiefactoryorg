<?php

checkembed($r_c);
include "analyticstracking.php";

if (isset($_POST["submit"]) && strip($_POST["text"]) != "") {
    
    $text = strip($_POST["text"]);
    $m_id = strip($_POST["m_id"]);
    $userid = $_SESSION["userid"];
    $dt = time();
    
    mysqli_query($con, "INSERT INTO `mapscomments` VALUES('','".$userid."','".$text."','".$dt."','".$m_id."')");
    
}

echo "<script src='js/maps.js'></script>";

$q = mysqli_query($con, "SELECT * FROM `maps` ORDER BY `id` DESC");

while ($r = mysqli_fetch_assoc($q)) {

    $gq = mysqli_query($con, "SELECT * FROM `gallery` WHERE `mapid`=".$r["id"]);
    echo "<div class='map-name'>".$r["name"]."</div>";
    echo "<div class='map-container'>";
      echo "<div class='map-leftarrow map-arrow-disabled' id='map-".$r["id"]."-left' onclick='startImagerollScrolling(this.id, -1);'></div>";
      echo "<div class='map-rightarrow map-arrow-disabled' id='map-".$r["id"]."-right' onclick='startImagerollScrolling(this.id, 1);'></div>";
      echo "<div class='map-imageroll' id='map-".$r["id"]."' onload='initialize(this.id);'>";
      echo "<script type='text/javascript'> lendict[\"map-".$r["id"]."\"] = ".(mysqli_num_rows($gq)+1)."; initialize(\"map-".$r["id"]."\");</script>";
      
    //display the main image
        echo "<div class='map-image'>";
          echo "<img class='map-image' alt='".$r["name"]."' src='img/maps/".$r["id"].".".$r["ext"]."'>";
        echo "</div>";
    
    //display additional images
    while ($gr = mysqli_fetch_assoc($gq)) {
    echo "<div class='map-image'>";
          echo "<img class='map-image' src='img/maps/".$r["id"]."/".$gr["filename"]."' alt='".$gr["desc"]."' title='".$gr["desc"]."'>";
        echo "</div>";
    }
    
      echo "</div>";
      echo "<div class='map-data'>";
        echo "<span class='map-author'>".getname($r["authorid"])."</span>";
        echo "<span class='map-game'>";
            $gq = mysqli_query($con, "SELECT * FROM `games` WHERE `id`=".$r["gameid"]);
            $gr = mysqli_fetch_assoc($gq);
            echo "<a target='_blank' href='http://steamcommunity.com/app/".$gr["steam"]."'>".$gr["name"]."</a>";
        echo "</span>";
        echo "<span class='map-desc'>".nl2br($r["desc"], false)."</span>";
        echo "<span class='map-dl'>";
        switch ($r["dltype"]) {
            case 0: echo "<a href='".$r["dl"]."' target='_blank'>DOWNLOAD</a>"; break;
            case 1: echo "<a href='http://steamcommunity.com/sharedfiles/filedetails/?id=".$r["dl"]."' target='_blank'>DOWNLOAD</a>"; break;
            case 2: echo "No dowload available yet."; break;
        }
        echo "</span>";
      echo "</div>";
      
      //comments
      $cq = mysqli_query($con, "SELECT * FROM `mapscomments` WHERE `mapid`=".$r["id"]) or die(mysqli_error());
      echo "<div class='comments'>";
        if (mysqli_num_rows($cq) > 0) {
          while ($cr = mysqli_fetch_assoc($cq)) {
            echo getname($cr["authorid"])." said on ".displaydate($cr["dt"]).": <p>".nl2br($cr["text"], false)."</p>";
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