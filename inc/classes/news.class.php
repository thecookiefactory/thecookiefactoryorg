<?php

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

    public function __construct($id = null) {

        global $con;

        if ($id != null) {

            try {

                $squery = $con->prepare("SELECT * FROM `news` WHERE `news`.`id` = :id");
                $squery->bindValue("id", $id, PDO::PARAM_INT);
                $squery->execute();

            } catch (PDOException $e) {

                echo "An error occured while trying to fetch data to the class. (" . $e->getMessage() . ")";

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
                $this->comments = $srow["comments"];
                $this->live     = $srow["live"];
                $this->stringid = $srow["stringid"];

            } else {

                echo "Could not find a piece of news with the given id.";

            }

        }

    }

}

?>
