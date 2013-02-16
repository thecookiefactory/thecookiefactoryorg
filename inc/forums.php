<?php

checkembed($r_c);
include "analyticstracking.php";
include "markdown/markdown.php";

$_SESSION["lp"] = "forums";

$action = isset($_GET["action"]) ? strip($_GET["action"]) : "";

if ($action == "add" && checkuser()) {

    if (isset($_POST["addnew"])) {

        $authorid = $_SESSION["userid"];
        $cat = strip($_POST["cat"]);
        $title = strip($_POST["title"]);
        $text = strip($_POST["text"]);
        $dt = time();

        mysqli_query($con, "INSERT INTO `forums` VALUES('','".$authorid."','".$dt."','".$title."','".$text."','".$cat."','0','".$dt."','0')");
        ?>
        added.
        <?php

    } else {

        ?>
        <form action='?p=forums&amp;action=add' method='post'>
        <input type='text' name='title' required>
        <textarea name='text' required></textarea>
        select cateryogy
        <select name='cat'>
        <?php
        $cq = mysqli_query($con, "SELECT * FROM `forumcat` ORDER BY `name` ASC");

        while ($cr = mysqli_fetch_assoc($cq)) {

            ?>
            <option value='<?php echo $cr["id"]; ?>'><?php echo $cr["name"]; ?></option>
            <?php

        }
        ?>
        </select>
        <input type='submit' name='addnew'>
        </form>
        <?php

    }

} else {

    if (isset($_GET["id"]) && is_numeric($_GET["id"])) {

        // SHOW ONE THREAD
        $query = mysqli_query($con, "SELECT * FROM `forums` WHERE `id`=".$_GET["id"]);
        $row = mysqli_fetch_assoc($query);

        //comment processing
        if (isset($_POST["cp"]) && trim($_POST["text"]) != "") {

            $tid = strip($_GET["id"]);
            $author = $_SESSION["userid"];
            $text = strip($_POST["text"]);
            $dt = time();

            $iq = mysqli_query($con, "INSERT INTO `forumposts` VALUES('','$author','$text','$dt','0','$tid')");

            $uq = mysqli_query($con, "UPDATE `forums` SET `ldt`=".$dt." WHERE `id`=".$row["id"]);

        }

        ?>

        <h1>
            <a href='?p=forums&id=<?php echo $row["id"]; ?>'><?php echo $row["title"]; ?></a>
        </h1>
        <?php echo (($row["closed"] == 1) ? "<div class='forums-thread-closedtext'>closed</div>" : ""); ?>
        <div class='forums-posts'>
            <div class='forums-post'>
                <div class='forums-post-metadata'>
                    <span class='forums-post-metadata-item'>
                        <span class='forums-post-author'>
                            <?php echo getname($row["authorid"]); ?>
                        </span>
                    </span>
                    <span class='forums-post-metadata-item'>
                        <span class='forums-post-date'>
                            <?php echo displaydate($row["dt"]); ?>
                        </span>
                    </span>
                </div>
                <div class='forums-post-text'>
                    <?php echo Markdown($row["text"]); ?>
                </div>
            </div>

            <?php
            if ($row["closed"] == 0) {

                //fetching comments
                $cq = mysqli_query($con, "SELECT * FROM `forumposts` WHERE `tid`=".$_GET["id"]);

                while ($cr = mysqli_fetch_assoc($cq)) {

                    ?>

                    <div class='forums-post'>
                        <div class='forums-post-metadata'>
                            <span class='forums-post-metadata-item'>
                                <span class='forums-post-author'>
                                    <?php echo getname($cr["authorid"]); ?>
                                </span>
                            </span>
                            <span class='forums-post-metadata-item'>
                                <span class='forums-post-date'>
                                    <?php echo displaydate($cr["dt"]); ?>
                                </span>
                            </span>
                        </div>
                        <div class='forums-post-text'>
                            <?php echo Markdown($cr["text"]); ?>
                        </div>
                    </div>

                <?php

            } ?>

        </div>

            <?php if (checkuser()) {

                 ?>
                <hr><h1 class='comments-title'>Reply to this thread</h1>
                [md buttons]
                <div id='comment-form'><form action='?p=forums&amp;id=<?php echo $_GET["id"]; ?>' method='post'>
                <textarea name='text' id='comment-textarea' required></textarea>
                <input type='submit' name='cp' value='&gt;' id='comment-submitbutton'>
                </form></div>
                <?php

            } else {

                ?>
                <hr><h1 class='comments-title'>Log in to be able to post replies</h1>
                <?php

            }

        } else {

            ?>
            closed thread
            <?php

        }

    } else {

        // SHOW ALL THREADS

        if (checkuser()) {

            ?>
            <a class='forums-createthread' href='?p=forums&amp;action=add'>Create a new thread</a>
            <?php

        }

        if (isset($_GET["cat"])) {

            $cat = strip($_GET["cat"]);
            $query = mysqli_query($con, "SELECT * FROM `forums` WHERE `cat`=".$cat." ORDER BY `ldt` DESC");
            ?>
            <a class='forums-clearfilter' href='?p=forums'>clear category filter</a><table>
            <?php

        } else {

            $query = mysqli_query($con, "SELECT * FROM `forums` ORDER BY `ldt` DESC");
            ?>
            <table class='forums-table'>
                <thead>
                    <tr>
                        <th class='forums-header-category'></th>
                        <th class='forums-header-title'>Title</th>
                        <th class='forums-header-author'>Author</th>
                        <th class='forums-header-status'></th>
                        <th class='forums-header-createdate'>Created</th>
                        <th class='forums-header-modifydate'>Last Post</th>
                        <th class='forums-header-postcount'>Total Posts</th>
                    </tr>
                </thead>
                <tbody>
            <?php

        }

        while ($row = mysqli_fetch_assoc($query)) {

            ?>
            <tr class='forums-entry'>
                <td class='forums-entry-category'><a href='?p=forums&cat=<?php echo $row["cat"]; ?>'>
                    <?php echo getcatname($row["cat"]); ?>
                </a></td>
                <td class='forums-entry-title'><a href='?p=forums&id=<?php echo $row["id"]; ?>'>
                    <?php echo $row["title"]; ?>
                </a></td>
                <td class='forums-entry-author'><span>
                    <?php echo getname($row["authorid"]); ?>
                </span></td>
                <td class='forums-entry-status'><span>
                    <?php echo (($row["closed"] == 1) ? "closed" : ""); ?>
                </span></td>
                <td class='forums-entry-createdate'><span>
                    <?php echo displaydate($row["dt"]); ?>
                </span></td>
                <td class='forums-entry-modifydate'><span>
                    <?php echo displaydate($row["ldt"]); ?>
                </span></td>
                <td class='forums-entry-postcount'><span>
                    <?php echo mysqli_num_rows(mysqli_query($con, "SELECT `id` FROM `forumposts` WHERE `tid`=".$row["id"])); ?>
                </span></td>
            </tr>
            <?php

        }

        ?>
            </tbody>
        </table>
        <?php

    }

}

function getcatname($x) {

    global $con;

    $fq = mysqli_query($con, "SELECT `name` FROM `forumcat` WHERE `id`=".$x);
    $fr = mysqli_fetch_assoc($fq);

    return $fr["name"];

}

?>
