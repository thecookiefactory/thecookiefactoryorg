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
<h1>manage your stream</h1>

<?php

if (isset($_POST["submit"])) {

    $twitchname = strip($_POST["twitchname"]);
    $desc = strip($_POST["description"]);

    if (isset($_POST["active"]) && $_POST["active"] == "on") {

            $sq = $con->prepare("SELECT `streams`.`id` FROM `streams` WHERE `streams`.`authorid` = :userid");
            $sq->bindValue("userid", $user->getId(), PDO::PARAM_INT);
            $sq->execute();

            if ($sq->rowCount() == 0) {

                $cq = $con->prepare("INSERT INTO `streams` VALUES(DEFAULT, '', '', :userid)");
                $cq ->bindValue("userid", $user->getId(), PDO::PARAM_INT);
                $cq->execute();

            }

        $uq = $con->prepare("UPDATE `streams` SET `streams`.`text` = :text WHERE `streams`.`authorid` = :userid");
        $uq->bindValue("text", $desc, PDO::PARAM_STR);
        $uq->bindValue("userid", $user->getId(), PDO::PARAM_INT);
        $uq->execute();

        $uq = $con->prepare("UPDATE `users` SET `users`.`twitchname` = :twitchname WHERE `users`.`id` = :userid");
        $uq->bindValue("twitchname", $twitchname, PDO::PARAM_STR);
        $uq->bindValue("userid", $user->getId(), PDO::PARAM_INT);
        $uq->execute();

        echo "Stream successfully updated.<br>";

    } else {

        $dq = $con->prepare("DELETE FROM `streams` WHERE `streams`.`authorid` = :userid");
        $dq->bindValue("userid", $user->getId(), PDO::PARAM_INT);
        $dq->execute();

        echo "sttream deleted";

    }



} else {

    $sq = $con->prepare("SELECT * FROM `streams` WHERE `streams`.`authorid` = :userid");
    $sq ->bindValue("userid", $user->getId(), PDO::PARAM_INT);
    $sq->execute();

    $sr = $sq->fetch();

    $uq = $con->prepare("SELECT * FROM `users` WHERE `users`.`id` = :userid");
    $uq ->bindValue("userid", $user->getId(), PDO::PARAM_INT);
    $uq->execute();

    $ur = $uq->fetch();

    echo "<form action='streams.php' method='post'>
    twitchname.tv username<br>
    <input type='text' name='twitchname' value='".$ur["twitchname"]."' required><br>
    description<br>
    <textarea name='description' rows='7' cols='50' required>".$sr["text"]."</textarea><br>
    Active stream <input type='checkbox' name='active' checked> (there is a chance your stream will be live sometime soon) - watch out, if you uncheck this, all data regarding your stream will be lost<br>
    <input type='submit' name='submit'>
    </form>";

}

?>

<a href='index.php'> &lt;&lt; back to the main page</a>
</body>
</html>
