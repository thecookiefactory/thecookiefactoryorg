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

            $selectPictureData = $con->prepare("SELECT `pictures`.`id`, `pictures`.`text`, `pictures`.`ordernumber` FROM `pictures` WHERE `pictures`.`id` = :id");
            $selectPictureData->bindValue("id", $id, PDO::PARAM_INT);
            $selectPictureData->execute();

        } catch (PDOException $e) {

            die("Query failed.");

        }

        if ($selectPictureData->rowCount() == 0) {

            die("Not a valid id.");

        }

        $picturedata = $selectPictureData->fetch();

        $picture = new picture($picturedata["id"]);

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

                    $updatePictureData = $con->prepare("UPDATE `pictures` SET `pictures`.`text` = :text, `pictures`.`ordernumber` = :ordernumber WHERE `pictures`.`id` = :id");
                    $updatePictureData->bindValue("text", $text, PDO::PARAM_STR);
                    $updatePictureData->bindValue("id", $id, PDO::PARAM_INT);
                    $updatePictureData->bindValue("ordernumber", $ordernumber, PDO::PARAM_INT);
                    $updatePictureData->execute();

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

            $selectMap = $con->prepare("SELECT `maps`.`name` FROM `maps` WHERE `maps`.`id` = :id");
            $selectMap->bindValue("id", $id, PDO::PARAM_INT);
            $selectMap->execute();

        } catch (PDOException $e) {

            die("Query failed.");

        }

        if ($selectMap->rowCount() == 0) {

            die("Not a valid id.");

        }

        if (isset($_POST["submit"])) {

            $uploadsuccess = true;

            $text = strip($_POST["text"]);

            $ordernumber = strip($_POST["ordernumber"]);

            //image variables
            $filename = strtolower($_FILES["image"]["name"]);
            $filetype = $_FILES["image"]["type"];
            $tmp_name = $_FILES["image"]["tmp_name"];

            $extension = substr($filename, strpos($filename, ".") + 1);

            if (!empty($filename)) {

                if (($extension == "jpg" || $extension == "jpeg" || $extension == "png") && ($filetype == "image/jpeg" || $filetype == "image/png")) {

                    $newfilename = uniqid();

                    $newfilename .= "." . $extension;

                    try {

                        // upload to S3
                        $S3C->putObject(array(
                            "Bucket"     => $config["s3"]["bucket"],
                            "Key"        => $newfilename,
                            "SourceFile"  => $tmp_name
                        ));

                        try {

                            $createPicture = $con->prepare("INSERT INTO `pictures` VALUES(DEFAULT, :text, DEFAULT, :filename, :mapid, :ordernumber)");
                            $createPicture->bindValue("text", $text, PDO::PARAM_STR);
                            $createPicture->bindValue("filename", $newfilename, PDO::PARAM_STR);
                            $createPicture->bindValue("mapid", $id, PDO::PARAM_INT);
                            $createPicture->bindValue("ordernumber", $ordernumber, PDO::PARAM_INT);
                            $createPicture->execute();

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

        $selectMapData = $con->query("SELECT `maps`.`id`, `maps`.`name` FROM `maps` ORDER BY `maps`.`id` DESC");

        $maps = array();

        while ($mapData = $selectMapData->fetch()) {

            $selectPictureData = $con->prepare("SELECT `pictures`.`id`, `pictures`.`text` FROM `pictures` WHERE `pictures`.`mapid` = :id");
            $selectPictureData->bindValue("id", $mapData["id"], PDO::PARAM_INT);
            $selectPictureData->execute();

            if ($selectPictureData->rowCount() > 0) {

                $pictures = array();

                while ($pictureData = $selectPictureData->fetch()) {

                    $pictures[] = array("id" => $pictureData["id"], "text" => $pictureData["text"]);

                }

            }

            $maps[] = array("id" => $mapData["id"], "name" => $mapData["name"], "picturecount" => $selectPictureData->rowCount(), "pictures" => (isset($pictures) ? $pictures : null));

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
