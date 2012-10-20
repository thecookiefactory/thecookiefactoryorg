<?php

checkembed();
include "analyticstracking.php";

if (isset($_GET["id"])) {
echo "display map id: ".$_GET["id"];
} else {
$q = mysql_query("SELECT * FROM maps");

while ($r = mysql_fetch_assoc($q)) {
echo "<div class='map-div' style='background-image: url(img/maps/".$r["name"].".jpg);'>";
echo "<span class='map-name'>".$r["name"]."</span>";
echo "<span class='map-author'>".$r["author"]."</span>"."<span class='map-game'>";
switch($r["game"]) {
case 1: echo "Team Fortress 2"; break;
case 2: echo "Portal 2"; break;
}
echo "</span>"."<span class='map-desc'>".$r["desc"]."</span>";
echo "<span class='map-dl'><a href='".$r["dl"]."' target='_blank'>DOWNLOAD</a></span>";
//echo $r["rating"]/$r["ratecount"];
echo "</div>";

// images
if ($r["gallery"] == 1) {
$iq = mysql_query("SELECT * FROM ".$r["name"]." ORDER BY id ASC");
while ($ir = mysql_fetch_assoc($iq)) {
echo "<a href='img/maps/".$r["name"]."/".$ir["id"].".jpg' title='".$ir["desc"]."' rel='lightbox[".$r["name"]."]'><img src='img/maps/".$r["name"]."/thumbs/".$ir["id"].".jpg' /></a>";
}
}

}

}

?>