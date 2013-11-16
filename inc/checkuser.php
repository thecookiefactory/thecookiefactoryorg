<?php

$r_c = 1;

require_once "functions.php";

if (vf($_GET["name"])) {

    $q = $con->prepare("SELECT `users`.`id` FROM `users` WHERE `users`.`name` = :name");
    $q->bindValue("name", strip($_GET["name"]), PDO::PARAM_STR);
    $q->execute();

    if ($q->rowCount() == 0) {

        echo "0";

    } else {

        echo "1";

    }

} else {

    header("Location: /notfound.php");

}
