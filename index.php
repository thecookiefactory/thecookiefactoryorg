<?php
session_start();

if (isset($_COOKIE["userid"]) && !isset($_SESSION["userid"]))
$_SESSION["userid"] = $_COOKIE["userid"];

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
</head>
<body>
	
<?php include_once("inc/analyticstracking.php") ?>

<header>

</header>

<div id='wrapper'>

<nav>
<span id='nav-menubar'>
<a class='menu-item' href='?p=news'>news</a><a class='menu-item' href='?p=maps'>maps</a><a class='menu-item' href='?p=streams'>streams</a><a class='menu-item' href='?p=projects'>projects</a>
</span>

<div id='nav-actionbar'>
<form class='menu-item' action='?p=search' method='post'><input type='text' name='searchb' style='display: inline;' id='searchbox' placeholder='search' onfocus="searchboxFocus();" onblur="searchboxBlur();"/></form>
<?php

if (isset($_SESSION["userid"])) {
echo "<span class='menu-item' id='actionbar-logindata'>logged in as <span id='actionbar-username'>".$_SESSION["userid"]."</span></span><span class='menu-item'><a href='?p=logout'>log out</a></span>";
} else {
echo "<a class='menu-item' href='?p=login'>log in</a>";
}


?>
</div>
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
<?php
if (checkadmin()) {
	echo "<a href='admin' target='_blank'>admin</a><br>";
}
?>
YYYY-MM-DD H:i (CET/CEST)
</footer>
<!-- ezt persze majd nem ide-->
<div id='contact-us'>
<a href='steam://url/GroupSteamIDPage/103582791433434721' target='_blank'>Steam</a> <a href='http://facebook.com/thecookiefactoryorg' target='_blank'>Facebook</a> <a href='http://youtube.com/thecookiefactoryorg' target='_blank'>YouTube</a> <a href='http://github.com/thecookiefactory' target='_blank'>GitHub</a>
</div>
<?php
if (isset($redirect) && $redirect == true)
	echo "<script type='text/javascript'>
	<!--
	window.location = '?p=news'
	//-->
	</script>";
?>
</body>
</html>