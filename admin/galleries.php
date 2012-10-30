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
if (isset($_GET["action"]) && ($_GET["action"] == "add" || $_GET["action"] == "delete")) {

} else {
	echo "<h1>manage galleries</h1>";
	$query = mysql_query("SELECT * FROM maps ORDER BY id DESC");
	echo "<table style='border-spacing: 5px;'>";
	echo "<tr><th>map</th><th>number of images</th></tr>";
	while ($row = mysql_fetch_assoc($query)) {
		echo "<tr>";
		echo "<td>";
		echo "<a href='?action=edit&id=".$row["id"]."'>#".$row["id"]." - ".$row["name"]." - ".$row["author"]."</a>";
		echo "</td>";
		echo "<td>";
		$gq = mysql_query("SELECT * FROM gallery WHERE mapid=".$row["id"]);
		echo mysql_num_rows($gq);
		echo "</td>";
		echo "</tr>";
	}
	echo "</table>";
}
?>
</body>
</html>