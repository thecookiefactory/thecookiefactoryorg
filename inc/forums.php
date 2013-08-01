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
        $cq = $con->prepare("SELECT `forumcategories`.`name` FROM `forumcategories` WHERE `forumcategories`.`id` = :cat");
        $cq->bindValue("cat", $cat, PDO::PARAM_INT);
        $cq->execute();

        if ($cq->rowCount() != 1) {

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

                    $date = time();

                    $iq = $con->prepare("INSERT INTO `forumthreads` VALUES('', :title, :text, :authorid, :date, 0, :datee, :cat, 0, 0, 0)");
                    $iq->bindValue("authorid", $authorid, PDO::PARAM_INT);
                    $iq->bindValue("date", $date, PDO::PARAM_INT);
                    $iq->bindValue("datee", $date, PDO::PARAM_INT);
                    $iq->bindValue("title", $title, PDO::PARAM_STR);
                    $iq->bindValue("text", $text, PDO::PARAM_STR);
                    $iq->bindValue("cat", $cat, PDO::PARAM_INT);
                    $iq->execute();

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
            $cq = $con->query("SELECT * FROM `forumcategories` ORDER BY `forumcategories`.`name` ASC");

            while ($cr = $cq->fetch(PDO::FETCH_ASSOC)) {
                ?>

                <option value='<?php echo $cr["id"]; ?>'><?php echo $cr["longname"]; ?></option>

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

        $eq = $con->prepare("SELECT * FROM `forumposts` WHERE `forumposts`.`id` = :pid AND `forumposts`.`threadid` = :tid");
        $eq->bindValue("pid", $pid, PDO::PARAM_INT);
        $eq->bindValue("tid", $tid, PDO::PARAM_INT);
        $eq->execute();

        if ($eq->rowCount() != 1) {

            echo "Something went wrong.";

        } else {

            $er = $eq->fetch(PDO::FETCH_ASSOC);

            if (($er["authorid"] != $_SESSION["userid"]) && !checkadmin()) {

                echo "You dont have the right!!";

            } else {

                // editing

                if (isset($_POST["edit"]) && (isset($_POST["text"]) && vf($_POST["text"]))) {

                    if (checkadmin() && isset($_POST["delete"]) && $_POST["delete"] == "on") {

                        $dq = $con->prepare("DELETE FROM `forumposts` WHERE `forumposts`.`threadid = :tid AND `forumposts`.`id` = :pid");
                        $dq->bindValue("pid", $pid, PDO::PARAM_INT);
                        $dq->bindValue("tid", $tid, PDO::PARAM_INT);
                        $dq->execute();
                        echo "deleted";

                    } else {

                        $text = strip($_POST["text"]);

                        if (strlen($text) > 20000) {

                            echo "Your comment must be less than 20 000 characters long.";

                        } else {

                            $editdate = time();

                            $uq = $con->prepare("UPDATE `forumposts` SET `forumposts`.`text` = :text, `forumposts`.`editdate` = :editdate WHERE `forumposts`.`threadid` = :tid AND `forumposts`.`id` = :pid");
                            $uq->bindvalue("text", $text, PDO::PARAM_STR);
                            $uq->bindvalue("editdate", $editdate, PDO::PARAM_INT);
                            $uq->bindvalue("tid", $tid, PDO::PARAM_INT);
                            $uq->bindvalue("pid", $pid, PDO::PARAM_INT);
                            $uq->execute();

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
        $eq = $con->prepare("SELECT * FROM `forumthreads` WHERE `forumthreads`.`id` = :tid");
        $eq->bindValue("tid", $tid, PDO::PARAM_INT);
        $eq->execute();

        if ($eq->rowCount() != 1) {

            echo "Something went wrong.";

        } else {

            $er = $eq->fetch(PDO::FETCH_ASSOC);

            if (($er["authorid"] != $_SESSION["userid"]) && !checkadmin()) {

                echo "You dont have the right!!";

            } else {

                // editing

                if (isset($_POST["edit"]) && (isset($_POST["cat"]) && vf($_POST["cat"])) && (isset($_POST["title"]) && vf($_POST["title"])) && (isset($_POST["text"]) && vf($_POST["text"]))) {

                    if (checkadmin() && isset($_POST["delete"]) && $_POST["delete"] == "on") {

                        $dq = $con->prepare("DELETE FROM `forumposts` WHERE `forumposts`.`threadid = :tid");
                        $dq->bindValue("tid", $tid, PDO::PARAM_INT);
                        $dq->execute();

                        $dq = $con->prepare("DELETE FROM `forumthreads` WHERE `forumthreads`.`id` = :tid");
                        $dq->bindValue("tid", $tid, PDO::PARAM_INT);
                        $dq->execute();
                        echo "deleted";

                    } else {

                        $cat = strip($_POST["cat"]);

                        $q = $con->prepare("SELECT `forumcategories`.`name` FROM `forumcategories` WHERE `forumcategories`.`id` = :cat");
                        $q->bindValue("cat", $cat, PDO::PARAM_INT);
                        $q->execute();

                        if ($q->rowCount() != 1) {

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

                                    $editdate = time();

                                    $uq = $con->prepare("UPDATE `forumthreads` SET `forumthreads`.`forumcategory` = :cat, `forumthreads`.`title` = :title, `forumthreads`.`text` = :text, `forumthreads`.`editdate` = :editdate WHERE `forumthreads`.`id` = :tid");
                                    $uq->bindvalue("cat", $cat, PDO::PARAM_INT);
                                    $uq->bindvalue("title", $title, PDO::PARAM_STR);
                                    $uq->bindvalue("text", $text, PDO::PARAM_STR);
                                    $uq->bindvalue("editdate", $editdate, PDO::PARAM_INT);
                                    $uq->bindvalue("tid", $tid, PDO::PARAM_INT);
                                    $uq->execute();

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
                            $cq = $con->query("SELECT * FROM `forumcategories` ORDER BY `forumcategories`.`name` ASC");

                            while ($cr = $cq->fetch(PDO::FETCH_ASSOC)) {
                                ?>

                                <option value='<?php echo $cr["id"]; ?>'><?php echo $cr["longname"]; ?></option>

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
        $query = $con->prepare("SELECT * FROM `forumthreads` WHERE `forumthreads`.`id` = :id");
        $query->bindValue("id", $id, PDO::PARAM_INT);
        $query->execute();

        if ($query->rowCount() == 1) {

            $row = $query->fetch(PDO::FETCH_ASSOC);

            //comment processing
            if (isset($_POST["cp"]) && isset($_POST["text"]) && vf($_POST["text"])) {

                $author = $_SESSION["userid"];
                $text = strip($_POST["text"]);

                if (strlen($text) > 20000) {

                    echo "Your comment must be less than 20 000 characters long.";

                } else {

                    $date = time();

                    $iq = $con->prepare("INSERT INTO `forumposts` VALUES('', :text, :author, :date, 0, :id)");
                    $iq->bindValue("author", $author, PDO::PARAM_INT);
                    $iq->bindValue("text", $text, PDO::PARAM_STR);
                    $iq->bindValue("date", $date, PDO::PARAM_INT);
                    $iq->bindValue("id", $id, PDO::PARAM_INT);
                    $iq->execute();

                    $uq = $con->prepare("UPDATE `forumthreads` SET `forumthreads`.`lastdate` = :date WHERE `forumthreads`.`id` = :id");
                    $uq->bindValue("date", $date, PDO::PARAM_INT);
                    $uq->bindValue("id", $id, PDO::PARAM_INT);
                    $uq->execute();

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
                            <?php if ($row["editdate"] != 0) echo "last edited ".displaydate($row["editdate"]); ?>

                            <span class='forums-post-metadata-item'>
                                <span class='forums-post-author'>

                                    <?php echo getname($row["authorid"], true); ?>

                                </span>
                            </span>
                            <span class='forums-post-metadata-item'>
                                <span class='forums-post-date'>

                                    <?php echo displaydate($row["date"]); ?>

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
                $cq = $con->prepare("SELECT * FROM `forumposts` WHERE `forumposts`.`threadid` = :id");
                $cq->bindValue("id", $id, PDO::PARAM_INT);
                $cq->execute();

                $cn = isset($tid) ? 1 : 2;

                while ($cr = $cq->fetch(PDO::FETCH_ASSOC)) {

                    ?>

                    <div class='forums-post'>
                        <div class='forums-post-header'>
                            <div class='forums-post-number'>

                                <?php echo "#".$cn; ?>

                            </div>
                            <div class='forums-post-metadata'>

                                <?php if ((checkuser() && $cr["authorid"] == $_SESSION["userid"]) || checkadmin()) echo "<a href='?p=forums&amp;action=edit&tid=".$row["id"]."&amp;pid=".$cr["id"]."'>edit</a>"; ?>
                                <?php if ($cr["editdate"] != 0) echo "last edited ".displaydate($cr["editdate"]); ?>

                                <span class='forums-post-metadata-item'>
                                    <span class='forums-post-author'>

                                        <?php echo getname($cr["authorid"]); ?>

                                    </span>
                                </span>
                                <span class='forums-post-metadata-item'>
                                    <span class='forums-post-date'>

                                        <?php echo displaydate($cr["date"]); ?>

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

            $query = $con->prepare("SELECT * FROM `forumthreads` WHERE `forumthreads`.`forumcategory` = :cat AND `forumthreads`.`forumcategory` <> 0 ORDER BY `forumthreads`.`lastdate` DESC");
            $query->bindValue("cat", $cat, PDO::PARAM_INT);
            $query->execute();

            ?>

            <a class='forums-clearfilter' href='?p=forums'>&#x21A9; clear category filter</a>

            <?php

        } else {

            $query = $con->query("SELECT * FROM `forumthreads` WHERE `forumthreads`.`forumcategory` <> 0 ORDER BY `forumthreads`.`lastdate` DESC");

        }

        ?>

        <style type='text/css' scoped>

            <?php
            $cq = $con->query("SELECT * FROM `forumcategories`");

            while ($cr = $cq->fetch(PDO::FETCH_ASSOC)) {

                echo ".forums-category-".$cr["name"]."         {background-color: #".$cr["hexcode"]."; }\n";
                echo ".forums-category-".$cr["name"].":hover   {background-color: #".$cr["hoverhexcode"]."; }\n";

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

        if ($query->rowCount() != 0) {

            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {

                ?>

                <tr class='forums-entry'>
                    <td class='forums-entry-category forums-category-<?php echo getcatname($row["forumcategory"]); ?>'>
                        <a class='forums-entry-category-text' href='?p=forums&cat=<?php echo $row["forumcategory"]; ?>'>

                                <?php echo getcatname($row["forumcategory"]); ?>

                        </a>
                    </td>
                    <td class='forums-entry-main <?php echo (($row["closed"] == 1) ? "forums-entry-closed" : ""); ?>'>
                        <a class='forums-entry-title' href='?p=forums&id=<?php echo $row["id"]; ?>'>

                            <?php echo $row["title"]; ?>

                        </a>
                        <br>
                        <span class='forums-entry-metadata'>

                            created by <?php echo getname($row["authorid"])." ".displaydate($row["date"]); ?>

                        </span>
                    </td>
                    <td class='forums-entry-modifydate'>
                        <span class='forums-entry-miniheader'>Last reply posted</span><br>

                        <?php echo displaydate($row["lastdate"]); ?>

                    </td>
                    <td class='forums-entry-postcount'>
                        <span class='forums-entry-miniheader'>Thread has</span><br>

                        <?php
                            $q = $con->prepare("SELECT `forumposts`.`id` FROM `forumposts` WHERE `forumposts`.`threadid` = :id");
                            $q->bindValue("id", $row["id"], PDO::PARAM_INT);
                            $q->execute();
                            echo $q->rowCount().(($q->rowCount()) == 1 ? " reply" : " replies");
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

    $fq = $con->prepare("SELECT `forumcategories`.`name` FROM `forumcategories` WHERE `forumcategories`.`id` = :x");
    $fq->bindValue("x", $x, PDO::PARAM_INT);
    $fq->execute();
    $fr = $fq->fetch(PDO::FETCH_ASSOC);

    return $fr["name"];

}
