<?php

if (!isset($r_c)) header("Location: /notfound.php");

require_once str_repeat("../", $r_c) . "inc/classes/dtime.class.php";
require_once str_repeat("../", $r_c) . "inc/classes/forumthread.class.php";
require_once str_repeat("../", $r_c) . "inc/classes/user.class.php";

/**
 * news class
 *
 * function __construct
 *
 * function returnArray
 *
 * function getStringId
 */
class news extends master {

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
                $this->editor   = ($srow["editorid"] != null) ? new user($srow["editorid"]) : null;
                $this->editdate = ($srow["editdate"] != null) ? new dtime($srow["editdate"]) : null;
                $this->comments = (int) $srow["BIN(`news`.`comments`)"];
                $this->live     = (int) $srow["BIN(`news`.`live`)"];
                $this->stringid = $srow["stringid"];

            }

        }

    }

    public function returnArray() {

        global $con;

        $a = array(
                    "id" => $this->id,
                    "title" => $this->title,
                    "text" => $this->text,
                    "author" => $this->author->getName(),
                    "date" => $this->date->display(),
                    "editor" => 0,
                    "editdate" => 0,
                    "comments" => $this->comments,
                    "live" => $this->live,
                    "stringid" => $this->stringid
                    );

        if ($this->editor != null) {

            $a["editor"] = $this->editor->getName();
            $a["editdate"] = $this->editdate->display();

        }

        if ($this->comments == 1) {

            $selectThreadId = $con->prepare("SELECT `forumthreads`.`id` FROM `forumthreads` WHERE `forumthreads`.`newsid` = :id");
            $selectThreadId->bindValue("id", $this->id, PDO::PARAM_INT);
            $selectThreadId->execute();

            $threadData = $selectThreadId->fetch();

            $thread = new forumthread($threadData["id"]);
            $a["commentcount"] = $thread->replyCount();

        }

        return $a;

    }

    public function getStringId() {

        return $this->stringid;

    }

}
