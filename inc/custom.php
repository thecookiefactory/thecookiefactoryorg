<?php

if (!isset($r_c)) header("Location: /notfound.php");

include_once "analyticstracking.php";
require_once "inc/classes/custompage.class.php";
require_once "markdown/markdown.php";

$_SESSION["lp"] = $p;

$page = new custompage(strip($_GET["p"]));

echo $page->display();
