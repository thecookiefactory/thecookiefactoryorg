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

<?php

if (isset($_GET["action"]) && ($_GET["action"] == "edit" || $_GET["action"] == "delete" || $_GET["action"] == "write")) {

	if ($_GET["action"] == "edit") { // EDIT EDIT EDIT EDIT EDIT EDIT
	
		if (isset($_GET["id"])) {
			// edit news id
			echo "edit".$_GET["id"];
		} else {
			echo "no id defined";
		}

	} else if ($_GET["action"] == "delete") { // DELETE DELETE DELETE DELETE DELETE DELETE

		if (isset($_GET["id"])) {
			// delete news id
			echo "delete".$_GET["id"];
		} else {
			echo "no id defined";
		}

	} else { // WRITE WRITE WRITE WRITE WRITE WRITE
		// write
		echo "write";
	}

} else {

	echo "
	<h1>manage news</h1>
	<p><a href='?action=write'>write new</a></p>
	";

	// display all the news

	$query = mysql_query("SELECT * FROM news ORDER BY id DESC");

	echo "<table style='border-spacing: 5px;'>";
	echo "<tr><th>news</th><th>editing tools</th></tr>";

	while ($row = mysql_fetch_assoc($query)) {
		echo "<tr>";
		echo "<td>";
		echo "#".$row["id"]." - ".$row["title"]." - ".substr($row["text"], 0, 100);
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