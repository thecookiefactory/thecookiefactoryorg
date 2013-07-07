<?php

session_start();

$r_c = 41;
require "../inc/functions.php";

if (!checkadmin()) die("403");

?>

<!doctype html>
<html>
<head>
    <meta http-equiv='Content-Type' content='text/html;charset=UTF-8'>
</head>
<body>
<h1>manage your stream</h1>

<?php

if (isset($_POST["submit"])) {

    $twitch = strip($_POST["twitch"]);
    $desc = strip($_POST["description"]);

    if (isset($_POST["active"]) && $_POST["active"] == "on") {

        $active = 1;

    } else {

        $active = 0;

    }

    $uq = mysqli_query($con, "UPDATE `streams` SET `twitch`='".$twitch."', `description`='".$desc."', `active`='".$active."' WHERE `authorid`=".$_SESSION["userid"]);

    echo "Stream successfully updated.<br>";

}

$sq = mysqli_query($con, "SELECT * FROM `streams` WHERE `authorid`=".$_SESSION["userid"]);

if (mysqli_num_rows($sq) == 0) {

    echo "Your stream page is being created now...<br>";
    $cq = mysqli_query($con, "INSERT INTO `streams` VALUES('','".$_SESSION["userid"]."','','','0')");
    echo "Done. Please fill out the fields below.<br>";

}

$sq = mysqli_query($con, "SELECT * FROM `streams` WHERE `authorid`=".$_SESSION["userid"]);
$sr = mysqli_fetch_assoc($sq);

echo "<form action='streams.php' method='post'>
twitch.tv username<br>
<input type='text' name='twitch' value='".$sr["twitch"]."' required><br>
description<br>
<textarea name='description' rows='7' cols='50' required>".$sr["description"]."</textarea><br>
Active stream <input type='checkbox' name='active'";

if ($sr["active"] == 1) {

    echo " checked";

}

echo "> (there is a chance your stream will be live sometime soon)<br>
<input type='submit' name='submit'>
</form>";

?>

<a href='index.php'> &lt;&lt; back to the main page</a>
</body>
</html>