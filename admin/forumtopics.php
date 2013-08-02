<?php

session_start();

$r_c = True;
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

$query = $con->query("SELECT * FROM `forumcategories`");

if (isset($_POST["update"])) {

    while ($r = $query->fetch()) {

        $id = $r["id"];
        $name = strip($_POST[$id."name"]);
        $longname = strip($_POST[$id."longname"]);
        $hexcode = strip($_POST[$id."hexcode"]);
        $hoverhexcode = strip($_POST[$id."hoverhexcode"]);

        if (!vf($name)) {

            $dq = $con->prepare("DELETE FROM `forumcategories` WHERE `forumcategories`.`id` = :id");
            $dq->bindValue("id", $id, PDO::PARAM_INT);
            $dq->execute();

        } else {

            $uq = $con->prepare("UPDATE `forumcategories` SET `forumcategories`.`name` = :name, `forumcategories`.`longname` = :longname, `forumcategories`.`hexcode` = :hexcode, `forumcategories`.`hoverhexcode` = :hoverhexcode WHERE `forumcategories`.`id` = :id");
            $uq->bindValue("name", $name, PDO::PARAM_STR);
            $uq->bindValue("longname", $longname, PDO::PARAM_STR);
            $uq->bindValue("hexcode", $hexcode, PDO::PARAM_STR);
            $uq->bindValue("hoverhexcode", $hoverhexcode, PDO::PARAM_STR);
            $uq->bindValue("id", $id, PDO::PARAM_INT);
            $uq->execute();

        }

    }

}

if (isset($_POST["addnew"])) {

    $name = strip($_POST["name"]);
    $longname = strip($_POST["longname"]);
    $hexcode = strip($_POST["hexcode"]);
    $hoverhexcode = strip($_POST["hoverhexcode"]);

    $iq = $con->prepare("INSERT INTO `forumcategories` VALUES('', :name, :longname, :hexcode, :hoverhexcode, now())");
    $iq->bindValue("name", $name, PDO::PARAM_STR);
    $iq->bindValue("longname", $longname, PDO::PARAM_STR);
    $iq->bindValue("hexcode", $hexcode, PDO::PARAM_STR);
    $iq->bindValue("hoverhexcode", $hoverhexcode, PDO::PARAM_STR);
    $iq->execute();

}

$query = $con->query("SELECT * FROM `forumcategories`");

echo "<h1>manage forum categories</h1>";

echo "<form action='forumtopics.php' method='post'>";

echo "<table border>";
echo "<tr><th>id</th><th>name</th><th>pretyname</th><th>background-color</th><th>hover background-color</th></tr>";

while ($row = $query->fetch()) {

    echo "<tr><td>".$row["id"]."</td><td><input type='text' value='".$row["name"]."' name='".$row["id"]."name'></td><td><input type='text' value='".$row["longname"]."' name='".$row["id"]."longname'></td><td><input type='text' value='".$row["hexcode"]."' name='".$row["id"]."hexcode'></td><td><input type='text' value='".$row["hoverhexcode"]."' name='".$row["id"]."hoverhexcode'></td></tr>";

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
