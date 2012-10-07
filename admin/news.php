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
		
			if (isset($_POST["delete"])) {
				// delete
				$id = $_GET["id"];
				$dq = mysql_query("DELETE FROM news WHERE id=$id");
				echo "piece of news successfully deleted";
			} else {
				echo "delete news id ".$_GET["id"];
				echo "<form action='?action=delete&id=".$_GET["id"]."' method='post'>
				<input type='submit' name='delete' value='Yes, delete' /> or just <a href='news.php'>go back</a>
				</form>";
			}
			
		} else {
			echo "no id defined";
		}

	} else { // WRITE WRITE WRITE WRITE WRITE WRITE
		// write
		if (isset($_POST["submit"])) {
			$title = $_POST["title"];
			$author = $_SESSION["username"];
			$date = date("Y-m-d");
			$time = date("H:i", time());
			$text = $_POST["text"];

			if (isset($_POST["comments"]) && $_POST["comments"] == "on") 
				$comments = 0;
			else
				$comments = 1;

			mysql_query("INSERT INTO news VALUES('','$title','$author','$date','$time','$text','$comments')");
			echo "piece of news successfully submitted";
		} else {
			echo "<!doctype html>
			<html>
			<body>
			<h1>post news - by ".$_SESSION["username"]."</h1>
			<form action='?action=write' method='post'>
			Title<br /><input type='text' name='title' /><br />
			Text<br /><textarea name='text'></textarea>
			<br />
			Disable comments<input type='checkbox' name='comments' />
			<br />
			<input type='submit'name='submit' />
			</form>";
		}

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