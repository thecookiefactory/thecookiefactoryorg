<?php

if (!isset($r_c)) header("Location: /notfound.php");

require_once str_repeat("../", $r_c) . "inc/classes/dtime.class.php";
require_once str_repeat("../", $r_c) . "inc/classes/user.class.php";

/**
 * forum post class
 *
 * function __construct
 * (line 45)
 *
 * function display
 * (line 84)
 *
 * function edit
 * (line 129)
 *
 * function editProcess
 * (line 155)
 *
 * function editForm
 * (line 200)
 */
class forumpost extends master {

    /**
     * variables
     *
     * @var $id int
     * @var $text string
     * @var $author object
     * @var $date object
     * @var $editdate object
     * @var $thread object
     */
    protected $id       = null;
    protected $text     = null;
    protected $author   = null;
    protected $date     = null;
    protected $editdate = null;
    protected $threadid = null;

    public function __construct($id = null) {

        global $con;

        if ($id != null) {

            try {

                $squery = $con->prepare("SELECT * FROM `forumposts` WHERE `forumposts`.`id` = :id");
                $squery->bindValue("id", $id, PDO::PARAM_INT);
                $squery->execute();

            } catch (PDOException $e) {

                echo "An error occured while trying to fetch data to the class. (" . $e->getMessage() . ")";

            }

            if ($squery->rowCount() == 1) {

                $srow = $squery->fetch();

                $this->id       = $srow["id"];
                $this->text     = $srow["text"];
                $this->author   = new user($srow["authorid"]);
                $this->date     = new dtime($srow["date"]);
                $this->editdate = ($srow["editdate"] != null) ? new dtime($srow["editdate"]) : null;
                $this->threadid = $srow["threadid"];

            } else {

                echo "Could not find a post with the given id.";

            }

        }

    }

    public function display($cn = 0) {

        global $user;

        $thread = new forumthread($this->threadid);

        ?>
        <div class='forums-post'>
            <div class='forums-post-header'>
                <div class='forums-post-number'>

                    <?php echo "#" . $cn; ?>

                </div>
                <div class='forums-post-metadata'>

                    <?php if (($user->isReal() && $this->author->getId() == $user->getId() && !$thread->isClosed()) || $user->isAdmin()) echo "<a href='/forums/edit/" . $this->threadid . "/" . $this->id . "'>edit</a>"; ?>
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

    public function edit() {

        global $user;

        $thread = new forumthread($this->threadid);

        if (($this->author->getId() != $user->getId() || $thread->isClosed()) && !$user->isAdmin()) {

            echo "You dont have the right!!";

        } else {

            if (isset($_POST["edit"]) && (isset($_POST["text"]) && vf($_POST["text"]))) {

                $this->editProcess();

            } else {

                $this->editForm();

            }

        }

    }

    protected function editProcess() {

        global $con;
        global $user;

        if ($user->isAdmin() && isset($_POST["delete"]) && $_POST["delete"] == "on") {

            $deletePost = $con->prepare("DELETE FROM `forumposts` WHERE `forumposts`.`id` = :pid");
            $deletePost->bindValue("pid", $this->id, PDO::PARAM_INT);
            $deletePost->execute();

            if (isset($_SESSION["lp"])) {

                header("Location: /" . $_SESSION["lp"]);

            } else {

                header("Location: /news");

            }

        } else {

            $text = strip($_POST["text"]);

            if (strlen($text) > 20000) {

                echo "Your comment must be less than 20 000 characters long.";

            } else {

                $updatePost = $con->prepare("UPDATE `forumposts` SET `forumposts`.`text` = :text WHERE `forumposts`.`id` = :pid");
                $updatePost->bindValue("text", $text, PDO::PARAM_STR);
                $updatePost->bindValue("pid", $this->id, PDO::PARAM_INT);
                $updatePost->execute();

                $thread = new forumthread($this->threadid);

                if (!$thread->isNewsThread()) {

                    header("Location: /forums/" . $this->threadid);

                } else {

                    header("Location: /news/" . $thread->getNewsStringId());

                }

            }

        }

    }

    protected function editForm() {

        global $user;

        ?>
        <form action='/forums/edit/<?php echo $this->threadid; ?>/<?php echo $this->id; ?>/' method='post'>

        <input class='forums-newpost-submit forums-edit-submit' type='submit' name='edit' value='Submit &#x27A8;'>
        <div class='forums-post'>
            <div class='forums-post-header'>
                <div class='forums-post-number'>
                    #N
                </div>
            </div>
            <div>
                <textarea class='forums-newpost-text' name='text' autofocus required placeholder='Type your post here...' maxlength='20000'><?php echo $this->text; ?></textarea>
            </div>
        </div>

        <?php
        if ($user->isAdmin()) {

            echo "delete this reply <input type='checkbox' name='delete'>";

        }
        ?>

        </form>

        <?php

    }

}
