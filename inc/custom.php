<?php

if (!isset($r_c)) header("Location: notfound.php");
include "analyticstracking.php";
include "markdown/markdown.php";

$_SESSION["lp"] = $p;

$q = mysqli_query($con, "SELECT `text` FROM `cpages` WHERE name='".$p."'");
$r = mysqli_fetch_assoc($q);
echo Markdown($r["text"]);