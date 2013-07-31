<?php

header("Content-Type: application/rss+xml; charset=utf-8");
$r_c = True;
require "../inc/functions.php";

$query = mysqli_query($con, "SELECT * FROM `news` ORDER BY `id` DESC");

$rssfeed = "<?xml version='1.0' encoding='utf-8'?>";
$rssfeed .= "<rss version='2.0'>";
$rssfeed .= "<channel>";
$rssfeed .= "<title>thecookiefactory.org RSS</title>";
$rssfeed .= "<link>http://thecookiefactory.org</link>";
$rssfeed .= "<description>The RSS feed of thecookiefactory.org</description>";
$rssfeed .= "<language>en-us</language>";
$rssfeed .= "<copyright>Copyright (C) 2013 thecookiefactory.org</copyright>";

while($row = mysqli_fetch_assoc($query)) {

    $rssfeed .= "<item>";
    $rssfeed .= "<title>" . $row["title"] . "</title>";
    $rssfeed .= "<description>" . $row["text"] . "</description>";
    $rssfeed .= "<link>http://thecookiefactory.org/p=news&id=" . $row["id"] . "</link>";
    $rssfeed .= "<pubDate>" . displaydate($row["dt"]) . "</pubDate>";
    $rssfeed .= "</item>";
}

$rssfeed .= "</channel>";
$rssfeed .= "</rss>";

echo $rssfeed;
?>
