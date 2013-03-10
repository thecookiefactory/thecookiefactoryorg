<?php

session_start();
$r_c = 42;
require "../inc/essential.php";

if (!checkadmin()) die("403");

?>

<html>
<head>
    <meta http-equiv='Content-Type' content='text/html;charset=UTF-8'>
</head>
<body>
<h1>welcome to the admin panel</h1>
<p>
<ul>
    <li><a href='news.php'>manage news</a></li>
    <li><a href='maps.php'>manage maps</a></li>
    <li><a href='streams.php'>manage your stream</a></li>
    <li><a href='galleries.php'>manage galleries</a></li>
    <li><a href='games.php'>manage games table</a></li>
    <li><a href='forums.php'>manage forum topics</a></li>
    <li><a href='cpages.php'>manage custom pages</a></li>
</ul>
</p>
</body>
</html>