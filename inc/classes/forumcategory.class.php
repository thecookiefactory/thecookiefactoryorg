<?php

if (!isset($r_c)) header("Location: /notfound.php");

require_once str_repeat("../", $r_c) . "inc/classes/dtime.class.php";

/**
 * forum category class
 *
 * function __construct
 *
 * function returnArray
 *
 * function getName
 *
 * function getLongName
 */
class forumcategory extends master {

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

    public function __construct($id = null, $field = null) {

        global $con;

        if ($id != null) {

            if ($field == "name") {

                try {

                    $squery = $con->prepare("SELECT * FROM `forumcategories` WHERE `forumcategories`.`name` = :id");
                    $squery->bindValue("id", $id, PDO::PARAM_STR);
                    $squery->execute();

                } catch (PDOException $e) {

                    echo "An error occured while trying to fetch data to the class.";

                }

            } else {

                try {

                    $squery = $con->prepare("SELECT * FROM `forumcategories` WHERE `forumcategories`.`id` = :id");
                    $squery->bindValue("id", $id, PDO::PARAM_INT);
                    $squery->execute();

                } catch (PDOException $e) {

                    echo "An error occured while trying to fetch data to the class.";

                }

            }

            if ($squery->rowCount() == 1) {

                $srow = $squery->fetch();

                $this->id           = $srow["id"];
                $this->name         = $srow["name"];
                $this->longname     = $srow["longname"];
                $this->hexcode      = $srow["hexcode"];
                $this->hoverhexcode = $srow["hoverhexcode"];
                $this->date         = new dtime($srow["date"]);

            } else {

                echo "Could not find a category with the given id.";

            }

        }

    }

    public function returnArray() {

        $a = array(
                    "id" => $this->id,
                    "name" => $this->name,
                    "longname" => $this->longname,
                    "hexcode" => $this->hexcode,
                    "hoverhexcode" => $this->hoverhexcode
                    );

        return $a;

    }

    public function getName() {

        return $this->name;

    }

    public function getLongName() {

        return $this->longname;

    }

}
