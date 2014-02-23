<?php

session_start();

$r_c = 1;
require_once "../inc/functions.php";
require_once "../inc/classes/custompage.class.php";
require_once "../inc/classes/user.class.php";

$user = new user((isset($_SESSION["userid"]) ? $_SESSION["userid"] : null));

if (!$user->isAdmin()) die("403");

$twig = twigInit();

if (isset($_POST["text"])) {

    if (isset($_POST["live"]) && $_POST["live"] == "on") {

        $live = 1;

    } else {

        $live = 0;

    }

    $uquery = $con->prepare("UPDATE `custompages` SET `custompages`.`text` = :text, `custompages`.`title` = :name, `custompages`.`live` = :live, `custompages`.`stringid` = :stringid WHERE `custompages`.`id` = :id");
    $uquery->bindValue("text", $_POST["text"], PDO::PARAM_STR);
    $uquery->bindValue("name", strip($_POST["name"]), PDO::PARAM_STR);
    $uquery->bindValue("live", $live, PDO::PARAM_INT);
    $uquery->bindValue("stringid", strip($_POST["stringid"]), PDO::PARAM_STR);
    $uquery->bindValue("id", strip($_POST["id"]), PDO::PARAM_INT);
    $uquery->execute();

}

if (isset($_POST["create"])) {

    $title = strip($_POST["title"]);
    $iquery = $con->prepare("INSERT INTO `custompages` VALUES(DEFAULT, :title, '', DEFAULT, DEFAULT, DEFAULT, '')");
    $iquery->bindValue("title", $title, PDO::PARAM_STR);
    $iquery->execute();

}

if (isset($_POST["cpage"])) {

    $squery = $con->prepare("SELECT *, BIN(`custompages`.`live`) FROM `custompages` WHERE `custompages`.`title` = :cpage");
    $squery->bindValue("cpage", strip($_POST["cpage"]), PDO::PARAM_STR);
    $squery->execute();

    $srow = $squery->fetch();

    $p = new custompage($srow["id"], "id");

    $data = $p->returnArray();

    $mode = "update";

} else {

    $squery = $con->query("SELECT * FROM `custompages` ORDER BY `custompages`.`title` ASC");

    $pages = array();

    while ($r = $squery->fetch()) {

        $fc = new custompage($r["id"], "id");
        $pages[] = $fc->returnArray();

    }

    $mode = "select";

}

echo $twig->render("admin/custom.html", array("mode" => $mode, "pages" => (isset($pages) ? $pages : 0), "data" => (isset($data) ? $data : 0)));
