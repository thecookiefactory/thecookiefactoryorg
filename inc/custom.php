<?php

if (!isset($r_c)) header("Location: /notfound.php");

include_once "analyticstracking.php";
require_once "inc/classes/custompage.class.php";
require_once "inc/markdown/markdown.php";

$_SESSION["lp"] = $p;
echo "almost there|";
$page = new custompage(strip($_GET["p"]));
echo "class created|";
echo $page->display();
echo "displayed";
