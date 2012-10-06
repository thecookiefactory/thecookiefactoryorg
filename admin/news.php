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
<h1>manage news</h1>
<p><a href='#'>write new</a></p>

<?php

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
	echo "<a href='#'>edit</a> <a href='#'>delete</a>";
	echo "</td>";
	echo "</tr>";
}

echo "</table>";

?>
</body>
</html>