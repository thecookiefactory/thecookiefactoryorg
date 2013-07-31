<?php

if (!isset($r_c)) header("Location: notfound.php");

include "analyticstracking.php";

$_SESSION["lp"] = $p;

$action = isset($_GET["action"]) ? strip($_GET["action"]) : "";

if ($action == "add" && checkuser()) {

    if (isset($_POST["addnew"]) && (isset($_POST["cat"]) && vf($_POST["cat"])) && (isset($_POST["title"]) && vf($_POST["title"])) && (isset($_POST["text"]) && vf($_POST["text"]))) {

        $authorid = $_SESSION["userid"];
        $cat = strip($_POST["cat"]);

        // checking if the selected category is valid
        if (mysqli_num_rows(mysqli_query($con, "SELECT `name` FROM `forumcat` WHERE `id`=".$cat)) != 1) {

            echo "That does not seem like a real forum category. Sorry, kiddo.";

        } else {

            $title = strip($_POST["title"]);

            if (strlen($title) > 37) {

                echo "Please enter a title shorter than 38 characters.";

            } else {

                $text = strip($_POST["text"]);

                if (strlen($text) > 20000) {

                    echo "Your comment must be less than 20,000 characters long.";

                } else {

                    $dt = time();

                    mysqli_query($con, "INSERT INTO `forums` VALUES('','".$authorid."','".$dt."','0','".$title."','".$text."','".$cat."','0','".$dt."','0','0')");

                    ?>
                    added.
                    <?php
                }

            }

        }

    } else {

        ?>
        <form action='?p=forums&amp;action=add' method='post'>
            <label class='forums-newpost-select-label' for="cat">Category:
            <select class='forums-newpost-select' name='cat'>

            <?php
        $cq = mysqli_query($con, "SELECT * FROM `forumcat` ORDER BY `name` ASC");
        while ($cr = mysqli_fetch_assoc($cq)) {
            ?>

            <option value='<?php echo $cr["id"]; ?>'><?php echo $cr["pname"]; ?></option>

            <?php
        }
            ?>

        </select></label>
        <input class='forums-newpost-submit' type='submit' name='addnew' value='Submit &#x27A8;'>
            <h1>
                <input class='forums-newpost-title' type='text' name='title' autofocus required placeholder='Enter a title here...' maxlength='37'>
            </h1>
        <div class='forums-post'>
            <div class='forums-post-header'>
                <div class='forums-post-number'>
                    #1
                </div>
            </div>
            <div>
                <textarea class='forums-newpost-text' name='text' required placeholder='Type your post here...' maxlength='20000'></textarea>
            </div>
        </div>

        </form>

        <?php

    }

} else if ($action == "edit" && checkuser() && isset($_GET["tid"]) && is_numeric($_GET["tid"])) {

    $tid = strip($_GET["tid"]);

    if (isset($_GET["pid"]) && is_numeric($_GET["pid"])) {

        // editing a reply
        $pid = strip($_GET["pid"]);

        $eq = mysqli_query($con, "SELECT * FROM `forumposts` WHERE `id`=".$pid." AND `tid`=".$tid);

        if (mysqli_num_rows($eq) != 1) {

            echo "Something went wrong.";

        } else {

            $er = mysqli_fetch_assoc($eq);

            if (($er["authorid"] != $_SESSION["userid"]) && !checkadmin()) {

                echo "You dont have the right!!";

            } else {

                // editing

                if (isset($_POST["edit"]) && (isset($_POST["text"]) && vf($_POST["text"]))) {

                    if (checkadmin() && isset($_POST["delete"]) && $_POST["delete"] == "on") {

                        mysqli_query($con, "DELETE FROM `forumposts` WHERE `tid`=".$tid." AND `id`=".$pid);
                        echo "deleted";

                    } else {

                        $text = strip($_POST["text"]);

                        if (strlen($text) > 20000) {

                            echo "Your comment must be less than 20 000 characters long.";

                        } else {

                            $edt = time();

                            mysqli_query($con, "UPDATE `forumposts` SET `text`='".$text."', `edt`='".$edt."' WHERE `tid`=".$tid." AND `id`=".$pid);

                            ?>
                            updated
                            <?php
                        }

                    }

                } else {

                    ?>
                    <form action='?p=forums&amp;action=edit&amp;tid=<?php echo $tid; ?>&amp;pid=<?php echo $pid; ?>' method='post'>

                    <input class='forums-newpost-submit forums-edit-submit' type='submit' name='edit' value='Submit &#x27A8;'>
                    <div class='forums-post'>
                        <div class='forums-post-header'>
                            <div class='forums-post-number'>
                                #N
                            </div>
                        </div>
                        <div>
                            <textarea class='forums-newpost-text' name='text' required placeholder='Type your post here...' maxlength='20000'><?php echo $er["text"]; ?></textarea>
                        </div>
                    </div>

                    </form>

                    <?php
                        if (checkadmin()) {

                            echo "delete this reply <input type='checkbox' name='delete'>";

                        }

                }

            }

        }

    } else {

        // editing the main post
        $eq = mysqli_query($con, "SELECT * FROM `forums` WHERE `id`=".$tid);

        if (mysqli_num_rows($eq) != 1) {

            echo "Something went wrong.";

        } else {

            $er = mysqli_fetch_assoc($eq);

            if (($er["authorid"] != $_SESSION["userid"]) && !checkadmin()) {

                echo "You dont have the right!!";

            } else {

                // editing

                if (isset($_POST["edit"]) && (isset($_POST["cat"]) && vf($_POST["cat"])) && (isset($_POST["title"]) && vf($_POST["title"])) && (isset($_POST["text"]) && vf($_POST["text"]))) {

                    if (checkadmin() && isset($_POST["delete"]) && $_POST["delete"] == "on") {

                        mysqli_query($con, "DELETE FROM `forumposts` WHERE `tid` = ".$tid);
                        mysqli_query($con, "DELETE FROM `forums` WHERE `id` = ".$tid);
                        echo "deleted";

                    } else {

                        $cat = strip($_POST["cat"]);

                        if (mysqli_num_rows(mysqli_query($con, "SELECT `name` FROM `forumcat` WHERE `id`=".$cat)) != 1) {

                            echo "that does not seem like a real forum categorny+";

                        } else {

                            $title = strip($_POST["title"]);

                            if (strlen($title) > 37) {

                                echo "please enter a title shorter than 38 characters";

                            } else {

                                $text = strip($_POST["text"]);

                                if (strlen($text) > 20000) {

                                    echo "Your comment must be less than 20 000 characters long.";

                                } else {

                                    $edt = time();

                                    mysqli_query($con, "UPDATE `forums` SET `cat`='".$cat."', `title`='".$title."', `text`='".$text."', `edt`='".$edt."' WHERE `id`=".$tid);

                                    ?>
                                    updated
                                    <?php
                                }

                            }

                        }

                    }

                } else {

                    ?>
                    <form action='?p=forums&amp;action=edit&amp;tid=<?php echo $tid; ?>' method='post'>
                        <label class='forums-newpost-select-label' for="cat">Category:
                        <select class='forums-newpost-select' name='cat'>

                        <?php
                    $cq = mysqli_query($con, "SELECT * FROM `forumcat` ORDER BY `name` ASC");
                    while ($cr = mysqli_fetch_assoc($cq)) {
                        ?>

                        <option value='<?php echo $cr["id"]; ?>'><?php echo $cr["pname"]; ?></option>

                        <?php
                    }
                        ?>

                    </select></label>
                    <input class='forums-newpost-submit' type='submit' name='edit' value='Submit &#x27A8;'>
                        <h1>
                            <input class='forums-newpost-title' type='text' name='title' autofocus required placeholder='Enter a title here...' maxlength='37' value='<?php echo $er["title"]; ?>'>
                        </h1>
                    <div class='forums-post'>
                        <div class='forums-post-header'>
                            <div class='forums-post-number'>
                                #1
                            </div>
                        </div>
                        <div>
                            <textarea class='forums-newpost-text' name='text' required placeholder='Type your post here...' maxlength='20000'><?php echo $er["text"]; ?></textarea>
                        </div>
                    </div>

                    </form>

                    <?php
                        if (checkadmin()) {

                            echo "delete this whole thread <input type='checkbox' name='delete'>";

                        }
                    ?>

                    </form>

                    <?php

                }

            }

        }

    }

} else {

    if ((isset($_GET["id"]) && is_numeric($_GET["id"])) || (isset($tid) && is_numeric($tid))) {

        // SHOW ONE THREAD
        $id = isset($tid) ? strip($tid) : strip($_GET["id"]);
        $query = mysqli_query($con, "SELECT * FROM `forums` WHERE `id`=".$id);

        if (mysqli_num_rows($query) == 1) {

            $row = mysqli_fetch_assoc($query);

            //comment processing
            if (isset($_POST["cp"]) && isset($_POST["text"]) && vf($_POST["text"])) {

                $author = $_SESSION["userid"];
                $text = strip($_POST["text"]);

                if (strlen($text) > 20000) {

                    echo "Your comment must be less than 20 000 characters long.";

                } else {

                    $dt = time();

                    $iq = mysqli_query($con, "INSERT INTO `forumposts` VALUES('','$author','$text','$dt','0','$id')");

                    $uq = mysqli_query($con, "UPDATE `forums` SET `ldt`=".$dt." WHERE `id`=".$row["id"]);

                }

            }

            ?>

            <?php

            if (!isset($tid)) {

            ?>
            <h1>
                <a href='?p=forums&id=<?php echo $row["id"]; ?>'><?php echo $row["title"]; ?></a>
            </h1>

            <?php echo (($row["closed"] == 1) ? "<div class='forums-thread-closedtext'>closed</div>" : ""); ?>
            <?php echo (($row["mapid"] != 0) ? "<a href='?p=maps#".$row["mapid"]."'>&#x21AA; related map</a>" : ""); ?>
            <?php
            }
            ?>

            <div class='forums-posts'>

                <?php
                if (!isset($tid)) {
                ?>

                <div class='forums-post'>
                    <div class='forums-post-header'>
                        <div class='forums-post-number'>
                            #1
                        </div>
                        <div class='forums-post-metadata'>

                            <?php if ((checkuser() && $row["authorid"] == $_SESSION["userid"]) || checkadmin()) echo "<a href='?p=forums&amp;action=edit&tid=".$row["id"]."'>edit</a>"; ?>
                            <?php if ($row["edt"] != 0) echo "last edited ".displaydate($row["edt"]); ?>

                            <span class='forums-post-metadata-item'>
                                <span class='forums-post-author'>

                                    <?php echo getname($row["authorid"], true); ?>

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

                        <p><?php echo tformat($row["text"]); ?></p>

                    </div>
                </div>

                <?php
                }
                ?>
                <?php

                //fetching comments
                $cq = mysqli_query($con, "SELECT * FROM `forumposts` WHERE `tid`=".$id);

                $cn = isset($tid) ? 1 : 2;

                while ($cr = mysqli_fetch_assoc($cq)) {

                    ?>

                    <div class='forums-post'>
                        <div class='forums-post-header'>
                            <div class='forums-post-number'>

                                <?php echo "#".$cn; ?>

                            </div>
                            <div class='forums-post-metadata'>

                                <?php if ((checkuser() && $cr["authorid"] == $_SESSION["userid"]) || checkadmin()) echo "<a href='?p=forums&amp;action=edit&tid=".$row["id"]."&amp;pid=".$cr["id"]."'>edit</a>"; ?>
                                <?php if ($row["edt"] != 0) echo "last edited ".displaydate($row["edt"]); ?>

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

                                <p><?php echo tformat($cr["text"]); ?></p>

                        </div>
                    </div>

                    <?php
                    $cn++;
                }
            ?>

            </div>

            <?php

            if ($row["closed"] == 0) {
                //writing a comment
                if (checkuser()) {

                     ?>
                    <hr><h1 class='comments-title'>Reply to this thread</h1>
                    <div class='comment-form'>
                        <?php
                        if (isset($tid)) {

                            echo "<form action='?p=news&amp;id=".strip($_GET["id"])."' method='post'>";

                        } else {

                            echo "<form action='?p=forums&amp;id=".$id."' method='post'>";

                        }
                        ?>
                            <textarea name='text' class='comment-textarea' required maxlength='20000'></textarea>
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

            // redirecting to the main page instead of giving an error message
            header("Location: ?p=forums");

        }

    } else {

        // SHOW ALL THREADS

        if (checkuser()) {

            ?>

            <a class='forums-createthread' href='?p=forums&amp;action=add'>
                <span class='forums-createthread-sign'>+</span>
                <span class='forums-createthread-text'>create a new thread</span>
            </a>

            <?php

        }

        if (isset($_GET["cat"]) && is_numeric($_GET["cat"])) {

            $cat = strip($_GET["cat"]);

            $query = mysqli_query($con, "SELECT `id`,`authorid`,`dt`,`title`,`cat`,`closed`,`ldt` FROM `forums` WHERE `cat`=".$cat." AND `cat`<>0 ORDER BY `ldt` DESC");

            ?>

            <a class='forums-clearfilter' href='?p=forums'>&#x21A9; clear category filter</a>

            <?php

        } else {

            $query = mysqli_query($con, "SELECT `id`,`authorid`,`dt`,`title`,`cat`,`closed`,`ldt` FROM `forums` WHERE `cat`<>0 ORDER BY `ldt` DESC");

        }

        ?>

        <style type='text/css' scoped>

            <?php
            $cq = mysqli_query($con, "SELECT * FROM `forumcat`");

            while ($cr = mysqli_fetch_assoc($cq)) {

                echo ".forums-category-".$cr["name"]."         {background-color: #".$cr["hex"]."; }\n";
                echo ".forums-category-".$cr["name"].":hover   {background-color: #".$cr["hexh"]."; }\n";

            }
            ?>

        </style>
        <table class='forums-table'>
                <colgroup>
                    <col class='forums-column-category'>
                    <col class='forums-column-title'>
                    <col class='forums-column-modifydate'>
                    <col class='forums-column-postcount'>
                </colgroup>
            <tbody>

        <?php

        if (mysqli_num_rows($query) != 0) {

            while ($row = mysqli_fetch_assoc($query)) {

                ?>

                <tr class='forums-entry'>
                    <td class='forums-entry-category forums-category-<?php echo getcatname($row["cat"]); ?>'>
                        <a class='forums-entry-category-text' href='?p=forums&cat=<?php echo $row["cat"]; ?>'>

                                <?php echo getcatname($row["cat"]); ?>

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
                        <span class='forums-entry-miniheader'>Last reply posted</span><br>

                        <?php echo displaydate($row["ldt"]); ?>

                    </td>
                    <td class='forums-entry-postcount'>
                        <span class='forums-entry-miniheader'>Thread has</span><br>

                        <?php
                            echo mysqli_num_rows(mysqli_query($con, "SELECT `id` FROM `forumposts` WHERE `tid`=".$row["id"])).(mysqli_num_rows(mysqli_query($con, "SELECT `id` FROM `forumposts` WHERE `tid`=".$row["id"])) == 1 ? " reply" : " replies");
                        ?>

                    </td>
                </tr>

                <?php

            }

        } else {

            echo "There are no forum threads. Why dont you create one?";

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
