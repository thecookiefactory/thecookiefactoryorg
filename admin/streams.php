<?php
session_start();
$r_c = 42;
require "../inc/essential.php";

if (!checkadmin())
	die("must be an dmin :(".$_SESSION["username"]);
?>

<!doctype html>
<html>
<body>
<h1>manage your stream</h1>

<?php

if (isset($_POST["submit"])) {
	$twitch = $_POST["twitch"];
	$desc = mysql_real_escape_string($_POST["description"]);
	$uq = mysql_query("UPDATE streams SET twitch='".$twitch."', description='".$desc."' WHERE author='".$_SESSION["username"]."'") or die(mysql_error());
}

$sq = mysql_query("SELECT * FROM streams WHERE author='".$_SESSION["username"]."'");
$sr = mysql_fetch_assoc($sq);

echo "<form action='streams.php' method='post'>
twitch usernme
<input type='text' name='twitch' value='".$sr["twitch"]."' /><br />
description
<textarea name='description' rows='7' cols='50'>".$sr["description"]."</textarea><br />
<input type='submit' name='submit' />
</form>";

?>
</body>
</html>