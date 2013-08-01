<?php

if (!isset($r_c)) header("Location: notfound.php");

include "analyticstracking.php";
include "markdown/markdown.php";

$_SESSION["lp"] = $p;

$q = $con->prepare("SELECT `custompages`.`text` FROM `custompages` WHERE `custompages`.`name` = :p");
$q->bindValue("p", $p, PDO::PARAM_STR);
$q->execute();

$r = $q->fetch(PDO::FETCH_ASSOC);

echo Markdown($r["text"]);
