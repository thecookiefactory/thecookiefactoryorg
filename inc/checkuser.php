<?php

$r_c = 1;

require_once "functions.php";

if (vf($_GET["name"])) {

    $squery = $con->prepare("SELECT `users`.`id` FROM `users` WHERE `users`.`name` = :name");
    $squery->bindValue("name", strip($_GET["name"]), PDO::PARAM_STR);
    $squery->execute();

    if ($squery->rowCount() == 0) {

        echo "0";

    } else {

        echo "1";

    }

} else {

    header("Location: /notfound.php");

}
