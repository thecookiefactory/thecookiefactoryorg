<?php

if (!isset($r_c)) header("Location: /notfound.php");

include_once "analyticstracking.php";

$_SESSION["lp"] = $p;

$action = isset($_GET["action"]) ? strip($_GET["action"]) : "";

if ($action == "add" && $user->isLoggedIn()) {

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

                    $iq = $con->prepare("INSERT INTO `forumthreads` VALUES(NULL, :title, :text, :authorid, now(), 0, now(), :cat, 0, 0, 0)");
                    $iq->bindValue("authorid", $authorid, PDO::PARAM_INT);
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
        <form action='/forums/add/' method='post'>
            <label class='forums-newpost-select-label' for="cat">Category:
            <select class='forums-newpost-select' name='cat'>

            <?php
            $cq = $con->query("SELECT * FROM `forumcategories` ORDER BY `forumcategories`.`name` ASC");

            while ($cr = $cq->fetch()) {
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

} else if ($action == "edit" && $user->isLoggedIn() && isset($_GET["tid"]) && is_numeric($_GET["tid"])) {

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

            $er = $eq->fetch();

            if (($er["authorid"] != $_SESSION["userid"]) && !checkadmin()) {

                echo "You dont have the right!!";

            } else {

                // editing

                if (isset($_POST["edit"]) && (isset($_POST["text"]) && vf($_POST["text"]))) {

                    if (checkadmin() && isset($_POST["delete"]) && $_POST["delete"] == "on") {

                        $dq = $con->prepare("DELETE FROM `forumposts` WHERE `forumposts`.`threadid` = :tid AND `forumposts`.`id` = :pid");
                        $dq->bindValue("pid", $pid, PDO::PARAM_INT);
                        $dq->bindValue("tid", $tid, PDO::PARAM_INT);
                        $dq->execute();
                        echo "deleted";

                    } else {

                        $text = strip($_POST["text"]);

                        if (strlen($text) > 20000) {

                            echo "Your comment must be less than 20 000 characters long.";

                        } else {

                            $uq = $con->prepare("UPDATE `forumposts` SET `forumposts`.`text` = :text WHERE `forumposts`.`threadid` = :tid AND `forumposts`.`id` = :pid");
                            $uq->bindValue("text", $text, PDO::PARAM_STR);
                            $uq->bindValue("tid", $tid, PDO::PARAM_INT);
                            $uq->bindValue("pid", $pid, PDO::PARAM_INT);
                            $uq->execute();

                            // redirect
                            if ($uq->rowCount() == 1) {

                                $eq = $con->prepare("SELECT `forumthreads`.`newsid` FROM `forumthreads` WHERE `forumthreads`.`id` = :tid");
                                $eq->bindValue("tid", $tid, PDO::PARAM_INT);
                                $eq->execute();

                                $er = $eq->fetch();

                                if ($er["newsid"] == 0 || is_null($er["newsid"])) {

                                    header("Location: /forums/" . $tid);

                                } else {

                                    $gq = $con->prepare("SELECT `news`.`stringid` FROM `news` WHERE `news`.`id` = :id");
                                    $gq->bindValue("id", $er["newsid"], PDO::PARAM_INT);
                                    $gq->execute();

                                    $gr = $gq->fetch();
                                    header("Location: /news/" . $gr["stringid"]);

                                }

                            }
                        }

                    }

                } else {

                    ?>
                    <form action='/forums/edit/<?php echo $tid; ?>/<?php echo $pid; ?>/' method='post'>

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

                    <?php
                    if (checkadmin()) {

                        echo "delete this reply <input type='checkbox' name='delete'>";

                    }
                    ?>

                    </form>

                    <?php

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

            $er = $eq->fetch();

            if (($er["authorid"] != $_SESSION["userid"]) && !checkadmin()) {

                echo "You dont have the right!!";

            } else {

                // editing

                if (isset($_POST["edit"]) && (isset($_POST["cat"]) && vf($_POST["cat"])) && (isset($_POST["title"]) && vf($_POST["title"])) && (isset($_POST["text"]) && vf($_POST["text"]))) {

                    if (checkadmin() && isset($_POST["delete"]) && $_POST["delete"] == "on") {

                        if (vf($er["mapid"])) {

                            $uq = $con->prepare("UPDATE `maps` SET `maps`.`comments` = 0 WHERE `maps`.`id` = :id");
                            $uq->bindValue("id", $er["mapid"], PDO::PARAM_INT);
                            $uq->execute();

                        }

                        $dq = $con->prepare("DELETE FROM `forumposts` WHERE `forumposts`.`threadid` = :tid");
                        $dq->bindValue("tid", $tid, PDO::PARAM_INT);
                        $dq->execute();

                        $dq = $con->prepare("DELETE FROM `forumthreads` WHERE `forumthreads`.`id` = :tid");
                        $dq->bindValue("tid", $tid, PDO::PARAM_INT);
                        $dq->execute();

                        // redirect
                        if ($uq->rowCount() == 1) {

                            header("Location: /forums");

                        }

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

                                    $uq = $con->prepare("UPDATE `forumthreads` SET `forumthreads`.`forumcategory` = :cat, `forumthreads`.`title` = :title, `forumthreads`.`text` = :text, `forumthreads`.`editdate` = now() WHERE `forumthreads`.`id` = :tid");
                                    $uq->bindValue("cat", $cat, PDO::PARAM_INT);
                                    $uq->bindValue("title", $title, PDO::PARAM_STR);
                                    $uq->bindValue("text", $text, PDO::PARAM_STR);
                                    $uq->bindValue("tid", $tid, PDO::PARAM_INT);
                                    $uq->execute();

                                    // redirect
                                    if ($uq->rowCount() == 1) {

                                        header("Location: /forums/" . $tid);

                                    }

                                }

                            }

                        }

                    }

                } else {

                    ?>
                    <form action='/forums/edit/<?php echo $tid; ?>/' method='post'>
                        <label class='forums-newpost-select-label' for="cat">Category:
                        <select class='forums-newpost-select' name='cat'>

                        <?php
                            $cq = $con->query("SELECT * FROM `forumcategories` ORDER BY `forumcategories`.`name` ASC");

                            while ($cr = $cq->fetch()) {
                                ?>

                                <option value='<?php echo $cr["id"]; ?>'<?php if ($cr["id"] == $er["forumcategory"]) echo " selected" ?>><?php echo $cr["longname"]; ?></option>

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
        $query = $con->prepare("SELECT `forumthreads`.`id`, `forumthreads`.`title`, `forumthreads`.`text`, `forumthreads`.`authorid`, `forumthreads`.`date`, `forumthreads`.`editdate`, `forumthreads`.`lastdate`, `forumthreads`.`mapid`, `forumthreads`.`newsid`, BIN(`forumthreads`.`closed`)
                                FROM `forumthreads`
                                WHERE `forumthreads`.`id` = :id");
        $query->bindValue("id", $id, PDO::PARAM_INT);
        $query->execute();

        if ($query->rowCount() == 1) {

            $row = $query->fetch();

            //comment processing
            if (isset($_POST["cp"]) && isset($_POST["text"]) && vf($_POST["text"]) && $user->isLoggedIn() && $row["BIN(`forumthreads`.`closed`)"] == 0) {

                $author = $_SESSION["userid"];
                $text = strip($_POST["text"]);

                if (strlen($text) > 20000) {

                    echo "Your comment must be less than 20 000 characters long.";

                } else {

                    $iq = $con->prepare("INSERT INTO `forumposts` VALUES(NULL, :text, :author, now(), NULL, :id)");
                    $iq->bindValue("author", $author, PDO::PARAM_INT);
                    $iq->bindValue("text", $text, PDO::PARAM_STR);
                    $iq->bindValue("id", $id, PDO::PARAM_INT);
                    $iq->execute();

                    $uq = $con->prepare("UPDATE `forumthreads` SET `forumthreads`.`lastdate` = now() WHERE `forumthreads`.`id` = :id");
                    $uq->bindValue("id", $id, PDO::PARAM_INT);
                    $uq->execute();

                }

            }

            ?>

            <?php

            if (!isset($tid)) {

            ?>
            <h1>
                <a href='/forums/<?php echo $row["id"]; ?>'><?php echo $row["title"]; ?></a>
            </h1>

            <?php echo (($row["BIN(`forumthreads`.`closed`)"] == 1) ? "<div class='forums-thread-closedtext'>closed</div>" : ""); ?>

            <?php

            if ($row["mapid"] != 0) {

                $sq = $con->prepare("SELECT `maps`.`name` FROM `maps` WHERE `maps`.`id` = :id");
                $sq->bindValue("id", $row["mapid"], PDO::PARAM_INT);
                $sq->execute();

                $sr = $sq->fetch();

                echo "<a href='/maps/#".$sr["name"]."'>&#x21AA; related map</a>";

            }

            ?>

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

                            <?php if (($user->isLoggedIn() && $row["authorid"] == $_SESSION["userid"]) || checkadmin()) echo "<a href='/forums/edit/".$row["id"]."'>edit</a>"; ?>
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

                while ($cr = $cq->fetch()) {

                    ?>

                    <div class='forums-post'>
                        <div class='forums-post-header'>
                            <div class='forums-post-number'>

                                <?php echo "#".$cn; ?>

                            </div>
                            <div class='forums-post-metadata'>

                                <?php if (($user->isLoggedIn() && $cr["authorid"] == $_SESSION["userid"]) || checkadmin()) echo "<a href='/forums/edit/".$row["id"]."/".$cr["id"]."'>edit</a>"; ?>
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

            if ($row["BIN(`forumthreads`.`closed`)"] == 0) {
                //writing a comment
                if ($user->isLoggedIn()) {

                     ?>
                    <hr><h1 class='comments-title'>Reply to this thread</h1>
                    <div class='comment-form'>
                        <?php
                        if (isset($tid)) {

                            echo "<form action='/news/".strip($_GET["id"])."/' method='post'>";

                        } else {

                            echo "<form action='/forums/".$id."/' method='post'>";

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
            header("Location: /forums");

        }

    } else {

        // SHOW ALL THREADS

        if ($user->isLoggedIn()) {

            ?>

            <a class='forums-createthread' href='/forums/add'>
                <span class='forums-createthread-sign'>+</span>
                <span class='forums-createthread-text'>create a new thread</span>
            </a>

            <?php

        }

        if (isset($_GET["cat"]) && is_numeric($_GET["cat"])) {

            $cat = strip($_GET["cat"]);

            $query = $con->prepare("SELECT `forumthreads`.`id`, `forumthreads`.`title`, `forumthreads`.`authorid`, `forumthreads`.`date`, `forumthreads`.`lastdate`, `forumthreads`.`forumcategory`, BIN(`forumthreads`.`closed`)
                                    FROM `forumthreads`
                                    WHERE `forumthreads`.`forumcategory` = :cat
                                    AND `forumthreads`.`forumcategory` <> 0
                                    ORDER BY `forumthreads`.`lastdate` DESC");
            $query->bindValue("cat", $cat, PDO::PARAM_INT);
            $query->execute();

            ?>

            <a class='forums-clearfilter' href='/forums'>&#x21A9; clear category filter</a>

            <?php

        } else {

            $query = $con->query("SELECT * FROM `forumthreads` WHERE `forumthreads`.`forumcategory` <> 0 ORDER BY `forumthreads`.`lastdate` DESC");

        }

        ?>

        <style type='text/css' scoped>

            <?php
            $cq = $con->query("SELECT * FROM `forumcategories`");

            while ($cr = $cq->fetch()) {

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

            while ($row = $query->fetch()) {

                ?>

                <tr class='forums-entry'>
                    <td class='forums-entry-category forums-category-<?php echo getcatname($row["forumcategory"]); ?>'>
                        <a class='forums-entry-category-text' href='/forums/category/<?php echo $row["forumcategory"]; ?>'>

                                <?php echo getcatname($row["forumcategory"]); ?>

                        </a>
                    </td>
                    <td class='forums-entry-main <?php echo (($row["BIN(`forumthreads`.`closed`)"] == 1) ? "forums-entry-closed" : ""); ?>'>
                        <a class='forums-entry-title' href='/forums/<?php echo $row["id"]; ?>'>

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
    $fr = $fq->fetch();

    return $fr["name"];

}
