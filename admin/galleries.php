<?php
session_start();
$r_c = 42;
require "../inc/essential.php";

if (!checkadmin())
	die("must be an dmin :(".$_SESSION["username"]);
?>

<!doctype html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
</head>
<body>

<?php
if (isset($_GET["action"]) && ($_GET["action"] == "add" || $_GET["action"] == "edit")) {

	if ($_GET["action"] == "edit" && isset($_GET["id"]) && is_numeric($_GET["id"])) {
	
		$query = mysql_query("SELECT * FROM `gallery` WHERE `id`=".$_GET["id"]);
		if (mysql_num_rows($query) == 0) {
			die("Not a valid id");
		}
		$row = mysql_fetch_assoc($query);
		if (isset($_POST["submit"])) {
		
			if (isset($_POST["delete"]) && $_POST["delete"] == "on") {
				if (unlink("../img/maps/".$row["mapid"]."/".$row["filename"])) {
					mysql_query("DELETE FROM `gallery` WHERE `id`=".$_GET["id"]);
					echo "Image deleted successfully.";
				} else {
					echo "delete process failed";
				}
			} else {
				$desc = htmlentities(mysql_real_escape_string($_POST["desc"]));
				
				$query = mysql_query("UPDATE `gallery` SET `desc`='".$desc."' WHERE `id`=".$_GET["id"]) or die(mysql_error());
				echo "image updated";
			}
		
		} else {
			
			echo "<img style='width: 300px;' src='../img/maps/".$row["mapid"]."/".$row["filename"]."' alt=''>";
			echo "<form action='?action=edit&amp;id=".$_GET["id"]."' method='post'>";
			echo "<input type='text' name='desc' maxlength='100' value='".$row["desc"]."' required><br>";
			echo "<input type='checkbox' name='delete'> Delete pernamently<br>";
			echo "<input type='submit' name='submit'>";
			echo "</form>";
			
		}
	
	} else if ($_GET["action"] == "add" && isset($_GET["id"]) && is_numeric($_GET["id"])) {
		
		$mq = mysql_query("SELECT `name` FROM `maps` WHERE `id`=".$_GET["id"]);
		if (mysql_num_rows($mq) == 0) {
			die("Not a valid id");
		}
		$mr = mysql_fetch_assoc($mq);
		echo "<h1>Add an image to ".$mr["name"]."</h1>";
		
		if (isset($_POST["submit"])) {
		
			$desc = htmlentities(mysql_real_escape_string($_POST["desc"]));
			$mapid = $_GET["id"];
			
			//image variables
			$filename = strtolower($_FILES["image"]["name"]);
			$filetype = $_FILES["image"]["type"];
			$tmp_name = $_FILES["image"]["tmp_name"];
			
			$extension = substr($filename, strpos($filename, ".") + 1);
			
			if (!empty($filename)) {
			
				if (($extension == "jpg" || $extension == "jpeg" || $extension == "png") && ($filetype == "image/jpeg" || $filetype == "image/png")) {
				
					$location = "../img/maps/".$_GET["id"]."/";
				
					if (move_uploaded_file($tmp_name, $location.$filename)) {
					
						mysql_query("INSERT INTO `gallery` VALUES('','".$_GET["id"]."','".$desc."','".$filename."')");
						echo "iuploaded";
			
					} else {
				
						echo "error";
				
					}
				
				} else {
				
					echo "jpg or png only";
				
				}
				
			}
		
		} else {
			
			echo "<form action='?action=add&amp;id=".$_GET["id"]."' method='post' enctype='multipart/form-data'>";
			echo "<input type='file' name='image' required> &lt;= Please choose a name wisely, because it will be kept, also make sure this is unique. jpg/png only<br>";
			echo "description: <input type='text' name='desc' required><br>";
			echo "<input type='submit' name='submit'>";
			echo "</form>";
			
		}
	
	}

} else {
	echo "<h1>manage galleries</h1>";
	$query = mysql_query("SELECT * FROM maps ORDER BY id DESC");
	
	echo "<ul>";
	while ($row = mysql_fetch_assoc($query)) {
		echo "<li>";
		echo "#".$row["id"]." - ".$row["name"]." - ".$row["author"]." - <a href='?action=add&amp;id=".$row["id"]."'>add new image</a>";
		$gq = mysql_query("SELECT * FROM `gallery` WHERE `mapid`=".$row["id"]);
		if (mysql_num_rows($gq) > 0) {
			echo "<ul>";
			while ($gr = mysql_fetch_assoc($gq)) {
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