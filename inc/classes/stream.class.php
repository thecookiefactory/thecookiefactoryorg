<?php

if (!isset($r_c)) header("Location: /notfound.php");

require_once str_repeat("../", $r_c) . "inc/classes/user.class.php";

/**
 * stream class
 *
 */
class stream {

    /**
     * variables
     *
     * @var $id int
     * @var $title string
     * @var $text string
     * @var $author object
     */
    protected $id       = null;
    protected $title    = null;
    protected $text     = null;
    protected $author   = null;

    public function __construct($id = null, $field = null) {

        global $con;

        if ($id != null) {

            if ($field == "author") {

                try {

                    $squery = $con->prepare("SELECT * FROM `streams` WHERE `streams`.`authorid` = :id");
                    $squery->bindValue("id", $id, PDO::PARAM_INT);
                    $squery->execute();

                } catch (PDOException $e) {

                    echo "An error occured while trying to fetch data to the class. (" . $e->getMessage() . ")";

                }

            } else {

                try {

                    $squery = $con->prepare("SELECT * FROM `streams` WHERE `streams`.`id` = :id");
                    $squery->bindValue("id", $id, PDO::PARAM_INT);
                    $squery->execute();

                } catch (PDOException $e) {

                    echo "An error occured while trying to fetch data to the class. (" . $e->getMessage() . ")";

                }

            }

            if ($squery->rowCount() == 1) {

                $srow = $squery->fetch();

                $this->id       = $srow["id"];
                $this->title    = $srow["title"];
                $this->text     = $srow["text"];
                $this->author   = new user($srow["authorid"]);

            } else {

                echo "Could not find a stream with the given id.";

            }

        }

    }

    public function display() {

        if ($this->isLive()) {

            ?>

            <div class='stream-title'><h1><?php echo $this->title; ?></h1></div>

            <?php

        }

        ?>

        <div class='stream-content'>
            <div class='stream-player'>

                <?php echo $this->getEmbedCode(); ?>

            </div>
            <div class='stream-description'>

                <?php echo Markdown($this->text); ?>

            </div>
        </div>

        <?php

    }

    public function button() {

        echo "<a href='/streams/" . $this->author->getName() . "'>";

        if (isset($_GET["id"]) && $this->author->getName() == $_GET["id"]) {

            ?>
            <li class='stream-button stream-button-selected
            <?php

        } else {

            ?>
            <li class='stream-button
            <?php

        }

        if ($this->isLive()) {
            ?>
            stream-live
            <?php
        }

        ?>

        '><?php echo $this->author->getName(); ?></li>
        </a>

        <?php

    }

    public function exists() {

        return ($this->id != null);

    }

    public function isLive() {

        return ($this->title != null);

    }

    protected function getEmbedCode() {

        return "<object type='application/x-shockwave-flash' height='378' width='620' id='live_embed_player_flash' data='http://www.twitch.tv/widgets/live_embed_player.swf?channel=" . $this->author->getTwitchName() . "' bgcolor='#000000'>
        <param name='allowFullScreen' value='true' />
        <param name='allowScriptAccess' value='always' />
        <param name='allowNetworking' value='all' />
        <param name='movie' value='http://www.twitch.tv/widgets/live_embed_player.swf' />
        <param name='flashvars' value='hostname=www.twitch.tv&channel=" . $this->author->getTwitchName() . "&auto_play=true&start_volume=25' />
        </object>";

    }

}

?>
