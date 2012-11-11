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


if (isset($_GET["action"]) && ($_GET["action"] == "edit" || $_GET["action"] == "delete" || $_GET["action"] == "write")) {
	if ($_GET["action"] == "edit") { // EDIT EDIT EDIT EDIT EDIT EDIT
		
	} else if ($_GET["action"] == "delete") {	// DELETE DELETE DELETE DELETE DELETE DELETE
	
		if (isset($_GET["id"]) && is_numeric($_GET["id"])) {
		
			$id = $_GET["id"];
			$eq = mysql_query("SELECT * FROM `maps` WHERE `id`=$id");
			if (mysql_num_rows($eq) == 1) {
			
				$er = mysql_fetch_assoc($eq);
				if (isset($_POST["delete"])) {
					$id = $_GET["id"];
					
					//deleting the bsp if present
					if ($er["dltype"] == 0) {
						unlink("../".$er["dl"]);
						echo "bsp deleted";
					}
					
					//deleting the main image
					unlink("../img/maps/".$er["id"].".".$er["ext"]);
					
					//deleting images from the gallery
					$gq = mysql_query("SELECT * FROM `gallery` WHERE `mapid`=".$id);
					
					while ($gr = mysql_fetch_assoc($gq)) {
						unlink("../img/maps/".$er["id"]."/".$gr["filename"]);
						mysql_query("DELETE FROM `gallery` WHERE `id`=".$gr["id"]);
					}
					
					//deleting comments related to the map
					
					$dq = mysql_query("DELETE FROM `maps` WHERE `id`=$id");
					rmdir("../img/maps/".$id);
					echo "map successfully deleted";
					echo "<a href='maps.php'>go back</a>";
				} else {
					echo "delete map id ".$_GET["id"];
					echo "<form action='?action=delete&amp;id=".$_GET["id"]."' method='post'>
					<input type='submit' name='delete' value='Yes, delete' /> or just <a href='maps.php'>go back</a>
					</form>";
				}
			} else {
				echo "wrong id";
				echo "<a href='maps.php'>go back</a>";
			}
		} else {
			echo "no id defined";
			echo "<a href='maps.php'>go back</a>";
		}
		
	} else { // WRITE WRITE WRITE WRITE WRITE WRITE
		
		if (isset($_POST["submit"])) {
		
			echo "map creating process begins...<br>";
			
			//basic values
			$name = mysql_real_escape_string($_POST["name"]);
			$author = $_SESSION["username"];
			$game = $_POST["game"];
			$desc = mysql_real_escape_string($_POST["desc"]);
			$date = date("Y-m-d");
			
			echo "basic values read...<br>";
			
			//inserting the basic data and returning the map id
			mysql_query("INSERT INTO `maps` VALUES('','$name','$author','$game','$desc','','0','','0','0','$date')");
			$id = mysql_insert_id();
			echo "basic values inserted...<br>";
			echo "the id is ".$id."<br>";
			
			//create the directory
			mkdir("../img/maps/".$id, 0777);
			echo "directory created...<br>";
			
			//map file
			switch($_POST["dli"]) {
				case "none": 
					echo "no download specified...<br>";
					mysql_query("UPDATE `maps` SET `dltype`='2' WHERE `id`=".$id);
					break;
				case "link": 
					//steam community link
					$dl = mysql_real_escape_string($_POST["dl"]);
					echo "steam community url read...<br>";
					mysql_query("UPDATE `maps` SET `dltype`='1' WHERE `id`=".$id);
					mysql_query("UPDATE `maps` SET `dl`='".$dl."' WHERE `id`=".$id);
					echo "download url added...<br>";
					break;
				case "file": 
					print_r($_FILES)."<br>";
					//file upload
					$bsp_name = $_FILES["bsp"]["name"];
					$bsp_type = $_FILES["bsp"]["type"];
					$bsp_size = $_FILES["bsp"]["size"];
					$bsp_tmp = $_FILES["bsp"]["tmp_name"];
					$bsp_error = $_FILES["bsp"]["error"];
					
					$extension = substr($bsp_name, strpos($bsp_name, ".") + 1);
					
					if (!empty($bsp_name)) {
			
						if ($extension == "bsp" && $bsp_type == "application/octet-stream") {
				
							$location = "../img/maps/";
							$dl = $name.".bsp";
							
							echo "bsp_tmp: ".$bsp_tmp;
							echo "<br>location: ".$location.$dl."<br>";
				
							if (move_uploaded_file($bsp_tmp, $location.$dl)) {
					
								echo "file uploaded...<br>";
								mysql_query("UPDATE `maps` SET `dl`='img/maps/".$dl."' WHERE `id`=".$id);
								echo "download url added...<br>";
			
							} else {
				
								echo "error uploading<br>";
				
							}
				
						} else {
				
							echo "not a bsp file";
				
						}
				
					} else echo "no file defined";
					
					break;
			}
			
			//image file
			$image_name = $_FILES["image"]["name"];
			$image_size = $_FILES["image"]["size"];
			$image_type = $_FILES["image"]["type"];
			$image_tmp = $_FILES["image"]["tmp_name"];
			
			$extension = substr($image_name, strpos($image_name, ".") + 1);
			
			if (!empty($image_name)) {
			
				if (($extension == "jpg" || $extension == "jpeg" || $extension == "png") && ($image_type == "image/jpeg" || $image_type == "image/png")) {
				
					$location = "../img/maps/";
				
					if (move_uploaded_file($image_tmp, $location.$id.".".$extension)) {
					
						echo "image file uploaded...<br>";
						mysql_query("UPDATE `maps` SET `ext`='".$extension."' WHERE `id`=".$id);
						echo "extension saved...<br>";
			
					} else {
				
						echo "error";
				
					}
				
				} else {
				
					echo "jpg or png only";
				
				}
				
			}
						
			echo "map successfully submitted";
			echo "<a href='maps.php'>go back</a>";
		} else {
			echo "<h1>post a map - by ".$_SESSION["username"]."</h1>
			<form action='?action=write' method='post' enctype='multipart/form-data'>
			Name<br>
			<input type='text' name='name' required><br>
			Associated game<br>
			<select name='game'>
				<option value='1'>Team Fortress 2</option>
				<option value='2'>Portal 2</option>
			</select><br>
			Description<br>
			<textarea name='desc' required></textarea><br>
			
			<input type='radio' name='dli' value='link' required>download link <input type='url' name='dl'>
			<br>
			<input type='radio' name='dli' value='file'>bsp file <input type='file' name='bsp'>			
			<br>
			<input type='radio' name='dli' value='none'>no dl
			<br>
			main image<br>
			<input type='file' name='image' required><br>
			<input type='submit' name='submit'>
			</form>";
		}
		
	}
} else { // display all the maps
	echo "<h1>manage maps</h1>
	<p><a href='?action=write'>add new</a></p>";
	$query = mysql_query("SELECT * FROM `maps` ORDER BY `id` DESC");
	echo "<table style='border-spacing: 5px;'>";
	echo "<tr><th>maps</th><th>editing tools</th></tr>";

	while ($row = mysql_fetch_assoc($query)) {
		echo "<tr>";
		echo "<td>";
		echo "#".$row["id"]." - ".$row["name"]." - ".$row["author"];
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