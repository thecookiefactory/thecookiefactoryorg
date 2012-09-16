<?php

include "../inc/connect.php";

if (isset($_POST["submit"])) {
$title = $_POST["title"];
$author = "mici"; //$_SESSION["username"];
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

<form action='writenews.php' method='post'>
Title<br /><input type='text' name='title' /><br />
Text<br /><textarea name='text'>
</textarea>
<br />
Disable comments<input type='checkbox' name='comments' />
<br />
<input type='submit'name='submit' />
</form>