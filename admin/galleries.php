<?php

session_start();

$r_c = 1;
require_once "../inc/functions.php";
require_once "../inc/classes/user.class.php";

$user = new user((isset($_SESSION["userid"]) ? $_SESSION["userid"] : null));

if (!$user->isAdmin()) die("403");

?>

<!doctype html>
<html>
<head>
    <meta http-equiv='Content-Type' content='text/html;charset=UTF-8'>
    <title>thecookiefactory.org admin</title>
</head>
<body>

<?php

if (isset($_GET["action"]) && ($_GET["action"] == "add" || $_GET["action"] == "edit")) {

    if ($_GET["action"] == "edit" && isset($_GET["id"]) && is_numeric($_GET["id"])) {

        $id = strip($_GET["id"]);

        $query = $con->prepare("SELECT * FROM `pictures` WHERE `pictures`.`id` = :id");
        $query->bindValue("id", $id, PDO::PARAM_INT);
        $query->execute();

        if ($query->rowCount() == 0) {

            die("Not a valid id.");

        }

        $row = $query->fetch();

        if (isset($_POST["submit"])) {

            if (isset($_POST["delete"]) && $_POST["delete"] == "on") {

                if (unlink("../img/maps/".$row["mapid"]."/".$row["filename"])) {

                    $dq = $con->prepare("DELETE FROM `pictures` WHERE `pictures`.`id` = :id");
                    $dq->bindValue("id", $id, PDO::PARAM_INT);
                    $dq->execute();

                    echo "Image deleted successfully.<br>";

                } else {

                    echo "Delete process failed.<br>";

                }

            } else {

                $text = strip($_POST["text"]);

                $query = $con->prepare("UPDATE `pictures` SET `pictures`.`text` = :text WHERE `pictures`.`id` = :id");
                $query->bindValue("text", $text, PDO::PARAM_STR);
                $query->bindValue("id", $id, PDO::PARAM_INT);
                $query->execute();
                echo "Image updated.<br>";

            }

        } else {

            echo "<img style='width: 300px;' src='../img/maps/".$row["mapid"]."/".$row["filename"]."' alt=''>";
            echo "<form action='?action=edit&amp;id=".$id."' method='post'>";
            echo "<input type='text' name='text' maxlength='100' value='".$row["text"]."' required><br>";
            echo "<input type='checkbox' name='delete'> Delete permanently<br>";
            echo "<input type='submit' name='submit'>";
            echo "</form>";

        }

    } else if ($_GET["action"] == "add" && isset($_GET["id"]) && is_numeric($_GET["id"])) {

        $id = strip($_GET["id"]);

        $mq = $con->prepare("SELECT `maps`.`name` FROM `maps` WHERE `maps`.`id` = :id");
        $mq->bindValue("id", $id, PDO::PARAM_INT);
        $mq->execute();

        if ($mq->rowCount() == 0) {

            die("Not a valid id.");

        }

        $mr = $mq->fetch();

        echo "<h1>Add an image to ".$mr["name"]."</h1>";

        if (isset($_POST["submit"])) {

            $text = strip($_POST["text"]);

            //image variables
            $filename = strtolower($_FILES["image"]["name"]);
            $filetype = $_FILES["image"]["type"];
            $tmp_name = $_FILES["image"]["tmp_name"];

            $extension = substr($filename, strpos($filename, ".") + 1);

            if (!empty($filename)) {

                if (($extension == "jpg" || $extension == "jpeg" || $extension == "png") && ($filetype == "image/jpeg" || $filetype == "image/png")) {

                    $location = "../img/maps/".$id."/";

                    if (move_uploaded_file($tmp_name, $location.$filename)) {

                        $iq = $con->prepare("INSERT INTO `pictures` VALUES(DEFAULT, :text, DEFAULT, :filename, :mapid)");
                        $iq->bindValue("text", $text, PDO::PARAM_STR);
                        $iq->bindValue("filename", $filename, PDO::PARAM_STR);
                        $iq->bindValue("mapid", $id, PDO::PARAM_INT);
                        $iq->execute();
                        echo "Image successfully uploaded.<br>";

                    } else {

                        echo "There was an error uploading your image.<br>";

                    }

                } else {

                    echo "File must be jpeg/png.<br>";

                }

            }

        } else {

            echo "<form action='?action=add&amp;id=".$id."' method='post' enctype='multipart/form-data'>";
            echo "<input type='file' name='image' required> &lt;= Please choose a name wisely, because it will be kept, also make sure this is unique. jpg/png only<br>";
            echo "textription: <input type='text' name='text' required><br>";
            echo "<input type='submit' name='submit'>";
            echo "</form>";

        }

    }

} else {

    echo "<h1>manage galleries</h1>";

    $query = $con->query("SELECT * FROM `maps` ORDER BY `maps`.`id` DESC");

    echo "<ul>";

    while ($row = $query->fetch()) {

        echo "<li>";
        echo "#".$row["id"]." - ".$row["name"]." - <a href='?action=add&amp;id=".$row["id"]."'>add new image</a>";

        $gq = $con->prepare("SELECT * FROM `pictures` WHERE `pictures`.`mapid` = :id");
        $gq->bindValue("id", $row["id"], PDO::PARAM_INT);
        $gq->execute();

        if ($gq->rowCount() > 0) {

            echo "<ul>";

            while ($gr = $gq->fetch()) {

                echo "<li>";
                echo "<a href='?action=edit&amp;id=".$gr["id"]."'>#".$gr["id"]." - ".$gr["text"]."</a>";
                echo "</li>";

            }

            echo "</ul>";
        }

        echo "</li>";

    }

    echo "</ul>";

}

?>

<a href='index.php'> &lt;&lt; back to the main page</a>
</body>
</html>
