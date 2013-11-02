<?php

/**
 * picture class
 *
 */
class picture {

    /**
     * variables
     *
     * @var $id int
     * @var $text string
     * @var $date object
     * @var $filename string
     */
    protected $id       = null;
    protected $text     = null;
    protected $date     = null;
    protected $filename = null;

    public function __construct($id = null) {

        global $con;

        if ($id != null) {

            try {

                $squery = $con->prepare("SELECT * FROM `pictures` WHERE `pictures`.`id` = :id");
                $squery->bindValue("id", $id, PDO::PARAM_INT);
                $squery->execute();

            } catch (PDOException $e) {

                echo "An error occured while trying to fetch data to the class. (" . $e->getMessage() . ")";

            }

            if ($squery->rowCount() == 1) {

                $srow = $squery->fetch();

                $this->id       = $srow["id"];
                $this->text     = $srow["text"];
                $this->date     = $srow["date"];
                $this->filename = $srow["filename"];

            } else {

                echo "Could not find a picture with the given id.";

            }

        }

    }

    public function getText() {

        return $this->text;

    }

    public function getFileName() {

        return $this->filename;

    }

}

?>
