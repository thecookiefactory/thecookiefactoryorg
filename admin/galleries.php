<?php

session_start();

$r_c = 1;
require_once "../inc/functions.php";
require_once "../inc/classes/picture.class.php";
require_once "../inc/classes/user.class.php";
require_once "../vendor/autoload.php";

$user = new user((isset($_SESSION["userid"]) ? $_SESSION["userid"] : null));

if (!$user->isAdmin()) die("403");

$twig = twigInit();

use Aws\S3\S3Client;

$S3C = S3Client::factory(array(
    "key"    => $config["s3"]["key"],
    "secret" => $config["s3"]["secret"]
));

if (isset($_GET["action"]) && ($_GET["action"] == "add" || $_GET["action"] == "edit")) {

    if ($_GET["action"] == "edit" && isset($_GET["id"]) && is_numeric($_GET["id"])) {

        $mode = "edit";

        $id = strip($_GET["id"]);

        try {

            $query = $con->prepare("SELECT `pictures`.`id`, `pictures`.`text`, `pictures`.`ordernumber` FROM `pictures` WHERE `pictures`.`id` = :id");
            $query->bindValue("id", $id, PDO::PARAM_INT);
            $query->execute();

        } catch (PDOException $e) {

            die("Query failed.");

        }

        if ($query->rowCount() == 0) {

            die("Not a valid id.");

        }

        $row = $query->fetch();

        $picturedata = $row;

        $picture = new picture($row["id"]);

        $picturedata["url"] = $picture->getUrl();

        if (isset($_POST["submit"])) {

            if (isset($_POST["delete"]) && $_POST["delete"] == "on") {

                $picture = new picture($id);

                if ($picture->delete()) {

                    $status = "deletesuccess";

                } else {

                    $status = "deletefailure";

                }

            } else {

                $text = strip($_POST["text"]);

                $ordernumber = strip($_POST["ordernumber"]);

                try {

                    $query = $con->prepare("UPDATE `pictures` SET `pictures`.`text` = :text, `pictures`.`ordernumber` = :ordernumber WHERE `pictures`.`id` = :id");
                    $query->bindValue("text", $text, PDO::PARAM_STR);
                    $query->bindValue("id", $id, PDO::PARAM_INT);
                    $query->bindValue("ordernumber", $ordernumber, PDO::PARAM_INT);
                    $query->execute();

                } catch (PDOException $e) {

                    die("Query failed.");

                }

                $status = "success";

            }

        } else {

            $status = "progress";

        }

    } else if ($_GET["action"] == "add" && isset($_GET["id"]) && is_numeric($_GET["id"])) {

        $mode = "add";

        $id = strip($_GET["id"]);

        $currentid = $id;

        try {

            $mq = $con->prepare("SELECT `maps`.`name` FROM `maps` WHERE `maps`.`id` = :id");
            $mq->bindValue("id", $id, PDO::PARAM_INT);
            $mq->execute();

        } catch (PDOException $e) {

            die("Query failed.");

        }

        if ($mq->rowCount() == 0) {

            die("Not a valid id.");

        }

        $mr = $mq->fetch();

        if (isset($_POST["submit"])) {

            $uploadsuccess = true;

            for($i = 0; $i < count($_FILES["images"]["name"]); $i++) {

                //image variables
                $filename = strtolower($_FILES["images"]["name"][$i]);
                $filetype = $_FILES["images"]["type"][$i];
                $tmp_name = $_FILES["images"]["tmp_name"][$i];

                $extension = substr($filename, strpos($filename, ".") + 1);

                if (!empty($filename)) {

                    if (($extension == "jpg" || $extension == "jpeg" || $extension == "png") && ($filetype == "image/jpeg" || $filetype == "image/png")) {

                        $newfilename = uniqid();

                        $newfilename .= ".".$extension;

                        try {

                            // upload to S3
                            $result = $S3C->putObject(array(
                                "Bucket"     => $config["s3"]["bucket"],
                                "Key"        => $newfilename,
                                "SouceFile" => $tmp_name
                            ));

                            try {

                                $iq = $con->prepare("INSERT INTO `pictures` VALUES(DEFAULT, :text, DEFAULT, :filename, :mapid, :ordernumber)");
                                $iq->bindValue("text", $text, PDO::PARAM_STR);
                                $iq->bindValue("filename", $newfilename, PDO::PARAM_STR);
                                $iq->bindValue("mapid", $id, PDO::PARAM_INT);
                                $iq->bindValue("ordernumber", $ordernumber, PDO::PARAM_INT);
                                $iq->execute();

                            } catch (PDOException $e) {

                                die("Query failed.");

                            }

                        } catch (Exception $e) {

                            $uploadsuccess = false;

                        }

                    } else {

                        $wrongtype = true;

                    }

                }

            }

            if ($uploadsuccess) {

                $status = "success";

            } else {

                $status = "failure";

            }

            if (isset($wrongtype)) {

                $status = "wrongtype";

            }

        } else {

            $status = "progress";

        }

    }

} else {

    $mode = "manage";

    try {

        $query = $con->query("SELECT `maps`.`id`, `maps`.`name` FROM `maps` ORDER BY `maps`.`id` DESC");

        $maps = array();

        while ($row = $query->fetch()) {

            $gq = $con->prepare("SELECT `pictures`.`id`, `pictures`.`text` FROM `pictures` WHERE `pictures`.`mapid` = :id");
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

    } catch (PDOException $e) {

        die("Query failed.");

    }

}

echo $twig->render("admin/galleries.html", array("mode" => $mode,
                                                 "status" => (isset($status) ? $status : null),
                                                 "picturedata" => (isset($picturedata) ? $picturedata : null),
                                                 "currentid" => (isset($currentid) ? $currentid : null),
                                                 "maps" => (isset($maps) ? $maps : null),
                                                 ));
