<?php

session_start();
$r_c = 42;
require "../inc/essential.php";

if (!checkadmin()) die("403");

?>

<!doctype html>
<html>
<head>
    <meta http-equiv='Content-Type' content='text/html;charset=UTF-8'>
</head>
<body>

<?php

$query = mysqli_query($con, "SELECT * FROM `forumcat`");

if (isset($_POST["update"])) {

    while ($r = mysqli_fetch_assoc($query)) {
    $id = $r["id"];
    $name = strip($_POST[$id."name"]);
    
    if ($name == "") {
        mysqli_query($con, "DELETE FROM `forumcat` WHERE `id`=".$id);
    } else {
        mysqli_query($con, "UPDATE `forumcat` SET `name`='".$name."' WHERE `id`=".$id);
    }
    
    
    }

}

if (isset($_POST["addnew"])) {

    $name = strip($_POST["name"]);
    mysqli_query($con, "INSERT INTO `forumcat` VALUES('','".$name."')");

}

$query = mysqli_query($con, "SELECT * FROM `forumcat`");
echo "<h1>manage forum categories</h1>";

echo "<form action='forumtopics.php' method='post'>";

echo "<table border>";
echo "<tr><th>id</th><th>name</th></tr>";
while ($row = mysqli_fetch_assoc($query)) {

    echo "<tr><td>".$row["id"]."</td><td><input type='text' value='".$row["name"]."' name='".$row["id"]."name'></td></tr>";

}
echo "</table>";

echo "<input type='submit' value='update' name='update'>";
echo "</form>";
echo "<hr>";
echo "<form action='forumtopics.php' method='post'>
<input type='text' name='name'><input type='submit' name='addnew' value='add new'>
</form>";

?>

</body>
</html>