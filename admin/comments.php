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
if (isset($_GET["id"]) && is_numeric($_GET["id"])) {
	$id = $_GET["id"];
	$eq = mysql_query("SELECT * FROM newscomments WHERE id=$id");
	if (mysql_num_rows($eq) == 1) {
		$er = mysql_fetch_assoc($eq);
		if (isset($_POST["delete"])) {
			$id = $_GET["id"];
			$dq = mysql_query("DELETE FROM newscomments WHERE id=$id");
			echo "piece of news successfully deleted";
			echo "<a href='../index.php?p=news'>go back</a>";
		} else {
			echo "delete comment id ".$_GET["id"]."(".$er["text"].")";
			echo "<form action='?action=delete&id=".$_GET["id"]."' method='post'>
			<input type='submit' name='delete' value='Yes, delete' /> or just <a href='news.php'>go back</a>
			</form>";
		}
	} else {
		echo "wrong id";
		echo "<a href='../index.php?p=news'>go back</a>";
	}
} else {
	echo "no id defined";
	echo "<a href='../index.php?p=news'>go back</a>";
}
?>
</body>
</html>