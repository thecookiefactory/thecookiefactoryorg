<?php

/**
 * custom page class
 *
 */
class custompage {

    /**
     * variables
     *
     * @var $id int
     * @var $title string
     * @var $text string
     * @var $date object
     * @var $editdate object
     * @var $live int
     * @var $stringid string
     */
    protected $id       = null;
    protected $title    = null;
    protected $text     = null;
    protected $date     = null;
    protected $editdate = null;
    protected $live     = null;
    protected $stringid = null;

    public function __construct($stringid = null) {

        global $con;

        if ($stringid != null) {

            try {

                $squery = $con->prepare("SELECT * FROM `custompages` WHERE `custompages`.`stringid` = :stringid");
                $squery->bindValue("stringid", $stringid, PDO::PARAM_STR);
                $squery->execute();

            } catch (PDOException $e) {

                echo "An error occured while trying to fetch data to the class. (" . $e->getMessage() . ")";

            }

            if ($squery->rowCount() == 1) {

                $srow = $squery->fetch();

                $this->id       = $srow["id"];
                $this->title    = $srow["title"];
                $this->text     = $srow["text"];
                $this->date     = new dtime($srow["date"]);
                $this->editdate = new dtime($srow["editdate"]);
                $this->live     = $srow["live"];
                $this->stringid = $srow["stringid"];

            } else {

                echo "Could not find a page with the given id.";

            }

        }

    }

    public function display() {

        return Markdown($this->text);

    }

}

?>
