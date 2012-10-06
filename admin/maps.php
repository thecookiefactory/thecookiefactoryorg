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

// display all the maps

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

?>
</body>
</html>