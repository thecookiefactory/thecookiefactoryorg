<?php

session_start();

$r_c = 1;
require_once "../inc/functions.php";
require_once "../inc/classes/user.class.php";

$user = new user((isset($_SESSION["userid"]) ? $_SESSION["userid"] : null));

if (!$user->isAdmin()) die("403");

?>

<html>
<head>
    <meta http-equiv='Content-Type' content='text/html;charset=UTF-8'>
    <title>thecookiefactory.org admin</title>
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
    <li><a href='forumtopics.php'>manage forum categories</a></li>
    <li><a href='cpages.php'>manage custom pages</a></li>
    <li><a href='users.php'>list users</a></li>
</ul>
</p>
</body>
</html>
