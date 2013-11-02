<?php

/**
 * game class
 *
 */
class game {

    /**
     * variables
     *
     * @var $id int
     * @var $name string
     * @var $steam int
     */
    protected $id       = null;
    protected $name     = null;
    protected $steamid  = null;

    public function __construct($id = null) {

        global $con;

        if ($id != null) {

            try {

                $squery = $con->prepare("SELECT * FROM `games` WHERE `games`.`id` = :id");
                $squery->bindValue("id", $id, PDO::PARAM_INT);
                $squery->execute();

            } catch (PDOException $e) {

                echo "An error occured while trying to fetch data to the class. (" . $e->getMessage() . ")";

            }

            if ($squery->rowCount() == 1) {

                $srow = $squery->fetch();

                $this->id       = $srow["id"];
                $this->name     = $srow["name"];
                $this->steamid  = $srow["steamid"];

            } else {

                echo "Could not find a game with the given id.";

            }

        }

    }

    public function getName() {

        return $this->name;

    }

    public function isSteamGame() {

        return ($this->steamid != null);

    }

    public function getSteamId() {

        return $this->steamid;

    }

}

?>
