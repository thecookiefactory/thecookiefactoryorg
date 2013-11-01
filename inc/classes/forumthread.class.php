<?php

/**
 * forum thread class
 *
 */
class forumthread {

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

                $squery = $con->prepare("SELECT * FROM `forumthreads` WHERE `forumthreads`.`id` = :id");
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
                $this->author           = $srow["author"];
                $this->date             = new dtime($srow["date"]);
                $this->editdate         = new dtime($srow["editdate"]);
                $this->lastdate         = new dtime($srow["lastdate"]);
                $this->forumcategory    = new forumcategory($srow["forumcategory"]);
                $this->map              = ($srow["mapid"] != 0) ? new map($srow["mapid"]) : null;
                $this->news             = ($srow["newsid"] != 0) ? new news($srow["newsid"]) : null;
                $this->closed           = $srow["closed"];

            } else {

                echo "Could not find a thread with the given id.";

            }

        }

    }

}

?>