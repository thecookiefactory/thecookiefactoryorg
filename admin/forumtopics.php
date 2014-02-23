<?php

session_start();

$r_c = 1;
require_once "../inc/functions.php";
require_once "../inc/classes/forumcategory.class.php";
require_once "../inc/classes/user.class.php";

$user = new user((isset($_SESSION["userid"]) ? $_SESSION["userid"] : null));

if (!$user->isAdmin()) die("403");

$twig = twigInit();

$squery = $con->query("SELECT * FROM `forumcategories`");

if (isset($_POST["update"])) {

    while ($srow = $squery->fetch()) {

        $id = $srow["id"];
        $name = strip($_POST[$id."name"]);
        $longname = strip($_POST[$id."longname"]);
        $hexcode = strip($_POST[$id."hexcode"]);
        $hoverhexcode = strip($_POST[$id."hoverhexcode"]);

        if (!vf($name)) {

            $dquery = $con->prepare("DELETE FROM `forumcategories` WHERE `forumcategories`.`id` = :id");
            $dquery->bindValue("id", $id, PDO::PARAM_INT);
            $dquery->execute();

        } else {

            $uquery = $con->prepare("UPDATE `forumcategories` SET `forumcategories`.`name` = :name, `forumcategories`.`longname` = :longname, `forumcategories`.`hexcode` = :hexcode, `forumcategories`.`hoverhexcode` = :hoverhexcode WHERE `forumcategories`.`id` = :id");
            $uquery->bindValue("name", $name, PDO::PARAM_STR);
            $uquery->bindValue("longname", $longname, PDO::PARAM_STR);
            $uquery->bindValue("hexcode", $hexcode, PDO::PARAM_STR);
            $uquery->bindValue("hoverhexcode", $hoverhexcode, PDO::PARAM_STR);
            $uquery->bindValue("id", $id, PDO::PARAM_INT);
            $uquery->execute();

        }

    }

}

if (isset($_POST["addnew"])) {

    $name = strip($_POST["name"]);
    $longname = strip($_POST["longname"]);
    $hexcode = strip($_POST["hexcode"]);
    $hoverhexcode = strip($_POST["hoverhexcode"]);

    $iquery = $con->prepare("INSERT INTO `forumcategories` VALUES(DEFAULT, :name, :longname, :hexcode, :hoverhexcode, DEFAULT)");
    $iquery->bindValue("name", $name, PDO::PARAM_STR);
    $iquery->bindValue("longname", $longname, PDO::PARAM_STR);
    $iquery->bindValue("hexcode", $hexcode, PDO::PARAM_STR);
    $iquery->bindValue("hoverhexcode", $hoverhexcode, PDO::PARAM_STR);
    $iquery->execute();

}

$squery = $con->query("SELECT * FROM `forumcategories`");

$rows = array();

while ($r = $squery->fetch()) {

    $fc = new forumcategory($r["id"]);
    $rows[] = $fc->returnArray();

}

echo $twig->render("admin/forumtopics.html", array("rows" => $rows));
