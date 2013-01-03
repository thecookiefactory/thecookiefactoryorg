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
            
            switch($_POST["dli"]) {
            
                case "file":
                    //file upload
                    
                    $dli = 0;
                    
                    $bsp_name = strtolower($_FILES["bsp"]["name"]);
                    $bsp_type = $_FILES["bsp"]["type"];
                    $bsp_size = $_FILES["bsp"]["size"];
                    $bsp_tmp = $_FILES["bsp"]["tmp_name"];
                    $bsp_error = $_FILES["bsp"]["error"];
                    
                    $extension = substr($bsp_name, strpos($bsp_name, ".") + 1);
                    
                    if (!empty($bsp_name) && $bsp_size > 0) {
            
                        if ($extension == "bsp" && $bsp_type == "application/octet-stream") {
                
                            $location = "../img/maps/";
                            $dl = $name.".bsp";
                            
                            if (file_exists("../img/maps/".$name.".bsp")) {
                                
                                echo "Deleting the old bsp file...<br>";
                                unlink("../img/maps/".$name.".bsp");
                            
                            } else { 
                                echo "No old .bsp file found, continuing...<br>";
                            }
                
                            if (move_uploaded_file($bsp_tmp, $location.$dl)) {
                    
                                echo ".bsp file successfully uploaded...<br>";
                                
                                mysqli_query($con, "UPDATE `maps` SET `dl`='img/maps/".$dl."' WHERE `id`=".$id);
            
                            } else {
                
                                echo "There was an error while uploading the file. It is highly recommended to try again or change the download to not available.<br>";
                
                            }
                
                        } else {
                
                            echo "The specified file does not appear to be a .bsp. It is highly recommended to try again or change the download to not available.<br>";
                
                        }
                
                    } else {
                    
                        echo "There was no file defined. It is highly recommended to change the download to not available.";
                        
                    }
                    
                    break;
            
                case "link":
                    //steamcommunity id
                
                    $dli = 1;

                    if (file_exists("../img/maps/".$name.".bsp")) {
                        
                        echo "Deleting the old bsp file...<br>";
                        unlink("../img/maps/".$name.".bsp");

                    } else { 
                        echo "No old .bsp file found, continuing...<br>";
                    } 
                    
                    $dl = strip($_POST["dl"]);
                    $uq = mysqli_query($con, "UPDATE maps SET `dl`='".$dl."' WHERE `id`=".$id);
                    
                    break;
                
                case "none":
                    //no download specified
                    
                    $dli = 2;

                    if (file_exists("../img/maps/".$name.".bsp")) {
                        
                        echo "Deleting the old bsp file...<br>";
                        unlink("../img/maps/".$name.".bsp");

                    } else { 
                        echo "No old .bsp file found, continuing...<br>";
                    }
                    
                    break;
            
            }
        
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
        
            $uq = mysqli_query($con, "UPDATE maps SET `name`='".$name."', `gameid`='".$game."', `desc`='".$desc."', `dltype`='".$dli."' WHERE `id`=".$id);
        
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
                if ($mr["gameid"] == $gr["id"]) echo " selected";
                echo ">".$gr["name"]."</option>";
            }
            
            echo "</select><br>
            Description<br>
            <textarea name='desc' required>".$mr["desc"]."</textarea><br>
            
            <input type='radio' name='dli' value='file'  "; if ($mr["dltype"] == 0) echo "checked "; echo ">bsp file <input type='file' name='bsp'>         
            <br>
            <input type='radio' name='dli' value='link' "; if ($mr["dltype"] == 1) echo "checked "; echo " required>download link <input type='text' name='dl' value='"; if ($mr["dltype"] == 1) echo $mr["dl"]; echo "'>
            <br>
            <input type='radio' name='dli' value='none'  "; if ($mr["dltype"] == 2) echo "checked "; echo ">no dl
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
                    
                    //deleting the bsp if present
                    if ($er["dltype"] == 0) {
                        unlink("../".$er["dl"]);
                        echo ".bsp file deleted.<br>";
                    }
                    
                    //deleting the main image
                    unlink("../img/maps/".$er["id"].".".$er["ext"]);
                    
                    //deleting images from the gallery
                    $gq = mysqli_query($con, "SELECT * FROM `gallery` WHERE `mapid`=".$id);
                    
                    while ($gr = mysqli_fetch_assoc($gq)) {
                        unlink("../img/maps/".$er["id"]."/".$gr["filename"]);
                        mysqli_query($con, "DELETE FROM `gallery` WHERE `id`=".$gr["id"]);
                    }
                    
                    //deleting comments related to the map
                    mysqli_query($con, "DELETE FROM `mapscomments` WHERE `mapid`=".$id);
                    
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
            $dt = time();
            
            //inserting the basic data and returning the map id
            mysqli_query($con, "INSERT INTO `maps` VALUES('','$name','$author','$game','$desc','','0','','0','0','$dt')");
            $id = mysqli_insert_id($con);
            echo "Basic values inserted...<br>";
            echo "The map id is ".$id."<br>";
            
            //create the directory
            mkdir("../img/maps/".$id, 0777);
            echo "Directory created...<br>";
            
            //map file
            switch($_POST["dli"]) {
            
                case "none":
                
                    echo "No download link specified...<br>";
                    mysqli_query($con, "UPDATE `maps` SET `dltype`='2' WHERE `id`=".$id);
                    
                    break;
                
                case "link": 
                    //steam community link
                    
                    $dl = strip($_POST["dl"]);
                    echo "Steam community id read...<br>";
                    mysqli_query($con, "UPDATE `maps` SET `dltype`='1' WHERE `id`=".$id);
                    mysqli_query($con, "UPDATE `maps` SET `dl`='".$dl."' WHERE `id`=".$id);
                    echo "Download url added...<br>";
                    
                    break;
                
                case "file": 
                    //file upload
                    
                    print_r($_FILES)."<br>";
                    
                    $bsp_name = strtolower($_FILES["bsp"]["name"]);
                    $bsp_type = $_FILES["bsp"]["type"];
                    $bsp_size = $_FILES["bsp"]["size"];
                    $bsp_tmp = $_FILES["bsp"]["tmp_name"];
                    $bsp_error = $_FILES["bsp"]["error"];
                    
                    $extension = substr($bsp_name, strpos($bsp_name, ".") + 1);
                    
                    if (!empty($bsp_name)) {
            
                        if ($extension == "bsp" && $bsp_type == "application/octet-stream") {
                
                            $location = "../img/maps/";
                            $dl = $name.".bsp";
                
                            if (move_uploaded_file($bsp_tmp, $location.$dl)) {
                    
                                mysqli_query($con, "UPDATE `maps` SET `dl`='img/maps/".$dl."' WHERE `id`=".$id);
                                echo "File successfully uploaded...<br>";
            
                            } else {
                
                                echo "There was an error while uploading the file. It is highly recommended to try again or change the download to not available.<br>";
                
                            }
                
                        } else {
                
                            echo "The specified file does not appear to be a .bsp. It is highly recommended to try again or change the download to not available.<br>";
                
                        }
                
                    } else {

                        echo "There was no file defined. It is highly recommended to change the download to not available.";
                    
                    }
                    
                    break;
            
            }
            
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
            
            <input type='radio' name='dli' value='file'>bsp file <input type='file' name='bsp'>         
            <br>
            <input type='radio' name='dli' value='link' required>steamcommunity id <input type='text' name='dl'>
            <br>
            <input type='radio' name='dli' value='none'>No download available
            <br>
            Main image<br>
            <input type='file' name='image' required><br>
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
</body>
</html>