<?php

session_start();

$r_c = 1;
require "../inc/functions.php";

if (!checkadmin()) die("403");

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

            $location = dirname(getcwd()) . "\\img\\maps\\" . $id . "\\";

            if (!empty($filename)) {

                // call the python uploader script
                exec($config["python"]["webp"] . " " . $tmp_name . " " . $location);

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
        echo "#".$row["id"]." - ".$row["name"]." - ".getname($row["authorid"])." - <a href='?action=add&amp;id=".$row["id"]."'>add new image</a>";

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
