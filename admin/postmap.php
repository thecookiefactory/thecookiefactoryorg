<?php
session_start();
$r_c = 42;
include "../inc/essential.php";

if (!checkadmin())
die("must be an dmin :(".$_SESSION["username"]);
?>
<!doctype html>
<html>
<body>
<h1>post a map</h1>
</body>
</html>