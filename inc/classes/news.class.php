<?php

if (!isset($r_c)) header("Location: /notfound.php");

require_once str_repeat("../", $r_c) . "inc/classes/dtime.class.php";
require_once str_repeat("../", $r_c) . "inc/classes/forumthread.class.php";
require_once str_repeat("../", $r_c) . "inc/classes/user.class.php";

/**
 * news class
 *
 */
class news {

    /**
     * variables
     *
     * @var $id int
     * @var $title string
     * @var $text string
     * @var $author object
     * @var $date object
     * @var $editor object
     * @var $editdate object
     * @var $comments int
     * @var $live int
     * @var $stringid string
     */
    protected $id       = null;
    protected $title    = null;
    protected $text     = null;
    protected $author   = null;
    protected $date     = null;
    protected $editor   = null;
    protected $editdate = null;
    protected $comments = null;
    protected $live     = null;
    protected $stringid = null;

    public function __construct($id = null, $field = null) {

        global $con;

        if ($id != null) {

            if ($field == "stringid") {

                try {

                    $squery = $con->prepare("SELECT *, BIN(`news`.`comments`), BIN(`news`.`live`) FROM `news` WHERE `news`.`stringid` = :id");
                    $squery->bindValue("id", $id, PDO::PARAM_STR);
                    $squery->execute();

                } catch (PDOException $e) {

                    echo "An error occured while trying to fetch data to the class. (" . $e->getMessage() . ")";

                }

            } else {

                try {

                    $squery = $con->prepare("SELECT *, BIN(`news`.`comments`), BIN(`news`.`live`) FROM `news` WHERE `news`.`id` = :id");
                    $squery->bindValue("id", $id, PDO::PARAM_INT);
                    $squery->execute();

                } catch (PDOException $e) {

                    echo "An error occured while trying to fetch data to the class. (" . $e->getMessage() . ")";

                }

            }

            if ($squery->rowCount() == 1) {

                $srow = $squery->fetch();

                $this->id       = $srow["id"];
                $this->title    = $srow["title"];
                $this->text     = $srow["text"];
                $this->author   = new user($srow["authorid"]);
                $this->date     = new dtime($srow["date"]);
                $this->editor   = new user($srow["editorid"]);
                $this->editdate = new dtime($srow["editdate"]);
                $this->comments = (int) $srow["BIN(`news`.`comments`)"];
                $this->live     = (int) $srow["BIN(`news`.`live`)"];
                $this->stringid = $srow["stringid"];

            }

        }

    }

    public function display($loc = null) {

        global $con;
        global $r_c;
        global $p;

        ?>

        <div class='article-header'>
        <div class='article-title'>
        <h1>

        <?php

        if ($loc == "main") {

            echo $this->title;

        } else {

            echo "<a href='/news/" . $this->stringid . "'>" . $this->title . "</a>";

        }

        ?>

        </h1>
        </div>

        <div class='article-metadata'>

        <?php

        if ($loc != "main" && $this->comments == 1) {

            $ct = $con->prepare("SELECT `forumthreads`.`id` FROM `forumthreads` WHERE `forumthreads`.`newsid` = :id");
            $ct->bindValue("id", $this->id, PDO::PARAM_INT);
            $ct->execute();

            $tid = $ct->fetch();

            $thread = new forumthread($tid["id"]);
            $commnum = $thread->replyCount();
            ?>

            <?php
            if ($commnum != 1) {
                ?>
                <span class='article-metadata-item'><a href='/news/<?php echo $this->stringid; ?>#comments'><?php echo $commnum; ?> comments</a></span>
                <?php
            } else {
                ?>
                <span class='article-metadata-item'><a href='/news/<?php echo $this->stringid; ?>#comments'><?php echo $commnum; ?> comment</a></span>
                <?php
            }

        }

        ?>

        <span class='article-metadata-item'><span class='article-author'><?php echo $this->author->getName(); ?></span></span>
        <span class='article-metadata-item'><span class='article-date'><?php echo $this->date->display(); ?></span></span>
        </div>

        <?php

        if ($this->editor != null && $this->editdate > $this->date) {
            ?>

            <div class='article-edit-metadata'>
            <span class='article-metadata-item'><span class='article-author'><?php echo $this->editor->getName(); ?></span></span>
            <span class='article-metadata-item'><span class='article-date'><?php echo $this->editdate->display(); ?></span></span>
            </div>

            <?php
        }

        ?>

        </div>
        <article>
        <span class='article-text'><?php echo Markdown($this->text); ?></span>
        </article>
        <?php

        if ($loc == "main") {

            echo "<hr id='comments'>";

            if ($this->comments == 1) {

                $sq = $con->prepare("SELECT `forumthreads`.`id` FROM `forumthreads` WHERE `forumthreads`.`newsid` = :id");
                $sq->bindValue("id", $this->id, PDO::PARAM_INT);
                $sq->execute();

                $tid = $sq->fetch();

                $tid = $tid["id"];

                require_once "inc/forums.php";

            } else {

                ?>

                <h1 class='comments-title'>Commenting disabled</h1>

                <?php

            }

        } else {

            echo "<hr class='article-separator'>";

        }

    }

    public function getStringId() {

        return $this->stringid;

    }

    public function isReal() {

        return ($this->id != null);

    }

}
