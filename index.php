<?php
session_start();
$r_c = 42;
require "inc/essential.php";



?>

<!doctype html>
<html>
<head>
<title>thecookiefactory.org</title>
<link rel='StyleSheet' type='text/css' href='base.css' />
<link href='http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700' rel='stylesheet' type='text/css' />
</head>
<body>


<header>

</header>

<div id='wrapper'>

<nav>
<span id='nav-menubar'>
<a href='?p=news'>news</a> / <a href='?p=maps'>maps</a> / <a href='?p=streams'>streams</a>
</span>
<span id='nav-actionbar'>
<?php

if (isset($_SESSION["username"])) {
echo "logged in as ".$_SESSION["username"]." | <a href='?p=logout'>log out</a>";
} else {
echo "<a href='?p=login'>log in</a>";
}

?>
&nbsp;<form action='?p=search' method='post'><input type='text' name='searchb' style='display: inline;' id='searchbox' placeholder='search' /></form>
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
} else {
echo "default page (news?)";
}

?>

</section>

</div>

<footer>
2012 thecookiefactory.org
</footer>

</body>
</html>