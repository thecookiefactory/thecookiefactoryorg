<?php

session_start();

$r_c = 1;
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

$query = $con->query("SELECT * FROM `games`");

if (isset($_POST["update"])) {

    while ($r = $query->fetch()) {

        $id = $r["id"];
        $name = strip($_POST[$id."name"]);
        $steamid = strip($_POST[$id."steamid"]);

        if ($name == "" && $steamid == "") {

            $dq = $con->prepare("DELETE FROM `games` WHERE `games`.`id` = :id");
            $dq->bindValue("id", $id, PDO::PARAM_INT);
            $dq->execute();

        } else {

            if (!vf($steamid)) {

                $uq = $con->prepare("UPDATE `games` SET `games`.`name` = :name, `games`.`steamid`= NULL WHERE `games`.`id` = :id");
                $uq->bindValue("name", $name, PDO::PARAM_STR);
                $uq->bindValue("id", $r["id"], PDO::PARAM_INT);
                $uq->execute();

            } else {

                $uq = $con->prepare("UPDATE `games` SET `games`.`name` = :name, `games`.`steamid`= :steamid WHERE `games`.`id` = :id");
                $uq->bindValue("name", $name, PDO::PARAM_STR);
                $uq->bindValue("steamid", $steamid, PDO::PARAM_INT);
                $uq->bindValue("id", $r["id"], PDO::PARAM_INT);
                $uq->execute();

            }

        }

    }

}

if (isset($_POST["addnew"])) {

    $name = strip($_POST["name"]);
    $steamid = strip($_POST["steamid"]);

    if (!vf($steamid)) {

        $iq = $con->prepare("INSERT INTO `games` VALUES('', :name, NULL, now())");
        $iq->bindValue("name", $name, PDO::PARAM_STR);
        $iq->execute();

    } else {

        $iq = $con->prepare("INSERT INTO `games` VALUES('', :name, :steamid, now())");
        $iq->bindValue("name", $name, PDO::PARAM_STR);
        $iq->bindValue("steamid", $steamid, PDO::PARAM_INT);
        $iq->execute();

    }

}

$query = $con->query("SELECT * FROM `games`");

echo "<h1>manage games</h1>";

echo "<form action='games.php' method='post'>";

echo "<table border>";
echo "<tr><th>id</th><th>name</th><th>steamid store id</th></tr>";

while ($row = $query->fetch()) {

    echo "<tr><td>".$row["id"]."</td><td><input type='text' value='".$row["name"]."' name='".$row["id"]."name'></td><td><input type='text' value='".$row["steamid"]."' name='".$row["id"]."steamid'></td></tr>";

}

echo "</table>";

echo "<input type='submit' value='update' name='update'>";
echo "</form>";
echo "<hr>";
echo "<form action='games.php' method='post'>
<input type='text' name='name'><input type='text' name='steamid'><input type='submit' name='addnew' value='add new'>
</form>";

?>

<a href='index.php'> &lt;&lt; back to the main page</a>
</body>
</html>
