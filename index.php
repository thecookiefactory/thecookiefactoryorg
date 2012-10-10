<?php
session_start();
$r_c = 42;
require "inc/essential.php";
?>

<!doctype html>
<html>
<head>

	<title>thecookiefactory.org</title>
	<meta http-equiv='Content-Type' content='text/html; charset=windows-1252' />
	<link rel='stylesheet' type='text/css' href='base.css' />
	<link rel='stylesheet' type='text/css' href='lightbox.css' />
	<link rel='stylesheet' type='text/css' href='http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700' />
	<script src='js/main.js'></script>
	<script src='js/jquery-1.7.2.min.js'></script>
	<script src='js/lightbox.js'></script>
	
</head>
<body>

<?php include "inc/analyticstracking.php" ?>

<header>
</header>

<div id='wrapper'>

<nav>
<span id='nav-menubar'>
<a href='?p=news'>news</a> / <a href='?p=maps'>maps</a> / <a href='?p=streams'>streams</a> / <a href='?p=projects'>projects</a>
</span>
<span id='nav-actionbar'>

<form action='?p=search' method='post'><input type='text' name='searchb' style='display: inline;' id='searchbox' placeholder='search' onfocus="searchboxFocus();" onblur="searchboxBlur();"/></form> / 

<?php

if (isset($_SESSION["username"]))
	echo "logged in as ".$_SESSION["username"]." | <a href='?p=logout'>log out</a>";
else
	echo "<a href='?p=login'>log in</a>";

?>

</span>
</nav>
<hr />
<section>

<?php

if (isset($_GET["p"]) && $_GET["p"] != null && $_GET["p"] != "") {
	if (file_exists("inc/".$_GET["p"].".php"))
		require "inc/".$_GET["p"].".php";
	else
		echo "404";
} else
	require "inc/news.php";

?>

</section>

</div>

<footer>
2012 thecookiefactory.org
</footer>

</body>
</html>