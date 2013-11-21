<?php

if (!isset($r_c)) header("Location: /notfound.php");

require_once str_repeat("../", $r_c) . "inc/classes/dtime.class.php";
require_once str_repeat("../", $r_c) . "inc/classes/forumcategory.class.php";
require_once str_repeat("../", $r_c) . "inc/classes/forumpost.class.php";
require_once str_repeat("../", $r_c) . "inc/classes/map.class.php";
require_once str_repeat("../", $r_c) . "inc/classes/news.class.php";
require_once str_repeat("../", $r_c) . "inc/classes/user.class.php";

/**
 * forum thread class
 *
 * function __construct
 * (line 92)
 *
 * function display
 * (line 136)
 *
 * function displayRow
 * (line 221)
 *
 * function commentProcess
 * (line 260)
 *
 * function firstPost
 * (line 292)
 *
 * function commentForm
 * (line 335)
 *
 * function addNew
 * (line 358)
 *
 * function addProcess
 * (line 374)
 *
 * function addForm
 * (line 420)
 *
 * function edit
 * (line 464)
 *
 * function editProcess
 * (line 489)
 *
 * function editForm
 * (line 584)
 *
 * function replyCount
 * (line 638)
 *
 * function isNewsThread
 * (line 650)
 *
 * function getNewsStringId
 * (line 656)
 *
 * function isClosed
 * (line 662)
 */
class forumthread extends master {

    /**
     * variables
     *
     * @var $id int
     * @var $title string
     * @var $text string
     * @var $author object
     * @var $date object
     * @var $editdate object
     * @var $lastdate object
     * @var $forumcategory object
     * @var $map object
     * @var $news object
     * @var $closed int
     */
    protected $id               = null;
    protected $title            = null;
    protected $text             = null;
    protected $author           = null;
    protected $date             = null;
    protected $editdate         = null;
    protected $lastdate         = null;
    protected $forumcategory    = null;
    protected $map              = null;
    protected $news             = null;
    protected $closed           = null;

    public function __construct($id = null) {

        global $con;

        if ($id != null) {

            try {

                $squery = $con->prepare("SELECT *, BIN(`forumthreads`.`closed`) FROM `forumthreads` WHERE `forumthreads`.`id` = :id");
                $squery->bindValue("id", $id, PDO::PARAM_INT);
                $squery->execute();

            } catch (PDOException $e) {

                echo "An error occured while trying to fetch data to the class. (" . $e->getMessage() . ")";

            }

            if ($squery->rowCount() == 1) {

                $srow = $squery->fetch();

                $this->id               = $srow["id"];
                $this->title            = $srow["title"];
                $this->text             = $srow["text"];
                $this->author           = new user($srow["authorid"]);
                $this->date             = new dtime($srow["date"]);
                $this->editdate         = ($srow["editdate"] != null) ? new dtime($srow["editdate"]) : null;
                $this->lastdate         = new dtime($srow["lastdate"]);
                $this->forumcategory    = ($srow["forumcategory"] != 0) ? new forumcategory($srow["forumcategory"]) : null;
                $this->map              = ($srow["mapid"] != 0) ? new map($srow["mapid"]) : null;
                $this->news             = ($srow["newsid"] != 0) ? new news($srow["newsid"]) : null;
                $this->closed           = (int) $srow["BIN(`forumthreads`.`closed`)"];

            } else {

                echo "Could not find a thread with the given id.";

            }

        }

    }

    public function display() {

        global $con;
        global $user;

        $this->commentProcess();

        if (!$this->isNewsThread()) {

            ?>
            <h1>
                <a href='/forums/<?php echo $this->id; ?>'><?php echo $this->title; ?></a>
            </h1>

            <?php echo ($this->isClosed() ? "<div class='forums-thread-closedtext'>closed</div>" : ""); ?>

            <?php

            if ($this->map != null) {

                echo "<a href='/maps/#" . $this->map->getName() . "'>&#x21AA; related map</a>";

            }

        }

        ?>

        <div class='forums-posts'>

            <?php
            if (!$this->isNewsThread()) {

                $this->firstPost();

            }

            $selectPosts = $con->prepare("SELECT `forumposts`.`id` FROM `forumposts` WHERE `forumposts`.`threadid` = :id");
            $selectPosts->bindValue("id", $this->id, PDO::PARAM_INT);
            $selectPosts->execute();

            $cn = $this->isNewsThread() ? 1 : 2;

            while ($foundPost = $selectPosts->fetch()) {

                $post = new forumpost($foundPost["id"]);
                $post->display($cn);
                $cn++;

            }

        ?>

        </div>

        <?php

        if (!$this->isClosed()) {

            if ($user->isReal()) {

                ?>
                <hr><h1 class='comments-title'>Reply to this thread</h1>
                <?php
                $this->commentForm();

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

    }

    public function displayRow() {

        global $con;

        ?>

        <tr class='forums-entry'>
            <?php $this->forumcategory->tableBox(); ?>
            <td class='forums-entry-main <?php echo ($this->isClosed() ? "forums-entry-closed" : ""); ?>'>
                <a class='forums-entry-title' href='/forums/<?php echo $this->id; ?>'>

                    <?php echo $this->title; ?>

                </a>
                <br>
                <span class='forums-entry-metadata'>

                    created by <?php echo $this->author->getName() . " " . $this->date->display(); ?>

                </span>
            </td>
            <td class='forums-entry-modifydate'>
                <span class='forums-entry-miniheader'>Last reply posted</span><br>

                <?php echo $this->lastdate->display(); ?>

            </td>
            <td class='forums-entry-postcount'>
                <span class='forums-entry-miniheader'>Thread has</span><br>

                <?php echo $this->replyCount() . ($this->replyCount() == 1 ? " reply" : " replies"); ?>

            </td>
        </tr>

        <?php

    }

    protected function commentProcess() {

        global $con;
        global $user;

        if (isset($_POST["cp"]) && isset($_POST["text"]) && vf($_POST["text"]) && $user->isReal() && !$this->isClosed()) {

            $author = $user->getId();
            $text = strip($_POST["text"]);

            if (strlen($text) > 20000) {

                echo "Your comment must be less than 20 000 characters long.";

            } else {

                $insertComment = $con->prepare("INSERT INTO `forumposts` VALUES(DEFAULT, :text, :author, DEFAULT, DEFAULT, :id)");
                $insertComment->bindValue("author", $author, PDO::PARAM_INT);
                $insertComment->bindValue("text", $text, PDO::PARAM_STR);
                $insertComment->bindValue("id", $this->id, PDO::PARAM_INT);
                $insertComment->execute();

                $updateLastDate = $con->prepare("UPDATE `forumthreads` SET `forumthreads`.`lastdate` = now() WHERE `forumthreads`.`id` = :id");
                $updateLastDate->bindValue("id", $this->id, PDO::PARAM_INT);
                $updateLastDate->execute();

            }

        }

    }

    protected function firstPost() {

        global $user;

        ?>

        <div class='forums-post'>
            <div class='forums-post-header'>
                <div class='forums-post-number'>
                    #1
                </div>
                <div class='forums-post-metadata'>

                    <?php if (($user->isReal() && $this->author->getId() == $user->getId() && !$this->isClosed()) || $user->isAdmin()) echo "<a href='/forums/edit/" . $this->id . "'>edit</a>"; ?>
                    <?php if ($this->editdate != null) echo "last edited " . $this->editdate->display(); ?>

                    <span class='forums-post-metadata-item'>
                        <span class='forums-post-author'>

                            <?php echo $this->author->getName(); ?>

                        </span>
                    </span>
                    <span class='forums-post-metadata-item'>
                        <span class='forums-post-date'>

                            <?php echo $this->date->display(); ?>

                        </span>
                    </span>
                </div>
            </div>
            <div class='forums-post-text'>

                <p><?php echo tformat($this->text); ?></p>

            </div>
        </div>

        <?php

    }

    protected function commentForm() {

        ?>
        <div class='comment-form'>
            <?php
            if ($this->isNewsThread()) {

                echo "<form action='/news/" . $this->news->getStringId() . "/' method='post'>";

            } else {

                echo "<form action='/forums/" . $this->id . "/' method='post'>";

            }
            ?>
                <textarea name='text' class='comment-textarea' required maxlength='20000'></textarea>
                <input type='submit' name='cp' value='&gt;' class='comment-submitbutton'>
            </form>
        </div>
        <?php

    }

    public function addNew() {

        global $con;

        if (isset($_POST["addnew"]) && (isset($_POST["cat"]) && vf($_POST["cat"])) && (isset($_POST["title"]) && vf($_POST["title"])) && (isset($_POST["text"]) && vf($_POST["text"]))) {

            $this->addProcess();

        } else {

            $this->addForm();

        }

    }

    protected function addProcess() {

        global $con;
        global $user;

        $authorid = $user->getId();
        $cat = new forumcategory(strip($_POST["cat"]));

        if (!$cat->isReal()) {

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

                    $insertThread = $con->prepare("INSERT INTO `forumthreads` VALUES(DEFAULT, :title, :text, :authorid, DEFAULT, DEFAULT, DEFAULT, :cat, DEFAULT, DEFAULT, 0)");
                    $insertThread->bindValue("authorid", $authorid, PDO::PARAM_INT);
                    $insertThread->bindValue("title", $title, PDO::PARAM_STR);
                    $insertThread->bindValue("text", $text, PDO::PARAM_STR);
                    $insertThread->bindValue("cat", $cat->getId(), PDO::PARAM_INT);
                    $insertThread->execute();

                    header("Location: /forums/" . $con->lastInsertId());
                }

            }

        }

    }

    protected function addForm() {

        global $con;

        ?>
        <form action='/forums/add/' method='post'>
            <label class='forums-newpost-select-label' for='cat'>Category:
            <select class='forums-newpost-select' name='cat'>

            <?php
            $selectCategories = $con->query("SELECT `forumcategories`.`id` FROM `forumcategories` ORDER BY `forumcategories`.`name` ASC");

            while ($foundCategory = $selectCategories->fetch()) {
                $cat = new forumcategory($foundCategory["id"]);
                ?>

                <option value='<?php echo $cat->getId(); ?>'><?php echo $cat->getLongName(); ?></option>

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

    public function edit() {

        global $con;
        global $user;

        if ((($this->author->getId() != $user->getId()) || $this->isClosed()) && !$user->isAdmin()) {

            echo "You dont have the right!!";

        } else {

            if (isset($_POST["edit"]) && (isset($_POST["cat"]) && vf($_POST["cat"])) && (isset($_POST["title"]) && vf($_POST["title"])) && (isset($_POST["text"]) && vf($_POST["text"]))) {

                $this->editProcess();

            } else {

                $this->editForm();

            }

        }

    }

    protected function editProcess() {

        global $con;
        global $user;

        $cat = new forumcategory(strip($_POST["cat"]));

        if (!$cat->isReal()) {

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

                    $updateThread = $con->prepare("UPDATE `forumthreads` SET `forumthreads`.`forumcategory` = :cat, `forumthreads`.`title` = :title, `forumthreads`.`text` = :text, `forumthreads`.`editdate` = now() WHERE `forumthreads`.`id` = :tid");
                    $updateThread->bindValue("cat", $cat->getId(), PDO::PARAM_INT);
                    $updateThread->bindValue("title", $title, PDO::PARAM_STR);
                    $updateThread->bindValue("text", $text, PDO::PARAM_STR);
                    $updateThread->bindValue("tid", $this->id, PDO::PARAM_INT);
                    $updateThread->execute();

                }

            }

        }

        if ($user->isAdmin()) {

            if (isset($_POST["closed"]) && $_POST["closed"] == "on" && !$this->isClosed()) {

                $closeThread = $con->prepare("UPDATE `forumthreads` SET `forumthreads`.`closed` = b'1' WHERE `forumthreads`.`id` = :id");
                $closeThread->bindValue("id", $this->getId(), PDO::PARAM_INT);
                $closeThread->execute();

            } else if (!isset($_POST["closed"]) && $this->isClosed()) {

                $openThread = $con->prepare("UPDATE `forumthreads` SET `forumthreads`.`closed` = b'0' WHERE `forumthreads`.`id` = :id");
                $openThread->bindValue("id", $this->getId(), PDO::PARAM_INT);
                $openThread->execute();

            }

            if (isset($_POST["delete"]) && $_POST["delete"] == "on") {

                if ($this->map != null) {

                    $updateMapComments = $con->prepare("UPDATE `maps` SET `maps`.`comments` = 0 WHERE `maps`.`id` = :id");
                    $updateMapComments->bindValue("id", $this->map->getId(), PDO::PARAM_INT);
                    $updateMapComments->execute();

                }

                $deletePosts = $con->prepare("DELETE FROM `forumposts` WHERE `forumposts`.`threadid` = :tid");
                $deletePosts->bindValue("tid", $this->id, PDO::PARAM_INT);
                $deletePosts->execute();

                $deleteThread = $con->prepare("DELETE FROM `forumthreads` WHERE `forumthreads`.`id` = :tid");
                $deleteThread->bindValue("tid", $this->id, PDO::PARAM_INT);
                $deleteThread->execute();

                if ($deleteThread->rowCount() == 1) {

                    header("Location: /forums");

                }

            }

        }

        // redirect
        if ($updateThread->rowCount() == 1) {

            header("Location: /forums/" . $this->id);

        }

    }

    protected function editForm() {

        global $con;
        global $user;

        ?>
        <form action='/forums/edit/<?php echo $this->id; ?>/' method='post'>
            <label class='forums-newpost-select-label' for='cat'>Category:
            <select class='forums-newpost-select' name='cat'>

            <?php
                $selectCategories = $con->query("SELECT `forumcategories`.`id` FROM `forumcategories` ORDER BY `forumcategories`.`name` ASC");

                while ($foundCategory = $selectCategories->fetch()) {
                    $cat = new forumcategory($foundCategory["id"]);
                    ?>

                    <option value='<?php echo $cat->getId(); ?>'<?php if ($cat->getId() == $this->forumcategory->getId()) echo " selected" ?>><?php echo $cat->getLongName(); ?></option>

                    <?php
                }
            ?>

        </select></label>
        <input class='forums-newpost-submit' type='submit' name='edit' value='Submit &#x27A8;'>
            <h1>
                <input class='forums-newpost-title' type='text' name='title' autofocus required placeholder='Enter a title here...' maxlength='37' value='<?php echo $this->title; ?>'>
            </h1>
        <div class='forums-post'>
            <div class='forums-post-header'>
                <div class='forums-post-number'>
                    #1
                </div>
            </div>
            <div>
                <textarea class='forums-newpost-text' name='text' required placeholder='Type your post here...' maxlength='20000'><?php echo $this->text; ?></textarea>
            </div>
        </div>

        <?php
            if ($user->isAdmin()) {

                echo "delete this whole thread <input type='checkbox' name='delete'>";
                echo "closed thread?"; echo ($this->isClosed()) ? " <input type='checkbox' name='closed' checked>" : " <input type='checkbox' name='closed'>";

            }
        ?>

        </form>

        <?php

    }

    public function replyCount() {

        global $con;

        $selectPosts = $con->prepare("SELECT `forumposts`.`id` FROM `forumposts` WHERE `forumposts`.`threadid` = :id");
        $selectPosts->bindValue("id", $this->id, PDO::PARAM_INT);
        $selectPosts->execute();

        return $selectPosts->rowCount();

    }

    public function isNewsThread() {

        return ($this->news != null);

    }

    public function getNewsStringId() {

        return $this->news->getStringId();

    }

    public function isClosed() {

        return ($this->closed != null);

    }

}
