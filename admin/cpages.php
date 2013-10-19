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

    $query = $con->prepare("UPDATE `custompages` SET `custompages`.`text` = :text, `custompages`.`title` = :name, `custompages`.`live` = :live WHERE `custompages`.`id` = :id");
    $query->bindValue("text", strip($_POST["text"]), PDO::PARAM_STR);
    $query->bindValue("name", strip($_POST["name"]), PDO::PARAM_STR);
    $query->bindValue("live", $live, PDO::PARAM_INT);
    $query->bindValue("id", strip($_POST["id"]), PDO::PARAM_INT);
    $query->execute();

}

if (isset($_POST["create"])) {

    $title = strip($_POST["title"]);
    $iq = $con->prepare("INSERT INTO `custompages` VALUES(DEFAULT, :title, '', DEFAULT, DEFAULT, 0)");
    $iq->bindValue("title", $title, PDO::PARAM_STR);
    $iq->execute();

}

if (isset($_POST["cpage"])) {

    $q = $con->prepare("SELECT *, BIN(`custompages`.`live`) FROM `custompages` WHERE `custompages`.`title` = :cpage");
    $q->bindValue("cpage", strip($_POST["cpage"]), PDO::PARAM_STR);
    $q->execute();

    $r = $q->fetch();

    echo "<form action='cpages.php' method='post'>";
    echo "<input type='hidden' name='id' value='".$r["id"]."'>";
    echo "<input type='text' name='name' value='".$r["title"]."'>";
    echo "<textarea name='text' rows='30' cols='100'>".$r["text"]."</textarea>";
    echo "<input type='checkbox' name='live'" . (($r["BIN(`custompages`.`live`)"] == 1) ? " checked" : "") . ">";
    echo "<input type='submit' value=''>";
    echo "</form>";

} else {

    $q = $con->query("SELECT * FROM `custompages` ORDER BY `custompages`.`title` ASC");

    echo "<form action='cpages.php' method='post'>";
    echo "<select name='cpage'>";

    while ($r = $q->fetch()) {

        echo "<option value='".$r["title"]."'>".$r["title"]."</option>";

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
