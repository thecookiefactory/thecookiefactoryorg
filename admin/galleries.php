<?php

session_start();
$r_c = 42;
require "../inc/essential.php";

if (!checkadmin()) die("403");

?>

<!doctype html>
<html>
<head>
    <meta http-equiv='Content-Type' content='text/html;charset=UTF-8'>
</head>
<body>

<?php

if (isset($_GET["action"]) && ($_GET["action"] == "add" || $_GET["action"] == "edit")) {

    if ($_GET["action"] == "edit" && isset($_GET["id"]) && is_numeric($_GET["id"])) {
    
        $id = strip($_GET["id"]);
        
        $query = mysqli_query($con, "SELECT * FROM `gallery` WHERE `id`=".$id);
        
        if (mysqli_num_rows($query) == 0) {
            die("Not a valid id.");
        }
        
        $row = mysqli_fetch_assoc($query);
        
        if (isset($_POST["submit"])) {
        
            if (isset($_POST["delete"]) && $_POST["delete"] == "on") {
                
                if (unlink("../img/maps/".$row["mapid"]."/".$row["filename"])) {
                    
                    mysqli_query($con, "DELETE FROM `gallery` WHERE `id`=".$id);
                    echo "Image deleted successfully.<br>";
                
                } else {
                    echo "Delete process failed.<br>";
                }
            
            } else {
                
                $desc = strip($_POST["desc"]);
                
                $query = mysqli_query($con, "UPDATE `gallery` SET `desc`='".$desc."' WHERE `id`=".$id);
                echo "Image updated.<br>";
            
            }
        
        } else {
            
            echo "<img style='width: 300px;' src='../img/maps/".$row["mapid"]."/".$row["filename"]."' alt=''>";
            echo "<form action='?action=edit&amp;id=".$id."' method='post'>";
            echo "<input type='text' name='desc' maxlength='100' value='".$row["desc"]."' required><br>";
            echo "<input type='checkbox' name='delete'> Delete permanently<br>";
            echo "<input type='submit' name='submit'>";
            echo "</form>";
            
        }
    
    } else if ($_GET["action"] == "add" && isset($_GET["id"]) && is_numeric($_GET["id"])) {
        
        $id = strip($_GET["id"]);
        
        $mq = mysqli_query($con, "SELECT `name` FROM `maps` WHERE `id`=".$id);
        
        if (mysqli_num_rows($mq) == 0) {
            die("Not a valid id.");
        }
        
        $mr = mysqli_fetch_assoc($mq);
        
        echo "<h1>Add an image to ".$mr["name"]."</h1>";
        
        if (isset($_POST["submit"])) {
        
            $desc = strip($_POST["desc"]);
            
            //image variables
            $filename = strtolower($_FILES["image"]["name"]);
            $filetype = $_FILES["image"]["type"];
            $tmp_name = $_FILES["image"]["tmp_name"];
            
            $extension = substr($filename, strpos($filename, ".") + 1);
            
            if (!empty($filename)) {
            
                if (($extension == "jpg" || $extension == "jpeg" || $extension == "png") && ($filetype == "image/jpeg" || $filetype == "image/png")) {
                
                    $location = "../img/maps/".$id."/";
                
                    if (move_uploaded_file($tmp_name, $location.$filename)) {
                    
                        mysqli_query($con, "INSERT INTO `gallery` VALUES('','".$id."','".$desc."','".$filename."')");
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
            echo "description: <input type='text' name='desc' required><br>";
            echo "<input type='submit' name='submit'>";
            echo "</form>";
            
        }
    
    }

} else {
    
    echo "<h1>manage galleries</h1>";
    
    $query = mysqli_query($con, "SELECT * FROM `maps` ORDER BY `id` DESC");
    
    echo "<ul>";
    
    while ($row = mysqli_fetch_assoc($query)) {
        
        echo "<li>";
        echo "#".$row["id"]." - ".$row["name"]." - ".getname($row["authorid"])." - <a href='?action=add&amp;id=".$row["id"]."'>add new image</a>";
        
        $gq = mysqli_query($con, "SELECT * FROM `gallery` WHERE `mapid`=".$row["id"]);
        
        if (mysqli_num_rows($gq) > 0) {
            
            echo "<ul>";
            
            while ($gr = mysqli_fetch_assoc($gq)) {
                echo "<li>";
                echo "<a href='?action=edit&amp;id=".$gr["id"]."'>#".$gr["id"]." - ".$gr["desc"]."</a>";
                echo "</li>";
            }
            
            echo "</ul>";
        }
        
        echo "</li>";
    
    }
    
    echo "</ul>";

}

?>

</body>
</html>