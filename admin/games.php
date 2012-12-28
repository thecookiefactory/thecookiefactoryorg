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

$query = mysqli_query($con, "SELECT * FROM `games`");

if (isset($_POST["update"])) {

    while ($r = mysqli_fetch_assoc($query)) {
    $id = $r["id"];
    $name = strip($_POST[$id."name"]);
    $steam = strip($_POST[$id."steam"]);
    
    mysqli_query($con, "UPDATE `games` SET `name`='".$name."', `steam`='".$steam."' WHERE `id`=".$id);
    }

}

if (isset($_POST["addnew"])) {

    $name = strip($_POST["name"]);
    $steam = strip($_POST["steam"]);
    mysqli_query($con, "INSERT INTO `games` VALUES('','".$name."','".$steam."')");

}

$query = mysqli_query($con, "SELECT * FROM `games`");
echo "<h1>manage games</h1>";

echo "<form action='games.php' method='post'>";

echo "<table border>";
echo "<tr><th>id</th><th>name</th><th>steam store id</th></tr>";
while ($row = mysqli_fetch_assoc($query)) {

    echo "<tr><td>".$row["id"]."</td><td><input type='text' value='".$row["name"]."' name='".$row["id"]."name'></td><td><input type='text' value='".$row["steam"]."' name='".$row["id"]."steam'></td></tr>";

}
echo "</table>";

echo "<input type='submit' value='update' name='update'>";
echo "</form>";
echo "<hr>";
echo "<form action='games.php' method='post'>
<input type='text' name='name'><input type='text' name='steam'><input type='submit' name='addnew' value='add new'>
</form>";

?>

</body>
</html>