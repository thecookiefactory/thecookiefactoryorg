<?php

checkembed();
include "analyticstracking.php";

if (isset($_GET["id"])) {
echo "display map id: ".$_GET["id"];
} else {
$q = mysql_query("SELECT * FROM maps");

while ($r = mysql_fetch_assoc($q)) {
echo "<div class='map' style='background-image: url(img/maps/".$r["name"].".jpg);'>";
echo $r["name"];
echo $r["author"];
switch($r["game"]) {
case 1: echo "Team Fortress 2"; break;
case 2: echo "Portal 2"; break;
}
echo $r["desc"];
echo "<a href='".$r["dl"]."' target='_blank'>DOWNLOAD</a>";
//echo $r["rating"]/$r["ratecount"];
echo "</div>";

}

}

?>