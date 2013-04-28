<?php

checkembed($r_c);
include "analyticstracking.php";
include "markdown/markdown.php";

$_SESSION["lp"] = $p;

$action = isset($_GET["action"]) ? strip($_GET["action"]) : "";

if ($action == "add" && checkuser()) {

    if (isset($_POST["addnew"])) {

        $authorid = $_SESSION["userid"];
        $cat = strip($_POST["cat"]);
        $title = strip($_POST["title"]);
        $text = strip($_POST["text"]);
        $dt = time();

        mysqli_query($con, "INSERT INTO `forums` VALUES('','".$authorid."','".$dt."','".$title."','".$text."','".$cat."','0','".$dt."')");
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
        $id = strip($_GET["id"]);
        $query = mysqli_query($con, "SELECT * FROM `forums` WHERE `id`=".$id);
        $row = mysqli_fetch_assoc($query);

        //comment processing
        if (isset($_POST["cp"]) && trim($_POST["text"]) != "") {

            $author = $_SESSION["userid"];
            $text = strip($_POST["text"]);
            $dt = time();

            $iq = mysqli_query($con, "INSERT INTO `forumposts` VALUES('','$author','$text','$dt','0','$id')");

            $uq = mysqli_query($con, "UPDATE `forums` SET `ldt`=".$dt." WHERE `id`=".$row["id"]);

        }

        ?>

        <h1>
            <a href='?p=forums&id=<?php echo $row["id"]; ?>'><?php echo $row["title"]; ?></a>
        </h1>
        <?php echo (($row["closed"] == 1) ? "<div class='forums-thread-closedtext'>closed</div>" : ""); ?>
        <div class='forums-posts'>
            <div class='forums-post'>
                <div class='forums-post-header'>
                    <div class='forums-post-number'>
                        <?php echo "#1"; ?>
                    </div>
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
                </div>
                <div class='forums-post-text'>
                    <?php echo Markdown($row["text"]); ?>
                </div>
            </div>

            <?php

            //fetching comments
            $cq = mysqli_query($con, "SELECT * FROM `forumposts` WHERE `tid`=".$id);

            $cn = 2;

            while ($cr = mysqli_fetch_assoc($cq)) {

                ?>

                <div class='forums-post'>
                    <div class='forums-post-header'>
                        <div class='forums-post-number'>
                            <?php echo "#".$cn; ?>
                        </div>
                        <div class='forums-post-metadata'>
                            <?php echo(editlinks($cr["id"])) ?>
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
                    </div>
                    <div class='forums-post-text'>
                        <?php

                        if (!isset($_GET["action"]) || $_GET["action"] != "edit" || !isset($_GET["pid"]) || $_GET["pid"] != $cr["id"] || !isset($_SESSION["userid"]) || (!checkadmin() && $_SESSION["userid"] != author($cr["id"]))) {
                            ?>
                            <?php echo Markdown($cr["text"]); ?>
                            <?php
                        } else {
                            if (isset($_POST["editsubmit"])) {
                                $text = strip($_POST["text"]);
                                if (isset($_POST["pdelete"]) && $_POST["pdelete"] == "on" && checkadmin()) {
                                    //delete
                                    $eq = mysqli_query($con, "DELETE FROM `forumposts` WHERE `id`=".$cr["id"]);
                                    ?>
                                    This post has been deleted.
                                    <?php
                                } else {
                                    //edit
                                    $eq = mysqli_query($con, "UPDATE `forumposts` SET `text`='".$text."' WHERE `id`=".$cr["id"]);
                                    ?>
                                    <?php echo Markdown($text); ?>
                                    <?php
                                }
                            } else {
                                ?>
                                <form action='?p=forums&id=<?php echo $id; ?>&action=edit&pid=<?php echo $cr["id"]; ?>' method='post'>
                                <textarea name='text'><?php echo $cr["text"]; ?></textarea>
                                <?php
                                if (checkadmin()) {
                                    ?>
                                    <br><input type='checkbox' name='pdelete' /> Delete
                                    <?php
                                }
                                ?>
                                <br><input type='submit' name='editsubmit' />
                                </form>
                                <?php
                            }
                        }

                        ?>
                    </div>
                </div>

                <?php
                $cn++;
            } ?>

        </div>

        <?php

        if ($row["closed"] == 0) {
            //writing a comment
            if (checkuser()) {

                 ?>
                <hr><h1 class='comments-title'>Reply to this thread</h1>
                <div class='comment-form'>
                    <form action='?p=forums&amp;id=<?php echo $id; ?>' method='post'>
                        <textarea name='text' class='comment-textarea' required></textarea>
                        <input type='submit' name='cp' value='&gt;' class='comment-submitbutton'>
                    </form>
                </div>
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
            $query = mysqli_query($con, "SELECT `id`,`authorid`,`dt`,`title`,`cat`,`closed`,`ldt` FROM `forums` WHERE `cat`=".$cat." ORDER BY `ldt` DESC");
            ?>
            <a class='forums-clearfilter' href='?p=forums'>clear category filter</a><table>
            <?php

        } else {

            $query = mysqli_query($con, "SELECT `id`,`authorid`,`dt`,`title`,`cat`,`closed`,`ldt` FROM `forums` ORDER BY `ldt` DESC");
            ?>
            <table class='forums-table'>
                <colgroup>
                    <col class='forums-column-category'>
                    <col class='forums-column-title'>
                    <col class='forums-column-modifydate'>
                    <col class='forums-column-postcount'>
                </colgroup>
                <tbody>
            <?php

        }

        while ($row = mysqli_fetch_assoc($query)) {

            ?>
            <tr class='forums-entry'>
                <td class='forums-entry-category forums-category-<?php echo getcatname($row["cat"]); ?>'>
                    <a href='?p=forums&cat=<?php echo $row["cat"]; ?>'>
                        <div class='forums-entry-category-text'>
                            <?php echo getcatname($row["cat"]); ?>
                        </div>
                    </a>
                </td>
                <td class='forums-entry-main <?php echo (($row["closed"] == 1) ? "forums-entry-closed" : ""); ?>'>
                    <a class='forums-entry-title' href='?p=forums&id=<?php echo $row["id"]; ?>'>
                        <?php echo $row["title"]; ?>
                    </a>
                    <br>
                    <span class='forums-entry-metadata'>
                        created by <?php echo getname($row["authorid"])." ".displaydate($row["dt"]); ?>
                    </span>
                </td>
                <td class='forums-entry-modifydate'>
                    <span class='forums-entry-miniheader'>
                        <?php echo "Last reply posted"?>
                    </span>
                    <br>
                    <?php echo displaydate($row["ldt"]); ?>
                </td>
                <td class='forums-entry-postcount'>
                    <span class='forums-entry-miniheader'>
                        Thread has
                    </span>
                    <br>
                    <?php
                        echo mysqli_num_rows(mysqli_query($con, "SELECT `id` FROM `forumposts` WHERE `tid`=".$row["id"])).(mysqli_num_rows(mysqli_query($con, "SELECT `id` FROM `forumposts` WHERE `tid`=".$row["id"])) == 1 ? " reply" : " replies");
                    ?>
                </td>
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

function editlinks($cn) {

    global $id;

    if (checkadmin() || (isset($_SESSION["userid"]) && $_SESSION["userid"] == author($cn))) {

        return("<a href='?p=forums&id=".$id."&action=edit&pid=".$cn."'>edit/delete</a>");

    }
}

function author($cn) {
    global $con;

    $x = mysqli_fetch_assoc(mysqli_query($con, "SELECT authorid FROM `forumposts` WHERE `id`=".$cn));
    return $x["authorid"];
}
