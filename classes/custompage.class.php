<?php

if (!isset($r_c)) header("Location: /notfound.php");

require_once str_repeat("../", $r_c) . "classes/dtime.class.php";
require_once str_repeat("../", $r_c) . "classes/master.class.php";

/**
 * custom page class
 *
 * function __construct
 *
 * function display
 */
class custompage extends master {

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

    public function __construct($stringid = null, $field = null) {

        global $con;

        if ($stringid != null) {

            if ($field == "id") {

                try {

                    $squery = $con->prepare("SELECT *, BIN(`custompages`.`live`) FROM `custompages` WHERE `custompages`.`id` = :stringid");
                    $squery->bindValue("stringid", $stringid, PDO::PARAM_STR);
                    $squery->execute();

                } catch (PDOException $e) {

                    die("An error occured while trying to fetch data to the class.");

                }

            } else {

                try {

                    $squery = $con->prepare("SELECT *, BIN(`custompages`.`live`) FROM `custompages` WHERE `custompages`.`stringid` = :stringid");
                    $squery->bindValue("stringid", $stringid, PDO::PARAM_STR);
                    $squery->execute();

                } catch (PDOException $e) {

                    die("An error occured while trying to fetch data to the class.");

                }

            }

            if ($squery->rowCount() == 1) {

                $srow = $squery->fetch();

                $this->id       = $srow["id"];
                $this->title    = $srow["title"];
                $this->text     = $srow["text"];
                $this->date     = new dtime($srow["date"]);
                $this->editdate = ($srow["editdate"] != null) ? new dtime($srow["editdate"]) : null;
                $this->live     = (int) $srow["BIN(`custompages`.`live`)"];
                $this->stringid = $srow["stringid"];

            } else {

                echo "Could not find a page with the given id.";

            }

        }

    }

    public function returnArray() {

        $a = array(
                    "id" => $this->id,
                    "title" => $this->title,
                    "text" => $this->text,
                    "live" => $this->live,
                    "stringid" => $this->stringid
                    );

        return $a;

    }

}
