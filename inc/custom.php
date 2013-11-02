<?php

if (!isset($r_c)) header("Location: /notfound.php");

include_once "analyticstracking.php";
require_once "markdown/markdown.php";

$_SESSION["lp"] = $p;

$q = $con->prepare("SELECT `custompages`.`text` FROM `custompages` WHERE `custompages`.`title` = :p");
$q->bindValue("p", $p, PDO::PARAM_STR);
$q->execute();

$r = $q->fetch();

echo Markdown($r["text"]);
