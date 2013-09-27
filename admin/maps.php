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
</head>
<body>

<?php

if (isset($_GET["action"]) && ($_GET["action"] == "edit" || $_GET["action"] == "delete" || $_GET["action"] == "write")) {

    if ($_GET["action"] == "edit") {
        // EDIT

        $id = strip($_GET["id"]);
        $eq = $con->prepare("SELECT * FROM `maps` WHERE `maps`.`id` = :id");
        $eq->bindValue("id", $id, PDO::PARAM_INT);
        $eq->execute();


        if (isset($_POST["submit"]) && vf($_POST["name"]) && vf($_POST["game"]) && vf($_POST["text"])) {

            $mr = $eq->fetch();

            $name = strip($_POST["name"]);
            $game = strip($_POST["game"]);
            $text = strip($_POST["text"]);
            $download = strip($_POST["download"]);

            //image file
            $image_name = $_FILES["image"]["name"];
            $image_size = $_FILES["image"]["size"];
            $image_type = $_FILES["image"]["type"];
            $image_tmp = $_FILES["image"]["tmp_name"];

            $location = dirname(dirname(getcwd())) . "\\img\\maps\\";

            if (!empty($filename)) {

                // call the python uploader script
                exec($config["python"]["webp"] . " " . $tmp_name . " " . $location);

            } else {

                echo "There was no new image, continuing.<br>";

            }

            $uq = $con->prepare("UPDATE `maps` SET `maps`.`name` = :name, `maps`.`gameid` = :game, `maps`.`text` = :text, `maps`.`dl` = :download WHERE `maps`.`id` = :id");
            $uq->bindValue("id", $id, PDO::PARAM_INT);
            $uq->bindValue("name", $name, PDO::PARAM_STR);
            $uq->bindValue("game", $game, PDO::PARAM_INT);
            $uq->bindValue("text", $text, PDO::PARAM_STR);
            $uq->bindValue("download", $download, PDO::PARAM_STR);
            $uq->execute();

        }

        if ($eq->rowCount() == 1) {

            //fetching the current data
            $eq = $con->prepare("SELECT * FROM `maps` WHERE `maps`.`id` = :id");
            $eq->bindValue("id", $id, PDO::PARAM_INT);
            $eq->execute();

            $mr = $eq->fetch();

            echo "<form action='?action=edit&amp;id=".$id."' method='post' enctype='multipart/form-data'>
            Name<br>
            <input type='text' name='name' value='".$mr["name"]."' required><br>
            Associated game<br>
            <select name='game'>";

            $gq = $con->query("SELECT * FROM `games` ORDER BY `games`.`id` ASC");

            while ($gr = $gq->fetch()) {

                echo "<option value='".$gr["id"]."'";

                if ($mr["gameid"] == $gr["id"]) {

                    echo " selected";

                }

                echo ">".$gr["name"]."</option>";

            }

            echo "</select><br>
            description<br>
            <textarea name='text' required>".$mr["text"]."</textarea><br>
            download (empty for none, repo name for github, steam file id for workshop file):
            <input type='text' name='download' value='".$mr["dl"]."'>
            <br>
            main image<br>
            <img style='width: 300px;' src='../img/maps/".$mr["id"].".".$mr["extension"]."' alt=''>
            <br>
            <input type='file' name='image'><br>
            <input type='submit' name='submit'>
            </form>";

        } else {

            echo "The specified id returned no maps.<br>";
            echo "<a href='maps.php'>maps admin panel</a> - <a href='../index.php?p=maps'>maps page</a>";

        }

    } else if ($_GET["action"] == "delete") {
        // DELETE

        if (isset($_GET["id"]) && is_numeric($_GET["id"])) {

            $id = strip($_GET["id"]);
            $eq = $con->prepare("SELECT * FROM `maps` WHERE `maps`.`id` = :id");
            $eq->bindValue("id", $id, PDO::PARAM_INT);
            $eq->execute();

            if ($eq->rowCount() == 1) {

                $er = $eq->fetch();

                if (isset($_POST["delete"])) {

                    //deleting the main image
                    unlink("../img/maps/".$er["id"].".".$er["extension"]);

                    //deleting images from the gallery
                    $gq = $con->prepare("SELECT * FROM `pictures` WHERE `pictures`.`mapid` = :id");
                    $gq->bindValue("id", $id, PDO::PARAM_INT);
                    $gq->execute();

                    while ($gr = $gq->fetch()) {

                        unlink("../img/maps/".$er["id"]."/".$gr["filename"]);
                        $dq = $con->prepare("DELETE FROM `pictures` WHERE `pictures`.`id` = :id");
                        $dq->bindValue("id", $gr["id"], PDO::PARAM_INT);
                        $dq->execute();

                    }

                    //deleting the forum thread
                    $dq = $con->prepare("DELETE FROM `forumthreads` WHERE `forumthreads`.`mapid` = :id");
                    $dq->bindValue("id", $id, PDO::PARAM_INT);
                    $dq->execute();
                    // comments are not actually deleted at this point, but w/e

                    $dq = $con->prepare("DELETE FROM `maps` WHERE `maps`.`id` = :id");
                    $dq->bindValue("id", $id, PDO::PARAM_INT);
                    $dq->execute();

                    rmdir("../img/maps/".$id);

                    echo "Map successfully deleted.<br>";
                    echo "<a href='maps.php'>maps admin panel</a> - <a href='../index.php?p=maps'>maps page</a>";

                } else {

                    echo "Delete map id ".$id."?";
                    echo "<form action='?action=delete&amp;id=".$id."' method='post'>
                    <input type='submit' name='delete' value='Yes, delete'> or <a href='maps.php'>maps admin panel</a> - <a href='../index.php?p=maps'>maps page</a>
                    </form>";

                }

            } else {

                echo "The specified id returned no map.<br>";
                echo "<a href='maps.php'>maps admin panel</a> - <a href='../index.php?p=maps'>maps page</a>";

            }

        } else {

            echo "There was no id defined.<br>";
            echo "<a href='maps.php'>maps admin panel</a> - <a href='../index.php?p=maps'>maps page</a>";
        }

    } else {
        // WRITE

        if (isset($_POST["submit"]) && vf($_POST["name"]) && vf($_POST["game"]) && vf($_POST["text"])) {

            echo "Map creating process initiating...<br>";

            //basic values
            $name = strip($_POST["name"]);
            $author = $_SESSION["userid"];
            $game = strip($_POST["game"]);
            $text = strip($_POST["text"]);
            $download = strip($_POST["download"]);

            if (isset($_POST["topicname"]) && vf($_POST["topicname"]) && vf($_POST["topiccat"]) && vf($_POST["topictext"])) {

                $comments = 1;
                echo "the topic name is: ".$_POST["topicname"];

            } else {

                $comments = 0;

            }

            //inserting the basic data and returning the map id
            $iq = $con->prepare("INSERT INTO `maps` VALUES(NULL, :name, :text, :author, now(), NULL, :download, '', :comments, :game, '')");
            $iq->bindValue("name", $name, PDO::PARAM_STR);
            $iq->bindValue("text", $text, PDO::PARAM_STR);
            $iq->bindValue("author", $author, PDO::PARAM_INT);
            $iq->bindValue("download", $download, PDO::PARAM_STR);
            $iq->bindValue("comments", $comments, PDO::PARAM_INT);
            $iq->bindValue("game", $game, PDO::PARAM_INT);
            $iq->execute();

            $id = $con->lastInsertId();
            echo "Basic values inserted...<br>";
            echo "The map id is ".$id."<br>";

            //create the directory
            mkdir("../img/maps/".$id, 0777);
            echo "Directory created...<br>";

            //image file
            $image_name = strtolower($_FILES["image"]["name"]);
            $image_size = $_FILES["image"]["size"];
            $image_type = $_FILES["image"]["type"];
            $image_tmp = $_FILES["image"]["tmp_name"];

            $location = dirname(dirname(getcwd())) . "\\img\\maps\\";

            if (!empty($filename)) {

                // call the python uploader script
                exec($config["python"]["webp"] . " " . $tmp_name . " " . $location);

            }

            if (isset($_POST["topicname"]) && vf($_POST["topicname"]) && vf($_POST["topicname"]) && vf($_POST["topiccat"]) && vf($_POST["topictext"])) {
                // creating forum entry for comments

                $authorid = $_SESSION["userid"];
                $title = strip($_POST["topicname"]);
                $text = strip($_POST["topictext"]);
                $cat = strip($_POST["topiccat"]);

                $iq = $con->prepare("INSERT INTO `forumthreads` VALUES(NULL, :title, :text, :authorid, now(), 0, now(), :cat, :id, 0, 0)");
                $iq->bindValue("authorid", $authorid, PDO::PARAM_INT);
                $iq->bindValue("title", $title, PDO::PARAM_STR);
                $iq->bindValue("text", $text, PDO::PARAM_STR);
                $iq->bindValue("cat", $cat, PDO::PARAM_INT);
                $iq->bindValue("id", $id, PDO::PARAM_INT);
                $iq->execute();

            }

            echo "Map successfully submitted.<br>";
            echo "<a href='maps.php'>maps admin panel</a> - <a href='../index.php?p=maps'>maps page</a>";

        } else {

            echo "<h1>post a map - by ".getname($_SESSION["userid"])."</h1>
            <form action='?action=write' method='post' enctype='multipart/form-data'>
            Name<br>
            <input type='text' name='name' required><br>
            Associated game<br>
            <select name='game'>";

            $gq = $con->query("SELECT * FROM `games` ORDER BY `games`.`id` ASC");

            while ($gr = $gq->fetch()) {

                echo "<option value='".$gr["id"]."'>".$gr["name"]."</option>";

            }

            echo "
            </select><br>
            description<br>
            <textarea name='text' required></textarea><br>
            download (empty for none, repo name for github, steam file id for workshop file):
            <input type='text' name='download'>
            <br>
            Main image<br>
            <input type='file' name='image' required><br>
            map topic name (leave empty if no topic)<br>
            <input type='text' name='topicname'>
            select cateryogy
            <select name='topiccat'>";

            $cq = $con->query("SELECT * FROM `forumcategories` ORDER BY `forumcategories`.`name` ASC");

            while ($cr = $cq->fetch()) {

                echo "<option value='".$cr["id"]."'>".$cr["name"]."</option>";

            }

            echo "</select>
            <textarea name='topictext'></textarea>
            <input type='submit' name='submit'>
            </form>";

        }

    }

} else {
    // ALL

    echo "<h1>manage maps</h1>
    <p><a href='?action=write'>add new</a></p>";

    $query = $con->query("SELECT * FROM `maps` ORDER BY `maps`.`id` DESC");

    echo "<table style='border-spacing: 5px;'>";
    echo "<tr><th>maps</th><th>editing tools</th></tr>";

    while ($row = $query->fetch()) {

        echo "<tr>";
        echo "<td>";
        echo "#".$row["id"]." - ".$row["name"]." - ".getname($row["authorid"]);
        echo "</td>";
        echo "<td>";
        echo "<a href='?action=edit&amp;id=".$row["id"]."'>edit</a> <a href='?action=delete&amp;id=".$row["id"]."'>delete</a>";
        echo "</td>";
        echo "</tr>";

    }

    echo "</table>";

}

?>
<a href='index.php'> &lt;&lt; back to the main page</a>
</body>
</html>
