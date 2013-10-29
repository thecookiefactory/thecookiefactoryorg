<?php

/**
 * user class
 *
 */
class user {

    /**
     * variables
     *
     * @var $id int
     * @var $name string
     * @var $steamid string
     * @var $admin int
     * @var $cookieh string
     * @var $date object
     * @var $twitchname string
     */
    protected $id           = null;
    protected $name         = null;
    protected $steamid      = null;
    protected $admin        = null;
    protected $cookieh      = null;
    protected $date         = null;
    protected $twitchname   = null;

    public function __construct($id = null) {

        global $con;

        if ($id != null) {

            try {

                $squery = $con->prepare("SELECT * FROM `users` WHERE `users`.`id` = :id");
                $squery->bindValue("id", $id, PDO::PARAM_INT);
                $squery->execute();

            } catch (PDOException $e) {

                echo "An error occured while trying to fetch data to the class. (" . $e->getMessage() . ")";

            }

            if ($squery->rowCount() == 1) {

                $srow = $squery->fetch();

                $this->id           = $srow["id"];
                $this->name         = $srow["name"];
                $this->steamid      = $srow["steamid"];
                $this->admin        = $srow["admin"];
                $this->cookieh      = $srow["cookieh"];
                $this->date         = new dtime($srow["date"]);
                $this->twitchname   = $srow["twitchname"];

            } else {

                echo "Could not find a user with the given id.";

            }

        }

    }

    public function getName($span = false) {

        if (!$this->isAdmin() || $span !== true) {

            return $this->name;

        } else if (isset($span) && $span === true) {

            return "<span class='admin-name'>" . $this->name . "</span>";

        }

    }

    public function getTwitchName() {

        return $this->twitchname;

    }

    public function isAdmin() {

        return ($this->admin != 0);

    }

    public function isLoggedIn() {

        return ($this->id != null);

    }

}

?>