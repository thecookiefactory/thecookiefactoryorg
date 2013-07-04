<?php

session_start();
$r_c = 42;
require "../inc/functions.php";

if (!checkadmin()) die("403");

?>

<!doctype html>
<html>
<head>
    <meta http-equiv='Content-Type' content='text/html;charset=UTF-8'>
</head>
<body>
<?php

$q = mysqli_query($con, "SELECT * FROM `cpages` ORDER BY `name` ASC");
echo "<form action='cpages.php' method='post'>";
echo "<select name='cpage'>";
while ($r = mysqli_fetch_assoc($q)) {
echo "<option value='".$r["name"]."'>".$r["name"]."</option>";
}
echo "</select>";
echo "<input type='submit' value=''>";
echo "</form>";

?>

<?php

if (isset($_POST["cpage"])) {
$q = mysqli_query($con, "SELECT * FROM `cpages` WHERE `name`='".strip($_POST["cpage"])."'");
$r = mysqli_fetch_assoc($q);
echo "<form action='cpages.php' method='post'>";
echo "<input type='hidden' name='id' value='".$r["id"]."'>";
echo "<input type='text' name='name' value='".$r["name"]."'>";
echo "<textarea name='text' rows='30' cols='100'>".$r["text"]."</textarea>";
echo "<input type='submit' value=''>";
echo "</form>";
}

if (isset($_POST["text"])) {
$editdt = time();
mysqli_query($con, "UPDATE `cpages` SET `text`='".strip($_POST["text"])."', `name`='".strip($_POST["name"])."', editdt='".$editdt."' WHERE `id`=".strip($_POST["id"]));
}

?>
<a href='index.php'> &lt;&lt; back to the main page</a>
</body>
</html>