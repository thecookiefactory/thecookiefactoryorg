<?php

session_start();

$r_c = 1;
require_once "../inc/functions.php";
require_once "../inc/classes/user.class.php";

$user = new user((isset($_SESSION["userid"]) ? $_SESSION["userid"] : null));

if (!$user->isAdmin()) die("403");

$twig = twigInit();

if (isset($_GET["action"]) && ($_GET["action"] == "add" || $_GET["action"] == "edit")) {

    if ($_GET["action"] == "edit" && isset($_GET["id"]) && is_numeric($_GET["id"])) {

        $mode = "edit";

        $id = strip($_GET["id"]);

        $query = $con->prepare("SELECT * FROM `pictures` WHERE `pictures`.`id` = :id");
        $query->bindValue("id", $id, PDO::PARAM_INT);
        $query->execute();

        if ($query->rowCount() == 0) {

            die("Not a valid id.");

        }

        $row = $query->fetch();

        $picturedata = $row;

        if (isset($_POST["submit"])) {

            if (isset($_POST["delete"]) && $_POST["delete"] == "on") {

                if (unlink("../img/maps/".$row["mapid"]."/".$row["filename"])) {

                    $dq = $con->prepare("DELETE FROM `pictures` WHERE `pictures`.`id` = :id");
                    $dq->bindValue("id", $id, PDO::PARAM_INT);
                    $dq->execute();

                    $status = "deletesuccess";

                } else {

                    $status = "deletefailure";

                }

            } else {

                $text = strip($_POST["text"]);

                $ordernumber = strip($_POST["ordernumber"]);

                $query = $con->prepare("UPDATE `pictures` SET `pictures`.`text` = :text, `pictures`.`ordernumber` = :ordernumber WHERE `pictures`.`id` = :id");
                $query->bindValue("text", $text, PDO::PARAM_STR);
                $query->bindValue("id", $id, PDO::PARAM_INT);
                $query->bindValue("ordernumber", $ordernumber, PDO::PARAM_INT);
                $query->execute();

                $status = "success";

            }

        } else {

            $status = "progress";

        }

    } else if ($_GET["action"] == "add" && isset($_GET["id"]) && is_numeric($_GET["id"])) {

        $mode = "add";

        $id = strip($_GET["id"]);

        $currentid = $id;

        $mq = $con->prepare("SELECT `maps`.`name` FROM `maps` WHERE `maps`.`id` = :id");
        $mq->bindValue("id", $id, PDO::PARAM_INT);
        $mq->execute();

        if ($mq->rowCount() == 0) {

            die("Not a valid id.");

        }

        $mr = $mq->fetch();

        if (isset($_POST["submit"])) {

            $text = strip($_POST["text"]);

            $ordernumber = strip($_POST["ordernumber"]);

            //image variables
            $filename = strtolower($_FILES["image"]["name"]);
            $filetype = $_FILES["image"]["type"];
            $tmp_name = $_FILES["image"]["tmp_name"];

            $extension = substr($filename, strpos($filename, ".") + 1);

            if (!empty($filename)) {

                if (($extension == "jpg" || $extension == "jpeg" || $extension == "png") && ($filetype == "image/jpeg" || $filetype == "image/png")) {

                    $location = "../img/maps/".$id."/";

                    if (move_uploaded_file($tmp_name, $location.$filename)) {

                        $iq = $con->prepare("INSERT INTO `pictures` VALUES(DEFAULT, :text, DEFAULT, :filename, :mapid, :ordernumber)");
                        $iq->bindValue("text", $text, PDO::PARAM_STR);
                        $iq->bindValue("filename", $filename, PDO::PARAM_STR);
                        $iq->bindValue("mapid", $id, PDO::PARAM_INT);
                        $iq->bindValue("ordernumber", $ordernumber, PDO::PARAM_INT);
                        $iq->execute();

                        $status = "success";

                    } else {

                        $status = "failure";

                    }

                } else {

                    $status = "wrongtype";

                }

            }

        } else {

            $status = "progress";

        }

    }

} else {

    $mode = "manage";

    $query = $con->query("SELECT `maps`.`id`, `maps`.`name` FROM `maps` ORDER BY `maps`.`id` DESC");

    $maps = array();

    while ($row = $query->fetch()) {

        $gq = $con->prepare("SELECT * FROM `pictures` WHERE `pictures`.`mapid` = :id");
        $gq->bindValue("id", $row["id"], PDO::PARAM_INT);
        $gq->execute();

        if ($gq->rowCount() > 0) {

            $pictures = array();

            while ($gr = $gq->fetch()) {

                $pictures[] = array("id" => $gr["id"], "text" => $gr["text"]);

            }

        }

        $maps[] = array("id" => $row["id"], "name" => $row["name"], "picturecount" => $gq->rowCount(), "pictures" => (isset($pictures) ? $pictures : null));

    }

}

echo $twig->render("admin/galleries.html", array("mode" => $mode,
                                                 "status" => (isset($status) ? $status : null),
                                                 "picturedata" => (isset($picturedata) ? $picturedata : null),
                                                 "currentid" => (isset($currentid) ? $currentid : null),
                                                 "maps" => (isset($maps) ? $maps : null),
                                                 ));
