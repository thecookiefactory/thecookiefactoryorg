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

$query = mysqli_query($con, "SELECT * FROM games");
$nr = mysqli_num_rows($query);

echo "<h1>manage games</h1>";

echo "<form action='games.php' method='post'>";

echo "<table border>";
echo "<tr><th>id</th><th>name</th><th>steam store id</th></tr>";
while ($row = mysqli_fetch_assoc($query)) {
echo "<tr><td>".$row["id"]."</td><td><input type='text' value='".$row["name"]."' name=''></td><td><input type='text' value='".$row["steam"]."' name=''></td></tr>";
}
echo "</table>";

echo "<input type='submit' value='update'>";
echo "</form>";
echo "<hr>";
echo "<form action='games.php' method='post'>
<input type='text' name=''><input type='text' name=''><input type='submit' value='add new'>
</form>";

?>

</body>
</html>