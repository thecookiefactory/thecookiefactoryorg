<?php

$r_c = 0;

require_once "inc/functions.php";

$id = (vf($_GET["id"]) && is_numeric($_GET["id"])) ? $_GET["id"] : 0;

if ($id != 0) {

    $fq = $con->prepare("SELECT `maps`.`link` FROM `maps` WHERE `maps`.`id` = :id");
    $fq->bindValue("id", $id, PDO::PARAM_INT);
    $fq->execute();

    if ($fq->rowCount() == 0) {

        header("Location: /notfound.php");

    } else {

        $uq = $con->prepare("UPDATE `maps` SET `maps`.`downloadcount` = `maps`.`downloadcount` + 1 WHERE `maps`.`id` = :id");
        $uq->bindValue("id", $id, PDO::PARAM_INT);
        $uq->execute();

        $url = $fq->fetch();
        $url = $url["link"];

        header("Location: $url");

    }

} else {

    header("Location: /notfound.php");

}
