<?php

require_once "inc/classes/dtime.class.php";
require_once "inc/classes/game.class.php";
require_once "inc/classes/user.class.php";

/**
 * map class
 *
 */
class map {

    /**
     * variables
     *
     * @var $id int
     * @var $name string
     * @var $text string
     * @var $author object
     * @var $date object
     * @var $editdate object
     * @var $dl string
     * @var $extension string
     * @var $comments int
     * @var $game object
     * @var $link string
     * @var $downloadcount int
     */
    protected $id               = null;
    protected $name             = null;
    protected $text             = null;
    protected $author           = null;
    protected $date             = null;
    protected $editdate         = null;
    protected $dl               = null;
    protected $extension        = null;
    protected $comments         = null;
    protected $game             = null;
    protected $link             = null;
    protected $downloadcount    = null;

    public function __construct($id = null) {

        global $con;

        if ($id != null) {

            try {

                $squery = $con->prepare("SELECT * FROM `maps` WHERE `maps`.`id` = :id");
                $squery->bindValue("id", $id, PDO::PARAM_INT);
                $squery->execute();

            } catch (PDOException $e) {

                echo "An error occured while trying to fetch data to the class. (" . $e->getMessage() . ")";

            }

            if ($squery->rowCount() == 1) {

                $srow = $squery->fetch();

                $this->id               = $srow["id"];
                $this->name             = $srow["name"];
                $this->text             = $srow["text"];
                $this->author           = new user($srow["authorid"]);
                $this->date             = new dtime($srow["date"]);
                $this->editdate         = new dtime($srow["editdate"]);
                $this->dl               = $srow["dl"];
                $this->extension        = $srow["extension"];
                $this->comments         = $srow["comments"];
                $this->game             = new game($srow["gameid"]);
                $this->link             = $srow["link"];
                $this->downloadcount    = $srow["downloadcount"];

            } else {

                echo "Could not find a map with the given id.";

            }

        }

    }

}

?>
