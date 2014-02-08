<?php

if (!isset($r_c)) header("Location: /notfound.php");

require_once str_repeat("../", $r_c) . "inc/classes/user.class.php";

/**
 * about page class
 *
 * function __construct
 *
 * function returnArray
 */
class about extends master {

    /**
     * variables
     *
     * @var $id int
     * @var $user object
     * @var $firstname string
     * @var $lastname string
     * @var $description string
     * @var $website string
     * @var $email string
     * @var $github string
     * @var $twitter string
     * @var $twitch string
     * @var $youtube string
     * @var $steam string
     * @var $reddit string
     */
    protected $id           = null;
    protected $user         = null;
    protected $firstname    = null;
    protected $lastname     = null;
    protected $description  = null;
    protected $website      = null;
    protected $email        = null;
    protected $github       = null;
    protected $twitter      = null;
    protected $twitch       = null;
    protected $youtube      = null;
    protected $steam        = null;
    protected $reddit       = null;

    public function __construct($userid = null) {

        global $con;

        if ($userid != null) {

            try {

                $squery = $con->prepare("SELECT * FROM `about` WHERE `about`.`userid` = :userid");
                $squery->bindValue("userid", $userid, PDO::PARAM_STR);
                $squery->execute();

            } catch (PDOException $e) {

                echo "An error occured while trying to fetch data to the class.";

            }

            if ($squery->rowCount() == 1) {

                $srow = $squery->fetch();

                $this->id           = $srow["id"];
                $this->user         = new user($srow["userid"]);
                $this->firstname    = $srow["firstname"];
                $this->lastname     = $srow["lastname"];
                $this->description  = $srow["description"];
                $this->website      = ($srow["website"] != null) ? $srow["website"] : null;
                $this->email        = ($srow["email"] != null) ? $srow["email"] : null;
                $this->github       = ($srow["github"] != null) ? $srow["github"] : null;
                $this->twitter      = ($srow["twitter"] != null) ? $srow["twitter"] : null;
                $this->twitch       = ($srow["twitch"] != null) ? $srow["twitch"] : null;
                $this->youtube      = ($srow["youtube"] != null) ? $srow["youtube"] : null;
                $this->steam        = ($srow["steam"] != null) ? $srow["steam"] : null;
                $this->reddit       = ($srow["reddit"] != null) ? $srow["reddit"] : null;

            } else {

                echo "Could not find a page with the given id.";

            }

        }

    }

    public function returnArray() {

        $a = array(
                    "username" => $this->user->getName(),
                    "firstname" => $this->firstname,
                    "lastname" => $this->lastname,
                    "description" => $this->description,
                    "links" => array()
                    );

        if ($this->website != null) $a["links"]["website"] = $this->website;
        if ($this->email != null) $a["links"]["email"] = $this->email;
        if ($this->github != null) $a["links"]["github"] = $this->github;
        if ($this->twitter != null) $a["links"]["twitter"] = $this->twitter;
        if ($this->twitch != null) $a["links"]["twitch"] = $this->twitch;
        if ($this->youtube != null) $a["links"]["youtube"] = $this->youtube;
        if ($this->steam != null) $a["links"]["steam"] = $this->steam;
        if ($this->reddit != null) $a["links"]["reddit"] = $this->reddit;

        return $a;

    }

}
