<?php

if (!isset($r_c)) header("Location: /notfound.php");

require_once "inc/classes/custompage.class.php";

$_SESSION["lp"] = $p;

$page = new custompage(strip($_GET["p"]));

$pagea = $page->returnArray();

echo $twig->render("custom.html", $pagea);
