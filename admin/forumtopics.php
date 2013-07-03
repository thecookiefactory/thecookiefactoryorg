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
    $pname = strip($_POST[$id."pname"]);
    $hex = strip($_POST[$id."hex"]);
    $hexh = strip($_POST[$id."hexh"]);
    
    if ($name == "") {
        mysqli_query($con, "DELETE FROM `forumcat` WHERE `id`=".$id);
    } else {
        mysqli_query($con, "UPDATE `forumcat` SET `name`='".$name."', `pname`='".$pname."', `hex`='".$hex."', `hexh`='".$hexh."' WHERE `id`=".$id);
    }
    
    
    }

}

if (isset($_POST["addnew"])) {

    $name = strip($_POST["name"]);
    $pname = strip($_POST["pname"]);
    $hex = strip($_POST["hex"]);
    $hexh = strip($_POST["hexh"]);
    mysqli_query($con, "INSERT INTO `forumcat` VALUES('','".$name."','".$pname."','".$hex."','".$hexh."')");

}

$query = mysqli_query($con, "SELECT * FROM `forumcat`");
echo "<h1>manage forum categories</h1>";

echo "<form action='forumtopics.php' method='post'>";

echo "<table border>";
echo "<tr><th>id</th><th>name</th><th>pretyname</th><th>background-color</th><th>hover background-color</th></tr>";
while ($row = mysqli_fetch_assoc($query)) {

    echo "<tr><td>".$row["id"]."</td><td><input type='text' value='".$row["name"]."' name='".$row["id"]."name'></td><td><input type='text' value='".$row["pname"]."' name='".$row["id"]."pname'></td><td><input type='text' value='".$row["hex"]."' name='".$row["id"]."hex'></td><td><input type='text' value='".$row["hexh"]."' name='".$row["id"]."hexh'></td></tr>";

}
echo "</table>";

echo "<input type='submit' value='update' name='update'>";
echo "</form>";
echo "<hr>";
echo "<form action='forumtopics.php' method='post'>
<input type='text' name='name'><input type='text' name='pname'><input type='text' name='hex'><input type='text' name='hexh'><input type='submit' name='addnew' value='add new'>
</form>";

?>

<a href='index.php'> &lt;&lt; back to the main page</a>
</body>
</html>