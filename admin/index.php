<?php
session_start();
$r_c = 42;
require "../inc/essential.php";

if (!checkadmin())
die("must be an dmin :(".$_SESSION["username"]);
?>

<html>
<head>
</head>
<body>
<h1>welcome to the admin panel</h1>
<p>
<ul>
<li><a href='news.php'>manage news</a></li>
<li><a href='maps.php'>manage maps</a></li>
</ul>
</p>
</body>
</html>