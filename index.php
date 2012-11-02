<?php
session_start();

if (isset($_COOKIE["username"]) && !isset($_SESSION["username"]))
$_SESSION["username"] = $_COOKIE["username"];

$r_c = 42;
require "inc/essential.php";

?>

<!doctype html>
<html>
<head>
<title>thecookiefactory.org</title>
<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
<link rel='StyleSheet' type='text/css' href='base.css' />
<link href='http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700' rel='stylesheet' type='text/css' />
<script src="js/main.js"></script>
<script src="js/maps.js"></script>
</head>
<body <?php if (isset($_GET["p"]) && $_GET["p"] == "login") echo "onLoad='document.forms.login.username.focus()'"; ?>>
	
<?php include_once("inc/analyticstracking.php") ?>

<header>

</header>

<div id='wrapper'>

<nav>
<span id='nav-menubar'>
<a href='?p=news'>news</a> / <a href='?p=maps'>maps</a> / <a href='?p=streams'>streams</a> / <a href='?p=projects'>projects</a>
</span>
<span id='nav-actionbar'>

<form action='?p=search' method='post'><input type='text' name='searchb' style='display: inline;' id='searchbox' placeholder='search' onfocus="searchboxFocus();" onblur="searchboxBlur();"/></form>
 / <?php

if (isset($_SESSION["username"])) {
echo "<span id='actionbar-logindata'>logged in as <span id='actionbar-username'>".$_SESSION["username"]."</span></span> / <a href='?p=logout'>log out</a>";
} else {
echo "<a href='?p=login'>log in</a>";
}


?>
</span>
</nav>
<hr />

<section>

<?php

if (isset($_GET["p"]) && $_GET["p"] != null && $_GET["p"] != "" && $_GET["p"] != "essential") {
	if (file_exists("inc/".$_GET["p"].".php"))
		require "inc/".$_GET["p"].".php";
	else
		echo "404";
} else {
require "inc/news.php";
}

?>

</section>

</div>

<footer>
2012 thecookiefactory.org<br>
YYYY-MM-DD H:i (CET/CEST)
</footer>
<!-- ezt persze majd nem ide-->
<div id='contact-us'>
<a href='steam://url/GroupSteamIDPage/103582791433434721' target='_blank'>Steam</a> <a href='http://facebook.com/thecookiefactoryorg' target='_blank'>Facebook</a> <a href='http://youtube.com/thecookiefactoryorg' target='_blank'>YouTube</a> <a href='http://github.com/thecookiefactory' target='_blank'>GitHub</a>
</div>

</body>
</html>