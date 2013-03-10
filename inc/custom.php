<?php

checkembed($r_c);
include "analyticstracking.php";
include "markdown/markdown.php";

$q = mysqli_query($con, "SELECT * FROM `cpages` WHERE name='".$_GET["p"]."'");
$r = mysqli_fetch_assoc($q);
echo Markdown($r["text"]);

?>