<?php

$r_c = 1;

require_once "functions.php";

if (vf($_GET["name"])) {

    try {

        $selectUserId = $con->prepare("SELECT `users`.`id` FROM `users` WHERE `users`.`name` = :name");
        $selectUserId->bindValue("name", strip($_GET["name"]), PDO::PARAM_STR);
        $selectUserId->execute();

        if ($selectUserId->rowCount() == 0) {

            echo "0";

        } else {

            echo "1";

        }

    } catch (PDOException $e) {

        echo "An error occurred while trying to fetch data.";

    }

} else {

    header("Location: /notfound.php");

}
