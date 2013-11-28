<?php

session_start();

$r_c = 1;
require_once "../inc/functions.php";
require_once "../inc/classes/user.class.php";

$user = new user((isset($_SESSION["userid"]) ? $_SESSION["userid"] : null));

if (!$user->isAdmin()) die("403");

?>

<!doctype html>
<html>
<head>
    <meta http-equiv='Content-Type' content='text/html;charset=UTF-8'>
    <title>thecookiefactory.org admin</title>
</head>
<body>

<?php

$squery = $con->query("SELECT * FROM `forumcategories`");

if (isset($_POST["update"])) {

    while ($srow = $squery->fetch()) {

        $id = $srow["id"];
        $name = strip($_POST[$id."name"]);
        $longname = strip($_POST[$id."longname"]);
        $hexcode = strip($_POST[$id."hexcode"]);
        $hoverhexcode = strip($_POST[$id."hoverhexcode"]);

        if (!vf($name)) {

            $dquery = $con->prepare("DELETE FROM `forumcategories` WHERE `forumcategories`.`id` = :id");
            $dquery->bindValue("id", $id, PDO::PARAM_INT);
            $dquery->execute();

        } else {

            $uquery = $con->prepare("UPDATE `forumcategories` SET `forumcategories`.`name` = :name, `forumcategories`.`longname` = :longname, `forumcategories`.`hexcode` = :hexcode, `forumcategories`.`hoverhexcode` = :hoverhexcode WHERE `forumcategories`.`id` = :id");
            $uquery->bindValue("name", $name, PDO::PARAM_STR);
            $uquery->bindValue("longname", $longname, PDO::PARAM_STR);
            $uquery->bindValue("hexcode", $hexcode, PDO::PARAM_STR);
            $uquery->bindValue("hoverhexcode", $hoverhexcode, PDO::PARAM_STR);
            $uquery->bindValue("id", $id, PDO::PARAM_INT);
            $uquery->execute();

        }

    }

}

if (isset($_POST["addnew"])) {

    $name = strip($_POST["name"]);
    $longname = strip($_POST["longname"]);
    $hexcode = strip($_POST["hexcode"]);
    $hoverhexcode = strip($_POST["hoverhexcode"]);

    $iquery = $con->prepare("INSERT INTO `forumcategories` VALUES(DEFAULT, :name, :longname, :hexcode, :hoverhexcode, DEFAULT)");
    $iquery->bindValue("name", $name, PDO::PARAM_STR);
    $iquery->bindValue("longname", $longname, PDO::PARAM_STR);
    $iquery->bindValue("hexcode", $hexcode, PDO::PARAM_STR);
    $iquery->bindValue("hoverhexcode", $hoverhexcode, PDO::PARAM_STR);
    $iquery->execute();

}

$squery = $con->query("SELECT * FROM `forumcategories`");

echo "<h1>manage forum categories</h1>";

echo "<form action='forumtopics.php' method='post'>";

echo "<table border>";
echo "<tr><th>id</th><th>name</th><th>pretyname</th><th>background-color</th><th>hover background-color</th></tr>";

while ($srow = $squery->fetch()) {

    echo "<tr><td>".$srow["id"]."</td><td><input type='text' value='".$srow["name"]."' name='".$srow["id"]."name'></td><td><input type='text' value='".$srow["longname"]."' name='".$srow["id"]."longname'></td><td><input type='text' value='".$srow["hexcode"]."' name='".$srow["id"]."hexcode'></td><td><input type='text' value='".$srow["hoverhexcode"]."' name='".$srow["id"]."hoverhexcode'></td></tr>";

}

echo "</table>";

echo "<input type='submit' value='update' name='update'>";
echo "</form>";
echo "<hr>";
echo "<form action='forumtopics.php' method='post'>
<input type='text' name='name'><input type='text' name='longname'><input type='text' name='hexcode'><input type='text' name='hoverhexcode'><input type='submit' name='addnew' value='add new'>
</form>";

?>

<a href='index.php'> &lt;&lt; back to the main page</a>
</body>
</html>
