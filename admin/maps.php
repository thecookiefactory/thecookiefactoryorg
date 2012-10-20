<?php
session_start();
$r_c = 42;
require "../inc/essential.php";

if (!checkadmin())
	die("must be an dmin :(".$_SESSION["username"]);
?>

<!doctype html>
<html>
<body>
<h1>manage maps</h1>
<p><a href='#'>add new</a></p>

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
				} else {
					echo "delete map id ".$_GET["id"];
					echo "<form action='?action=delete&id=".$_GET["id"]."' method='post'>
					<input type='submit' name='delete' value='Yes, delete' /> or just <a href='maps.php'>go back</a>
					</form>";
				}
			} else {
				echo "wrong id";
			}
		} else {
			echo "no id defined";
		}
	} else { // WRITE WRITE WRITE WRITE WRITE WRITE
	
	}
	} else { // display all the maps
		$query = mysql_query("SELECT * FROM maps ORDER BY id DESC");
		echo "<table style='border-spacing: 5px;'>";
		echo "<tr><th>maps</th><th>editing tools</th></tr>";

		while ($row = mysql_fetch_assoc($query)) {
			echo "<tr>";
			echo "<td>";
			echo "#".$row["id"]." - ".$row["name"]." - ".$row["author"];
			echo "</td>";
			echo "<td>";
			echo "<a href='#'>edit</a> <a href='#'>delete</a>";
			echo "</td>";
			echo "</tr>";
}

echo "</table>";
}

?>
</body>
</html>