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

if (isset($_POST["text"])) {

    if (isset($_POST["live"]) && $_POST["live"] == "on") {

        $live = 1;

    } else {

        $live = 0;

    }

    $uquery = $con->prepare("UPDATE `custompages` SET `custompages`.`text` = :text, `custompages`.`title` = :name, `custompages`.`live` = :live, `custompages`.`stringid` = :stringid WHERE `custompages`.`id` = :id");
    $uquery->bindValue("text", strip($_POST["text"]), PDO::PARAM_STR);
    $uquery->bindValue("name", strip($_POST["name"]), PDO::PARAM_STR);
    $uquery->bindValue("live", $live, PDO::PARAM_INT);
    $uquery->bindValue("stringid", strip($_POST["stringid"]), PDO::PARAM_STR);
    $uquery->bindValue("id", strip($_POST["id"]), PDO::PARAM_INT);
    $uquery->execute();

}

if (isset($_POST["create"])) {

    $title = strip($_POST["title"]);
    $iquery = $con->prepare("INSERT INTO `custompages` VALUES(DEFAULT, :title, '', DEFAULT, DEFAULT, DEFAULT, '')");
    $iquery->bindValue("title", $title, PDO::PARAM_STR);
    $iquery->execute();

}

if (isset($_POST["cpage"])) {

    $squery = $con->prepare("SELECT *, BIN(`custompages`.`live`) FROM `custompages` WHERE `custompages`.`title` = :cpage");
    $squery->bindValue("cpage", strip($_POST["cpage"]), PDO::PARAM_STR);
    $squery->execute();

    $srow = $squery->fetch();

    echo "<form action='cpages.php' method='post'>";
    echo "<input type='hidden' name='id' value='".$srow["id"]."'>";
    echo "<input type='text' name='name' value='".$srow["title"]."'>";
    echo "<textarea name='text' rows='30' cols='100'>".$srow["text"]."</textarea>";
    echo "stringid (url): <input type='text' name='stringid' value='".$srow["stringid"]."'>";
    echo "<input type='checkbox' name='live'" . (($srow["BIN(`custompages`.`live`)"] == 1) ? " checked" : "") . ">";
    echo "<input type='submit' value=''>";
    echo "</form>";

} else {

    $squery = $con->query("SELECT * FROM `custompages` ORDER BY `custompages`.`title` ASC");

    echo "<form action='cpages.php' method='post'>";
    echo "<select name='cpage'>";

    while ($srow = $squery->fetch()) {

        echo "<option value='".$srow["title"]."'>".$srow["title"]."</option>";

    }

    echo "</select>";
    echo "<input type='submit' value=''>";
    echo "</form>";

    echo "create new:";
    echo "<form action='cpages.php' method='post'>";
    echo "<input type='text' name='title'>";
    echo "<input type='submit' name='create'>";
    echo "</form>";

}

?>

<a href='index.php'> &lt;&lt; back to the main page</a>
</body>
</html>
