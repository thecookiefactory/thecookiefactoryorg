<?php

if (!isset($r_c)) header("Location: /notfound.php");

require_once str_repeat("../", $r_c) . "inc/classes/dtime.class.php";
require_once str_repeat("../", $r_c) . "inc/classes/forumthread.class.php";
require_once str_repeat("../", $r_c) . "inc/classes/game.class.php";
require_once str_repeat("../", $r_c) . "inc/classes/picture.class.php";
require_once str_repeat("../", $r_c) . "inc/classes/user.class.php";

use Aws\S3\S3Client;

$S3C = S3Client::factory(array(
    "key"    => $config["s3"]["key"],
    "secret" => $config["s3"]["secret"]
));

/**
 * map class
 *
 * function __construct
 *
 * function returnArray
 *
 * function getPictures
 *
 * function getName
 */
class map extends master {

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

                $squery = $con->prepare("SELECT *, BIN(`maps`.`comments`) FROM `maps` WHERE `maps`.`id` = :id");
                $squery->bindValue("id", $id, PDO::PARAM_INT);
                $squery->execute();

            } catch (PDOException $e) {

                die("An error occured while trying to fetch data to the class.");

            }

            if ($squery->rowCount() == 1) {

                $srow = $squery->fetch();

                $this->id               = $srow["id"];
                $this->name             = $srow["name"];
                $this->text             = $srow["text"];
                $this->author           = new user($srow["authorid"]);
                $this->date             = new dtime($srow["date"]);
                $this->editdate         = ($srow["editdate"] != null) ? new dtime($srow["editdate"]) : null;
                $this->dl               = $srow["dl"];
                $this->comments         = (int) $srow["BIN(`maps`.`comments`)"];
                $this->game             = new game($srow["gameid"]);
                $this->link             = $srow["link"];
                $this->downloadcount    = $srow["downloadcount"];

            } else {

                echo "Could not find a map with the given id.";

            }

        }

    }

    public function returnArray() {

        global $con;
        global $config;
        global $S3C;

        $a = array(
                    "id" => $this->id,
                    "name" => $this->name,
                    "text" => $this->text,
                    "author" => $this->author->getName(),
                    "editdate" => $this->editdate->display(),
                    "comments" => $this->comments,
                    "game" => array("name" => $this->game->getName(), "steamid" => $this->game->getSteamId()),
                    "link" => $this->link,
                    "downloadcount" => $this->downloadcount,
                    "picturecount" => count($this->getPictures()),
                    "pictures" => array()
                    );

        if ($this->comments) {

            try {

                $selectThreadId = $con->prepare("SELECT `forumthreads`.`id` FROM `forumthreads` WHERE `forumthreads`.`mapid` = :id");
                $selectThreadId->bindValue("id", $this->id, PDO::PARAM_INT);
                $selectThreadId->execute();
                $threadData = $selectThreadId->fetch();

                $thread = new forumthread($threadData["id"]);

                $a["thread"] = array("id" => $thread->getId(), "replycount" => $thread->replyCount());

            } catch (PDOException $e) {

                echo "Failed to fetch the related forum thread.";

            }

        }

        foreach ($this->getPictures() as $picture) {

            try {

                // get S3 url
                $url = $S3C->getObjectUrl($config["s3"]["bucket"], $picture->getFileName());

            } catch (Exception $e) {

                die("Could not get the picture url from S3.");

            }

            $a["pictures"][] = array("url" => $url, "text" => $picture->getText());

        }

        return $a;

    }

    protected function getPictures() {

        global $con;

        $pictures = array();

        try {

            $selectPictures = $con->prepare("SELECT `pictures`.`id` FROM `pictures` WHERE `pictures`.`mapid` = :id ORDER BY `pictures`.`ordernumber` ASC");
            $selectPictures->bindValue("id", $this->id, PDO::PARAM_INT);
            $selectPictures->execute();

        } catch (PDOException $e) {

            echo "An error occured while trying to fetch the pictures. ";

        }

        if ($selectPictures->rowCount() != 0) {

            while ($foundPicture = $selectPictures->fetch()) {

                $pictures[] = new picture($foundPicture["id"]);

            }

        }

        return $pictures;

    }

    public function getName() {

        return $this->name;

    }

}
