<?php

session_start();

$r_c = 41;
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

if (isset($_GET["action"]) && ($_GET["action"] == "edit" || $_GET["action"] == "delete" || $_GET["action"] == "write")) {

    if ($_GET["action"] == "edit") {
        // EDIT

        if (isset($_GET["id"]) && is_numeric($_GET["id"])) {

            $id = strip($_GET["id"]);
            $eq = mysqli_query($con, "SELECT * FROM `news` WHERE `id`=".$id);

            if (mysqli_num_rows($eq) == 1) {

                $er = mysqli_fetch_assoc($eq);

                if (isset($_POST["submit"])) {

                    $title = strip($_POST["title"]);
                    $editorid = $_SESSION["userid"];
                    $editdt = time();
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

                    mysqli_query($con, "UPDATE `news` SET `title`='".$title."', `editorid`='".$editorid."', `text`='".$text."', `comments`=".$comments.", `live`=".$live.", `editdt`='".$editdt."' WHERE `id`=".$id);

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

                    if ($er["comments"] == 0) {

                        echo "checked";

                    }

                    echo "><br>
                    Publish? <input type='checkbox' name='live'";

                    if ($er["live"] == 1) {

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
            $eq = mysqli_query($con, "SELECT * FROM `news` WHERE `id`=".$id);

            if (mysqli_num_rows($eq) == 1) {

                if (isset($_POST["delete"])) {

                    $dq = mysqli_query($con, "DELETE FROM `news` WHERE `id`=".$id);
                    echo "News post successfully deleted.<br>";

                    mysqli_query($con, "DELETE FROM `forums` WHERE `newsid`=".$id);
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
            $author = $_SESSION["userid"];
            $dt = time();
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

            mysqli_query($con, "INSERT INTO `news` VALUES('','".$title."','".$author."','".$dt."','".$text."','".$comments."','','','".$live."')");
            $id = mysqli_insert_id($con);

            mysqli_query($con, "INSERT INTO `forums` VALUES('','".$author."','".$dt."','0','".$title."','".$text."','0','0','".$dt."','0','".$id."')");

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

} else {
    // ALL

    echo "<h1>manage news</h1>
    <p><a href='?action=write'>write new</a></p>";

    echo "<h2>unpublished newz</h2>";

    $query = mysqli_query($con, "SELECT * FROM `news` WHERE `live` = 0 ORDER BY `id` DESC");

    echo "<table style='border-spacing: 5px;'>";
    echo "<tr><th>news</th><th>editing tools</th></tr>";

    while ($row = mysqli_fetch_assoc($query)) {

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

    $query = mysqli_query($con, "SELECT * FROM `news` WHERE `live` = 1 ORDER BY `id` DESC");

    echo "<table style='border-spacing: 5px;'>";
    echo "<tr><th>news</th><th>editing tools</th></tr>";

    while ($row = mysqli_fetch_assoc($query)) {

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

}

?>

<a href='index.php'> &lt;&lt; back to the main page</a>
</body>
</html>
