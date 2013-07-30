<?php

session_start();

$r_c = 41;
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
        $eq = mysqli_query($con, "SELECT * FROM `maps` WHERE `id`=".$id);

        if (isset($_POST["submit"])) {

            $mr = mysqli_fetch_assoc($eq);

            $name = strip($_POST["name"]);
            $game = strip($_POST["game"]);
            $desc = strip($_POST["desc"]);
            $download = strip($_POST["download"]);

            //image file
            $image_name = $_FILES["image"]["name"];
            $image_size = $_FILES["image"]["size"];
            $image_type = $_FILES["image"]["type"];
            $image_tmp = $_FILES["image"]["tmp_name"];

            $extension = substr($image_name, strpos($image_name, ".") + 1);

            if (!empty($image_name) && $image_size > 0) {

                if (($extension == "jpg" || $extension == "jpeg" || $extension == "png") && ($image_type == "image/jpeg" || $image_type == "image/png")) {

                    $location = "../img/maps/";

                    unlink($location.$id.".".$mr["ext"]);
                    echo "Old image file deleted.<br>";

                    if (move_uploaded_file($image_tmp, $location.$id.".".$extension)) {

                        echo "New image file uploaded...<br>";

                        mysqli_query($con, "UPDATE `maps` SET `ext`='".$extension."' WHERE `id`=".$id);
                        echo "File extension saved...<br>";

                    } else {

                        echo "There was an error uploading your image.<br>";

                    }

                } else {

                    echo "File must be jpeg/png.<br>";

                }

            } else {

                echo "There was no new image, continuing.<br>";

            }

            $editdt = time();

            $uq = mysqli_query($con, "UPDATE maps SET `name`='".$name."', `gameid`='".$game."', `desc`='".$desc."', `dl`='".$download."', editdt='".$editdt."' WHERE `id`=".$id);

        }

        if (mysqli_num_rows($eq) == 1) {

            //fetching the current data
            $eq = mysqli_query($con, "SELECT * FROM `maps` WHERE `id`=$id");
            $mr = mysqli_fetch_assoc($eq);

            echo "<form action='?action=edit&amp;id=".$id."' method='post' enctype='multipart/form-data'>
            Name<br>
            <input type='text' name='name' value='".$mr["name"]."' required><br>
            Associated game<br>
            <select name='game'>";

            $gq = mysqli_query($con, "SELECT * FROM `games` ORDER BY `id` ASC");

            while ($gr = mysqli_fetch_assoc($gq)) {

                echo "<option value='".$gr["id"]."'";

                if ($mr["gameid"] == $gr["id"]) {

                    echo " selected";

                }

                echo ">".$gr["name"]."</option>";

            }

            echo "</select><br>
            Description<br>
            <textarea name='desc' required>".$mr["desc"]."</textarea><br>
            download (empty for none, repo name for github, steam file id for workshop file):
            <input type='text' name='download' value='".$mr["dl"]."'>
            <br>
            main image<br>
            <img style='width: 300px;' src='../img/maps/".$mr["id"].".".$mr["ext"]."' alt=''>
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
            $eq = mysqli_query($con, "SELECT * FROM `maps` WHERE `id`=".$id);

            if (mysqli_num_rows($eq) == 1) {

                $er = mysqli_fetch_assoc($eq);

                if (isset($_POST["delete"])) {

                    //deleting the main image
                    unlink("../img/maps/".$er["id"].".".$er["ext"]);

                    //deleting images from the gallery
                    $gq = mysqli_query($con, "SELECT * FROM `gallery` WHERE `mapid`=".$id);

                    while ($gr = mysqli_fetch_assoc($gq)) {

                        unlink("../img/maps/".$er["id"]."/".$gr["filename"]);
                        mysqli_query($con, "DELETE FROM `gallery` WHERE `id`=".$gr["id"]);

                    }

                    //deleting the forum thread
                    mysqli_query($con, "DELETE FROM `forums` WHERE `mapid`=".$id);
                    // comments are not actually deleted at this point, but w/e

                    $dq = mysqli_query($con, "DELETE FROM `maps` WHERE `id`=$id");

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

        if (isset($_POST["submit"])) {

            echo "Map creating process initiating...<br>";

            //basic values
            $name = strip($_POST["name"]);
            $author = $_SESSION["userid"];
            $game = strip($_POST["game"]);
            $desc = strip($_POST["desc"]);
            $download = strip($_POST["download"]);

            if (isset($_POST["topicname"]) && vf($_POST["topicname"])) {

                $comments = 1;

            } else {

                $comments = 0;

            }

            $dt = time();

            //inserting the basic data and returning the map id
            mysqli_query($con, "INSERT INTO `maps` VALUES('','$name','$author','$game','$desc','$download','0','','0','0','$comments','$dt','0')");
            $id = mysqli_insert_id($con);
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

            $extension = substr($image_name, strpos($image_name, ".") + 1);

            if (!empty($image_name)) {

                if (($extension == "jpg" || $extension == "jpeg" || $extension == "png") && ($image_type == "image/jpeg" || $image_type == "image/png")) {

                    $location = "../img/maps/";

                    if (move_uploaded_file($image_tmp, $location.$id.".".$extension)) {

                        echo "Image file successfully uploaded...<br>";
                        mysqli_query($con, "UPDATE `maps` SET `ext`='".$extension."' WHERE `id`=".$id);
                        echo "File extension saved...<br>";

                    } else {

                        echo "There was an error uploading your image.<br>";

                    }

                } else {

                    echo "File must be jpeg/png.<br>";

                }

            }

            if (isset($_POST["topicname"]) && vf($_POST["topicname"])) {
                // creating forum entry for comments

                $authorid = $_SESSION["userid"];
                $dt = time();
                $title = strip($_POST["topicname"]);
                $text = strip($_POST["topictext"]);
                $cat = strip($_POST["topiccat"]);

                mysqli_query($con, "INSERT INTO `forums` VALUES('','".$authorid."','".$dt."','0','".$title."','".$text."','".$cat."','0','".$dt."','".$id."','0')");

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

            $gq = mysqli_query($con, "SELECT * FROM `games` ORDER BY `id` ASC");

            while ($gr = mysqli_fetch_assoc($gq)) {

                echo "<option value='".$gr["id"]."'>".$gr["name"]."</option>";

            }

            echo "
            </select><br>
            Description<br>
            <textarea name='desc' required></textarea><br>
            download (empty for none, repo name for github, steam file id for workshop file):
            <input type='text' name='download'>
            <br>
            Main image<br>
            <input type='file' name='image' required><br>
            map topic name (leave empty if no topic)<br>
            <input type='text' name='topicname'>
            select cateryogy
            <select name='topiccat'>";

            $cq = mysqli_query($con, "SELECT * FROM `forumcat` ORDER BY `name` ASC");

            while ($cr = mysqli_fetch_assoc($cq)) {

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

    $query = mysqli_query($con, "SELECT * FROM `maps` ORDER BY `id` DESC");

    echo "<table style='border-spacing: 5px;'>";
    echo "<tr><th>maps</th><th>editing tools</th></tr>";

    while ($row = mysqli_fetch_assoc($query)) {

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
