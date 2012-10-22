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
	
	} else if ($_GET["action"] == "delete") { // DELETE DELETE DELETE DELETE DELETE DELETE
		if (isset($_GET["id"]) && is_numeric($_GET["id"])) {
			$id = $_GET["id"];
			$eq = mysql_query("SELECT * FROM maps WHERE id=$id");
			if (mysql_num_rows($eq) == 1) {
				if (isset($_POST["delete"])) {
					$id = $_GET["id"];
					$dq = mysql_query("DELETE FROM maps WHERE id=$id");
					echo "map successfully deleted";
					echo "<a href='maps.php'>go back</a>";
				} else {
					echo "delete map id ".$_GET["id"];
					echo "<form action='?action=delete&id=".$_GET["id"]."' method='post'>
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
			$name = $_POST["name"];
			$author = $_SESSION["username"];
			$game = $_POST["game"];
			$desc = $_POST["desc"];
			$dl = $_POST["dl"];
			$date = date("Y-m-d");

			if (isset($_POST["gallery"]) && $_POST["gallery"] == "on") 
				$gallery = 1;
			else
				$gallery = 0;

			mysql_query("INSERT INTO maps VALUES('','$name','$author','$game','$desc','$dl','0','0','$date','$gallery')");
			echo "map successfully submitted";
			echo "<a href='maps.php'>go back</a>";
		} else {
			echo "<h1>post a map - by ".$_SESSION["username"]."</h1>
			<form action='?action=write' method='post'>
			Name<br /><input type='text' name='name' /><br />
			<select name='game'>
				<option value='1'>Team Fortress 2</option>
				<option value='2'>Portal 2</option>
			</select>
			Description<br /><textarea name='desc'></textarea><br />
			download link<br /><input type='text' name='dl' /><br />
			Create a gallery<input type='checkbox' name='gallery' />
			<br />
			<input type='submit'name='submit' />
			</form>";
		}
	}
	} else { // display all the maps
		echo "<h1>manage maps</h1>
		<p><a href='?action=write'>add new</a></p>";
		$query = mysql_query("SELECT * FROM maps ORDER BY id DESC");
		echo "<table style='border-spacing: 5px;'>";
		echo "<tr><th>maps</th><th>editing tools</th></tr>";

		while ($row = mysql_fetch_assoc($query)) {
			echo "<tr>";
			echo "<td>";
			echo "#".$row["id"]." - ".$row["name"]." - ".$row["author"];
			echo "</td>";
			echo "<td>";
			echo "<a href='?action=edit&id=".$row["id"]."'>edit</a> <a href='?action=delete&id=".$row["id"]."'>delete</a>";
			echo "</td>";
			echo "</tr>";
}

echo "</table>";
}

?>
</body>
</html>