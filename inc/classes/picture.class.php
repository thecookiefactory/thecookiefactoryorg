<?php

if (!isset($r_c)) header("Location: /notfound.php");

require_once str_repeat("../", $r_c) . "inc/classes/dtime.class.php";

use Aws\S3\S3Client;

$S3C = S3Client::factory(array(
    "key"    => $config["s3"]["key"],
    "secret" => $config["s3"]["secret"]
));

/**
 * picture class
 *
 * function __construct
 *
 * function getText
 *
 * function getFileName
 */
class picture extends master {

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

                die("An error occured while trying to fetch data to the class.");

            }

            if ($squery->rowCount() == 1) {

                $srow = $squery->fetch();

                $this->id       = $srow["id"];
                $this->text     = $srow["text"];
                $this->date     = new dtime($srow["date"]);
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

    public function getUrl() {

        global $config;
        global $S3C;

        try {

            return $S3C->getObjectUrl($config["s3"]["bucket"], $this->getFileName(), "+10 minutes");

        } catch (Exception $e) {

            die("Could not get the picture url from S3.");

        }

    }

    public function delete() {

        global $con;

        try {

            $S3C->deleteObject(array("Bucket"     => $config["s3"]["bucket"],
                                     "Key"        => $row["filename"]
                                    ));

            $dq = $con->prepare("DELETE FROM `pictures` WHERE `pictures`.`id` = :id");
            $dq->bindValue("id", $this->id, PDO::PARAM_INT);
            $dq->execute();

            return true;

        } catch (Exception $e) {

            return false;

        }

    }

}
