<?php
session_start();
$r_c = 42;
require "../inc/essential.php";

if (!checkadmin())
	die("must be an dmin :(".$_SESSION["userid"]);
?>

<!doctype html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
</head>
<body>
<h1>manage your stream</h1>

<?php

if (isset($_POST["submit"])) {
	$twitch = $_POST["twitch"];
	$desc = mysql_real_escape_string($_POST["description"]);
	
	if (isset($_POST["active"]) && $_POST["active"] == "on")
		$active = 1;
	else
		$active = 0;
		
	$uq = mysql_query("UPDATE `streams` SET `twitch`='".$twitch."', `description`='".$desc."', `active`='".$active."' WHERE `author`='".$_SESSION["userid"]."'") or die(mysql_error());
}

$sq = mysql_query("SELECT * FROM `streams` WHERE `author`='".$_SESSION["userid"]."'");
if (mysql_num_rows($sq) == 0) {
	echo "Your stream page is being created now...";
	$cq = mysql_query("INSERT INTO `streams` VALUES('','".$_SESSION["userid"]."','','','0')");
	echo "Done. Please fill out the fields below.";
}
$sq = mysql_query("SELECT * FROM `streams` WHERE `author`='".$_SESSION["userid"]."'");
$sr = mysql_fetch_assoc($sq);

echo "<form action='streams.php' method='post'>
twitch.tv username:<br>
<input type='text' name='twitch' value='".$sr["twitch"]."' required /><br>
description:<br>
<textarea name='description' rows='7' cols='50' required>".$sr["description"]."</textarea><br>
Active stream <input type='checkbox' name='active' ";
	
if ($sr["active"] == 1)
	echo "checked ";
	
echo "/> (there is a chance your stream will be live sometime soon)<br>
<input type='submit' name='submit'>
</form>";

?>
</body>
</html>