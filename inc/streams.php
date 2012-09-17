<?php

$q = mysql_query("SELECT * FROM streams");

echo "<ul id='stream-menu'>";

while ($r = mysql_fetch_assoc($q)) {

echo "<a href='?p=streams&streamid=".$r["id"]."'>";
if (isset($_GET["streamid"]) && $r["id"] == $_GET["streamid"])
echo "<li class='stream-button-selected'>".$r["author"]."</li>";
else
echo "<li class='stream-button'>".$r["author"]."</li>";
echo "</a>";

}

echo "</ul>";


if (isset($_GET["streamid"]) && is_numeric($_GET["streamid"])) {
 // DISPLAY A STREAM
 $q = mysql_query("SELECT * FROM streams WHERE id=".$_GET["streamid"]);
 
 if (mysql_num_rows($q) == 1) {
 
 $r = mysql_fetch_assoc($q);
 echo "<h1>".$r["author"]."'s stream</h1>";
 streamo($r["twitch"]);
 echo "<p>".nl2br($r["description"])."</p>";
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

?>