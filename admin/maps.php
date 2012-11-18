<?php
session_start();
$r_c = 42;
require "../inc/essential.php";

if (!checkadmin())
	die("must be an dmin :(".$_SESSION["userid"]);
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
		
		$id = $_GET["id"];
		$eq = mysqli_query($con, "SELECT * FROM `maps` WHERE `id`=$id");
		
		if (isset($_POST["submit"])) {
		
			$mr = mysqli_fetch_assoc($eq);
		
			$name = mysqli_real_escape_string($con, $_POST["name"]);
			$game = $_POST["game"];
			$desc = mysqli_real_escape_string($con, $_POST["desc"]);
			
			switch($_POST["dli"]) {
				case "file": $dli = 0;
					//file upload
					$bsp_name = $_FILES["bsp"]["name"];
					$bsp_type = $_FILES["bsp"]["type"];
					$bsp_size = $_FILES["bsp"]["size"];
					$bsp_tmp = $_FILES["bsp"]["tmp_name"];
					$bsp_error = $_FILES["bsp"]["error"];
					
					$extension = substr($bsp_name, strpos($bsp_name, ".") + 1);
					
					if (!empty($bsp_name) && $bsp_size > 0) {
			
						if ($extension == "bsp" && $bsp_type == "application/octet-stream") {
				
							$location = "../img/maps/";
							$dl = $name.".bsp";
							if (file_exists("../img/maps/".$name.".bsp")) { echo "deleting bsp"; unlink("../img/maps/".$name.".bsp"); } else { echo "no old bsp found"; } 
							echo "bsp_tmp: ".$bsp_tmp;
							echo "<br>location: ".$location.$dl."<br>";
				
							if (move_uploaded_file($bsp_tmp, $location.$dl)) {
					
								echo "file uploaded...<br>";
								mysqli_query($con, "UPDATE `maps` SET `dl`='img/maps/".$dl."' WHERE `id`=".$id);
								echo "download url added...<br>";
			
							} else {
				
								echo "error uploading<br>";
				
							}
				
						} else {
				
							echo "not a bsp file";
				
						}
				
					} else echo "no file defined this si bad";
				



				break;
			

				case "link": $dli = 1; if (file_exists("../img/maps/".$name.".bsp")) { echo "deleting bsp"; unlink("../img/maps/".$name.".bsp"); } else { echo "no old bsp found"; } 
					$dl = $_POST["dl"]; $uq = mysqli_query($con, "UPDATE maps SET `dl`='".$dl."' WHERE `id`=".$id) or die(mysqli_error()); break;
				case "none": $dli = 2; if (file_exists("../img/maps/".$name.".bsp")) { echo "deleting bsp"; unlink("../img/maps/".$name.".bsp"); } else { echo "no old bsp found"; } break;
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
					echo "old image deleted";
				
					if (move_uploaded_file($image_tmp, $location.$id.".".$extension)) {
					
						echo "image file uploaded...<br>";
						mysqli_query($con, "UPDATE `maps` SET `ext`='".$extension."' WHERE `id`=".$id);
						echo "extension saved...<br>";
			
					} else {
				
						echo "error";
				
					}
				
				} else {
				
					echo "jpg or png only";
				
				}
				
			} else {
				echo "there is no new image";
			}
		
			$uq = mysqli_query($con, "UPDATE maps SET `name`='".$name."', `game`='".$game."', `desc`='".$desc."', `dltype`='".$dli."' WHERE `id`=".$id) or die(mysqli_error());
		}
		
		if (mysqli_num_rows($eq) == 1) {
			
			//fetching the current data
			$eq = mysqli_query($con, "SELECT * FROM `maps` WHERE `id`=$id");
			$mr = mysqli_fetch_assoc($eq);
			
			echo "<form action='?action=edit&amp;id=".$id."' method='post' enctype='multipart/form-data'>
			Name<br>
			<input type='text' name='name' value='".$mr["name"]."' required><br>
			Associated game<br>
			<select name='game'>
				<option value='1' "; if ($mr["game"] == 1) echo "selected "; echo ">Team Fortress 2</option>
				<option value='2' "; if ($mr["game"] == 2) echo "selected "; echo ">Portal 2</option>
			</select><br>
			Description<br>
			<textarea name='desc' required>".$mr["desc"]."</textarea><br>
			
			<input type='radio' name='dli' value='link' "; if ($mr["dltype"] == 1) echo "checked "; echo " required>download link <input type='text' name='dl' value='"; if ($mr["dltype"] == 1) echo $mr["dl"]; echo "'>
			<br>
			<input type='radio' name='dli' value='file'  "; if ($mr["dltype"] == 0) echo "checked "; echo ">bsp file <input type='file' name='bsp'>			
			<br>
			<input type='radio' name='dli' value='none'  "; if ($mr["dltype"] == 2) echo "checked "; echo ">no dl
			<br>
			main image<br>
			<img style='width: 300px;' src='../img/maps/".$mr["id"].".".$mr["ext"]."' alt=''>
			<br>
			<input type='file' name='image'><br>
			<input type='submit' name='submit'>
			</form>";
		}
		
	} else if ($_GET["action"] == "delete") {	// DELETE DELETE DELETE DELETE DELETE DELETE
	
		if (isset($_GET["id"]) && is_numeric($_GET["id"])) {
		
			$id = $_GET["id"];
			$eq = mysqli_query($con, "SELECT * FROM `maps` WHERE `id`=$id");
			if (mysqli_num_rows($eq) == 1) {
			
				$er = mysqli_fetch_assoc($eq);
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
					$gq = mysqli_query($con, "SELECT * FROM `gallery` WHERE `mapid`=".$id);
					
					while ($gr = mysqli_fetch_assoc($gq)) {
						unlink("../img/maps/".$er["id"]."/".$gr["filename"]);
						mysqli_query($con, "DELETE FROM `gallery` WHERE `id`=".$gr["id"]);
					}
					
					//deleting comments related to the map
					
					$dq = mysqli_query($con, "DELETE FROM `maps` WHERE `id`=$id");
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
			$name = mysqli_real_escape_string($con, $_POST["name"]);
			$author = $_SESSION["userid"];
			$game = $_POST["game"];
			$desc = mysqli_real_escape_string($con, $_POST["desc"]);
			$date = date("Y-m-d");
			
			echo "basic values read...<br>";
			
			//inserting the basic data and returning the map id
			mysqli_query($con, "INSERT INTO `maps` VALUES('','$name','$author','$game','$desc','','0','','0','0','$date')");
			$id = mysqli_insert_id($con);
			echo "basic values inserted...<br>";
			echo "the id is ".$id."<br>";
			
			//create the directory
			mkdir("../img/maps/".$id, 0777);
			echo "directory created...<br>";
			
			//map file
			switch($_POST["dli"]) {
				case "none": 
					echo "no download specified...<br>";
					mysqli_query($con, "UPDATE `maps` SET `dltype`='2' WHERE `id`=".$id);
					break;
				case "link": 
					//steam community link
					$dl = mysqli_real_escape_string($con, $_POST["dl"]);
					echo "steam community url read...<br>";
					mysqli_query($con, "UPDATE `maps` SET `dltype`='1' WHERE `id`=".$id);
					mysqli_query($con, "UPDATE `maps` SET `dl`='".$dl."' WHERE `id`=".$id);
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
								mysqli_query($con, "UPDATE `maps` SET `dl`='img/maps/".$dl."' WHERE `id`=".$id);
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
						mysqli_query($con, "UPDATE `maps` SET `ext`='".$extension."' WHERE `id`=".$id);
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
			echo "<h1>post a map - by ".getname($_SESSION["userid"])."</h1>
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