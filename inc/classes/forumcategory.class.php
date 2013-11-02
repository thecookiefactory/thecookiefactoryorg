<?php

if (!isset($r_c)) header("Location: /notfound.php");

require_once str_repeat("../", $r_c) . "inc/classes/dtime.class.php";

/**
 * forum category class
 *
 */
class forumcategory {

    /**
     * variables
     *
     * @var $id int
     * @var $name string
     * @var $longname string
     * @var $hexcode string
     * @var $hoverhexcode string
     * @var $date object
     */
    protected $id           = null;
    protected $name         = null;
    protected $longname     = null;
    protected $hexcode      = null;
    protected $hoverhexcode = null;
    protected $date         = null;

    public function __construct($id = null) {

        global $con;

        if ($id != null) {

            try {

                $squery = $con->prepare("SELECT * FROM `forumcategories` WHERE `forumcategories`.`id` = :id");
                $squery->bindValue("id", $id, PDO::PARAM_INT);
                $squery->execute();

            } catch (PDOException $e) {

                echo "An error occured while trying to fetch data to the class. (" . $e->getMessage() . ")";

            }

            if ($squery->rowCount() == 1) {

                $srow = $squery->fetch();

                $this->id           = $srow["id"];
                $this->name         = $srow["name"];
                $this->longname     = $srow["longname"];
                $this->hexcode      = new dtime($srow["hexcode"]);
                $this->hoverhexcode = new dtime($srow["hoverhexcode"]);
                $this->date         = $srow["date"];

            } else {

                echo "Could not find a category with the given id.";

            }

        }

    }

    public function getName() {

        return $this->name;

    }

    public function getLongName() {

        return $this->longname;

    }

    public function isReal() {

        return ($this->id != null);

    }

}

?>
