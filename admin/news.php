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

if (isset($_GET["action"]) && ($_GET["action"] == "edit" || $_GET["action"] == "delete" || $_GET["action"] == "write")) {

    if ($_GET["action"] == "edit") {
        // EDIT

        if (isset($_GET["id"]) && is_numeric($_GET["id"])) {

            $id = strip($_GET["id"]);
            $eq = $con->prepare("SELECT `news`.`id`, `news`.`title`, `news`.`text`, `news`.`authorid`, `news`.`date`, `news`.`editorid`, `news`.`editdate`, BIN(`news`.`comments`), BIN(`news`.`live`)
                                 FROM `news`
                                 WHERE `news`.`id` = :id");
            $eq->bindValue("id", $id, PDO::PARAM_INT);
            $eq->execute();

            if ($eq->rowCount() == 1) {

                $er = $eq->fetch();

                if (isset($_POST["submit"])) {

                    $title = strip($_POST["title"]);
                    $editorid = $user->getId();
                    $text = strip($_POST["text"]);

                    if (isset($_POST["comments"]) && $_POST["comments"] == "on") {

                        $comments = 0;

                    } else {

                        $comments = 1;

                    }

                    if (isset($_POST["live"]) && $_POST["live"] == "on") {

                        $live = 1;

                        if ($er["BIN(`news`.`live`)"] == 0) {

                            $uq = $con->prepare("UPDATE `news` SET `news`.`date` = now() WHERE `news`.`id` = :id");
                            $uq->bindValue("id", $id, PDO::PARAM_INT);
                            $uq->execute();

                            generateid($id);

                        }

                    } else {

                        $live = 0;

                    }

                    $uq = $con->prepare("UPDATE `news` SET `news`.`title` = :title, `news`.`editorid` = :editorid, `news`.`text` = :text, `news`.`comments` = :comments, `news`.`live` = :live WHERE `news`.`id` = :id");
                    $uq->bindValue("title", $title, PDO::PARAM_STR);
                    $uq->bindValue("editorid", $editorid, PDO::PARAM_INT);
                    $uq->bindValue("text", $text, PDO::PARAM_STR);
                    $uq->bindValue("comments", $comments, PDO::PARAM_INT);
                    $uq->bindValue("live", $live, PDO::PARAM_INT);
                    $uq->bindValue("id", $id, PDO::PARAM_INT);
                    $uq->execute();

                    echo "Piece of news successfully updated.<br>";
                    echo "<a href='news.php'>news admin panel</a> - <a href='../index.php?p=news'>news page</a>";

                } else {

                    echo "<h1>edit news</h1>
                    <form action='?action=edit&amp;id=".$id."' method='post'>
                    Title<br>
                    <input type='text' name='title' value='".$er["title"]."' required><br>
                    Text<br>
                    <textarea name='text' rows='10' cols='90' required>".$er["text"]."</textarea><br>
                    Disable comments <input type='checkbox' name='comments'";

                    if ($er["BIN(`news`.`comments`)"] == 0) {

                        echo "checked";

                    }

                    echo "><br>
                    Publish? <input type='checkbox' name='live'";

                    if ($er["BIN(`news`.`live`)"] == 1) {

                        echo "checked";

                    }

                    echo "><br>
                    <input type='submit' name='submit'>
                    </form>";

                }

            } else {

                echo "The specified id returned no news post.<br>";
                echo "<a href='news.php'>news admin panel</a> - <a href='../index.php?p=news'>news page</a>";

            }

        } else {

            echo "There was no id defined.<br>";
            echo "<a href='news.php'>news admin panel</a> - <a href='../index.php?p=news'>news page</a>";

        }

    } else if ($_GET["action"] == "delete") {
        // DELETE

        if (isset($_GET["id"]) && is_numeric($_GET["id"])) {

            $id = strip($_GET["id"]);
            $eq = $con->prepare("SELECT * FROM `news` WHERE `news`.`id` = :id");
            $eq->bindValue("id", $id, PDO::PARAM_INT);
            $eq->execute();

            if ($eq->rowCount() == 1) {

                if (isset($_POST["delete"])) {

                    $dq = $con->prepare("DELETE FROM `news` WHERE `news`.`id` = :id");
                    $dq->bindValue("id", $id, PDO::PARAM_INT);
                    $dq->execute();
                    echo "News post successfully deleted.<br>";

                    $dq = $con->prepare("DELETE FROM `forumthreads` WHERE `forumthreads`.`newsid` = :id");
                    $dq->bindValue("id", $id, PDO::PARAM_INT);
                    $dq->execute();
                    // comments are not actually deleted at this point, but w/e
                    echo "Related comments successfully deleted.<br>";
                    echo "<a href='news.php'>news admin panel</a> - <a href='../index.php?p=news'>news page</a>";

                } else {

                    echo "Delete news id ".$id;
                    echo "<form action='?action=delete&amp;id=".$id."' method='post'>
                    <input type='submit' name='delete' value='Yes, delete'> or <a href='news.php'>news admin panel</a> - <a href='../index.php?p=news'>news page</a>
                    </form>";

                }

            } else {

                echo "The specified id returned no news post.<br>";
                echo "<a href='news.php'>news admin panel</a> - <a href='../index.php?p=news'>news page</a>";

            }

        } else {

            echo "There was no id defined.<br>";
            echo "<a href='news.php'>news admin panel</a> - <a href='../index.php?p=news'>news page</a>";

        }

    } else {
        // WRITE

        if (isset($_POST["submit"])) {

            $title = strip($_POST["title"]);
            $author = $user->getId();
            $text = strip($_POST["text"]);

            if (isset($_POST["comments"]) && $_POST["comments"] == "on") {

                $comments = 0;

            } else {

                $comments = 1;

            }

            if (isset($_POST["live"]) && $_POST["live"] == "on") {

                $live = 1;

            } else {

                $live = 0;

            }

            $iq = $con->prepare("INSERT INTO `news` VALUES(DEFAULT, :title, :text, :author, DEFAULT, DEFAULT, DEFAULT, :comments, :live, '')");
            $iq->bindValue("title", $title, PDO::PARAM_STR);
            $iq->bindValue("text", $text, PDO::PARAM_STR);
            $iq->bindValue("author", $author, PDO::PARAM_INT);
            $iq->bindValue("comments", $comments, PDO::PARAM_INT);
            $iq->bindValue("live", $live, PDO::PARAM_INT);
            $iq->execute();

            $id = $con->lastInsertId();

            $iq = $con->prepare("INSERT INTO `forumthreads` VALUES(DEFAULT, :title, :text, :author, DEFAULT, DEFAULT, DEFAULT, 0, DEFAULT, :id, 0)");
            $iq->bindValue("title", $title, PDO::PARAM_STR);
            $iq->bindValue("text", $text, PDO::PARAM_STR);
            $iq->bindValue("author", $author, PDO::PARAM_INT);
            $iq->bindValue("id", $id, PDO::PARAM_INT);
            $iq->execute();

            generateid($id);

            echo "News post successfully submitted.<br>";
            echo "<a href='news.php'>news admin panel</a> - <a href='../index.php?p=news'>news page</a>";

        } else {

            echo "<h1>post news</h1>
            <form action='?action=write' method='post'>
            Title<br>
            <input type='text' name='title' required><br>
            Text<br>
            <textarea name='text' rows='10' cols='90' required></textarea><br>
            Disable comments <input type='checkbox' name='comments'><br>
            Publish? <input type='checkbox' name='live'><br>
            <input type='submit' name='submit'>
            </form>";

        }

    }

    // update the rss feed
    exec($config["python"]["rss"], $output, $return);

    if (!$return)
        echo "RSS feed updated!";
    else
        echo "RSS feed update failed!";

} else {
    // ALL

    echo "<h1>manage news</h1>
    <p><a href='?action=write'>write new</a></p>";

    echo "<h2>unpublished newz</h2>";

    $query = $con->query("SELECT * FROM `news` WHERE `news`.`live` = 0 ORDER BY `news`.`id` DESC");

    echo "<table style='border-spacing: 5px;'>";
    echo "<tr><th>news</th><th>editing tools</th></tr>";

    while ($row = $query->fetch()) {

        echo "<tr>";
        echo "<td>";
        echo "#".$row["id"]." - ".$row["title"]." - ".substr($row["text"], 0, 100);
        echo "</td>";
        echo "<td>";
        echo "<a href='?action=edit&amp;id=".$row["id"]."'>edit</a> <a href='?action=delete&amp;id=".$row["id"]."'>delete</a>";
        echo "</td>";
        echo "</tr>";

    }

    echo "</table>";

    echo "<h2>published newz</h2>";

    $query = $con->query("SELECT * FROM `news` WHERE `news`.`live` = 1 ORDER BY `news`.`id` DESC");

    echo "<table style='border-spacing: 5px;'>";
    echo "<tr><th>news</th><th>editing tools</th></tr>";

    while ($row = $query->fetch()) {

        echo "<tr>";
        echo "<td>";
        echo "#".$row["id"]." - ".$row["title"]." - ".substr($row["text"], 0, 100);
        echo "</td>";
        echo "<td>";
        echo "<a href='?action=edit&amp;id=".$row["id"]."'>edit</a> <a href='?action=delete&amp;id=".$row["id"]."'>delete</a>";
        echo "</td>";
        echo "</tr>";

        if (!vf($row["stringid"])) {

            generateid($row["id"]);

        }

    }

    echo "</table>";

}

function generateid($x) {

    global $con;

    $gq = $con->prepare("SELECT `news`.`id`, `news`.`title` FROM `news` WHERE `news`.`id` = :id");
    $gq->bindValue("id", $x, PDO::PARAM_INT);
    $gq->execute();

    $gr = $gq->fetch();

    $stringid = preg_replace("/[^A-Za-z0-9 ]/", "", $gr["title"]);

    $stringid = str_replace(" ", "_", $stringid);

    $cq = $con->prepare("SELECT `news`.`id` FROM `news` WHERE `news`.`stringid` = :si");
    $cq->bindValue("si", $stringid, PDO::PARAM_STR);
    $cq->execute();

    if ($cq->rowCount() != 0) {

        $stringid .= "-".$x;

    }

    $uq = $con->prepare("UPDATE `news` SET `news`.`stringid` = :si WHERE `news`.`id` = :id");
    $uq->bindValue("si", $stringid, PDO::PARAM_STR);
    $uq->bindValue("id", $x, PDO::PARAM_INT);
    $uq->execute();

    return 0;

}

?>

<a href='index.php'> &lt;&lt; back to the main page</a>
</body>
</html>
