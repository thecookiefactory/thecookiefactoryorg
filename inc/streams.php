<?php

checkembed($r_c);
include "analyticstracking.php";

$q = mysqli_query($con, "SELECT * FROM `streams` WHERE `active`=1");

echo "<ul id='stream-menu'>";

while ($r = mysqli_fetch_assoc($q)) {
    echo "<a href='?p=streams&amp;streamid=".$r["id"]."'>";
if (isset($_GET["streamid"]) && $r["id"] == $_GET["streamid"]) {
    echo "<li class='stream-button stream-button-selected";
    if (islive($r["twitch"]))
    echo " stream-live";
}
else {
echo "<li class='stream-button";
if (islive($r["twitch"]))
echo " stream-live";
}

echo "'>".$r["author"]."</li>";
echo "</a>";

}

echo "</ul>";


if (isset($_GET["streamid"]) && is_numeric($_GET["streamid"])) {
// DISPLAY A STREAM
$q = mysqli_query($con, "SELECT * FROM `streams` WHERE `id`=".$_GET["streamid"]);

if (mysqli_num_rows($q) == 1) {

$r = mysqli_fetch_assoc($q);
echo "<h1>".$r["author"]."'s stream</h1>";
streamo($r["twitch"]);
echo "<p>".nl2br($r["description"], false)."</p>";
} else {
echo "<p>Something went wrong.</p>";
}
}

function streamo($x) {
echo "<object type='application/x-shockwave-flash' height='378' width='620' id='live_embed_player_flash' data='http://www.twitch.tv/widgets/live_embed_player.swf?channel=".$x."' bgcolor='#000000'>
<param name='allowFullScreen' value='true' />
<param name='allowScriptAccess' value='always' />
<param name='allowNetworking' value='all' />
<param name='movie' value='http://www.twitch.tv/widgets/live_embed_player.swf' />
<param name='flashvars' value='hostname=www.twitch.tv&channel=".$x."&auto_play=true&start_volume=25' />
</object>";
}

function islive($x) {
$json_file = @file_get_contents("http://api.justin.tv/api/stream/list.json?channel=$x", 0, null, null);
$json_array = json_decode($json_file, true);
if (empty($json_array)) {
return false;
}

if ($json_array[0]['name'] == "live_user_$x") {
return true;
} else {
return false;
}
}

?>