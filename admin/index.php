<?php
session_start();
$r_c = 42;
include "../inc/essential.php";

if (!checkadmin())
die("must be an dmin :(".$_SESSION["username"]);
?>

<html>
<head>
</head>
<body>
<p>
welcome to the admin panel
<a href='writenews.php'>write news</a> <a href='postmap.php'>post a map</a>
</p>
</body>
</html>