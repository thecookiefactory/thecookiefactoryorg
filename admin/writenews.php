<?php
session_start();
$r_c = 42;
include "../inc/essential.php";

if (!checkadmin())
die("must be an dmin :(");

if (isset($_POST["submit"])) {
$title = $_POST["title"];
$author = $_SESSION["username"];
$date = date("Y-m-d");
$time = date("H:i", time());
$text = $_POST["text"];

if ($_POST["comments"] == "on") 
 $comments = 0;
else
 $comments = 1;
 
 mysql_query("INSERT INTO news VALUES('','$title','$author','$date','$time','$text','$comments')");
}

?>
<!doctype html>
<html>
<body>
<h1>post news - by <?php echo $_SESSION["username"] ?></h1>
<form action='writenews.php' method='post'>
Title<br /><input type='text' name='title' /><br />
Text<br /><textarea name='text'>
</textarea>
<br />
Disable comments<input type='checkbox' name='comments' />
<br />
<input type='submit'name='submit' />
</form>
</body>
</html>