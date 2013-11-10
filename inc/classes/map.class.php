<?php

if (!isset($r_c)) header("Location: /notfound.php");

require_once str_repeat("../", $r_c) . "inc/classes/dtime.class.php";
require_once str_repeat("../", $r_c) . "inc/classes/game.class.php";
require_once str_repeat("../", $r_c) . "inc/classes/picture.class.php";
require_once str_repeat("../", $r_c) . "inc/classes/user.class.php";

/**
 * map class
 *
 */
class map {

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

                $squery = $con->prepare("SELECT * FROM `maps` WHERE `maps`.`id` = :id");
                $squery->bindValue("id", $id, PDO::PARAM_INT);
                $squery->execute();

            } catch (PDOException $e) {

                echo "An error occured while trying to fetch data to the class. (" . $e->getMessage() . ")";

            }

            if ($squery->rowCount() == 1) {

                $srow = $squery->fetch();

                $this->id               = $srow["id"];
                $this->name             = $srow["name"];
                $this->text             = $srow["text"];
                $this->author           = new user($srow["authorid"]);
                $this->date             = new dtime($srow["date"]);
                $this->editdate         = new dtime($srow["editdate"]);
                $this->dl               = $srow["dl"];
                $this->extension        = $srow["extension"];
                $this->comments         = $srow["comments"];
                $this->game             = new game($srow["gameid"]);
                $this->link             = $srow["link"];
                $this->downloadcount    = $srow["downloadcount"];

            } else {

                echo "Could not find a map with the given id.";

            }

        }

    }

    public function display() {

        ?>
        <div class='map-name' id='<?php echo $this->name; ?>'><?php echo $this->name; ?></div>
        <div class='map-container'>
          <div class='map-leftarrow map-arrow-disabled' id='map-<?php echo $this->id; ?>-left' onclick='startImagerollScrolling(this.id, -1);'></div>
          <div class='map-rightarrow map-arrow-disabled' id='map-<?php echo $this->id; ?>-right' onclick='startImagerollScrolling(this.id, 1);'></div>
          <div class='map-actionbar' id='map-actionbar-<?php echo $this->id; ?>'>
            <span class='map-actionbar-button' id='map-moreinfo-<?php echo $this->id; ?>' onclick='animateDataPanel(this.id)'>More info</span>
              <?php if ($this->link != null) { ?>
                <a href='<?php echo $this->link; ?>' target='_blank'><span class='map-actionbar-button'>Download</span></a>
              <?php } else { ?>
                <span class='map-actionbar-button-disabled'>Download</span>
              <?php } ?>
          </div>
          <?php $pictures = $this->getPictures(); ?>
          <div class='map-imageroll' id='map-<?php echo $this->id; ?>' onload='initialize(this.id);'>
            <script type='text/javascript'> lendict["map-<?php echo $this->id; ?>"] = <?php echo (count($pictures) + 1); ?>; initialize("map-<?php echo $this->id; ?>");</script>
            <div class='map-image'><img class='map-image' alt='<?php echo $this->name; ?>' src='/img/maps/<?php echo $this->id; ?>.<?php echo $this->extension; ?>'></div>
            <?php
            //display additional images
            foreach ($pictures as $picture) {
                ?>
                <div class='map-image'><img class='map-image' src='/img/maps/<?php echo $this->id; ?>/<?php echo $picture->getFilename(); ?>' alt='<?php echo $picture->getText(); ?>' title='<?php echo $picture->getText(); ?>'></div>
                <?php
            }

            ?>
          </div>
          <div class='map-data' id='map-data-<?php echo $this->id; ?>'>
            <div class='map-data-properties'>
              <span class='map-data-prop map-data-author'><?php echo $this->author->getName(); ?></span>
              <span class='map-data-prop map-data-game'>
                  <?php
                  if ($this->game->isSteamGame()) {

                      echo "<a target='_blank' href='http://store.steampowered.com/app/" . $this->game->getSteamId() . "'>" . $this->game->getName() . "</a>";

                  } else {

                      echo $this->game->getName();

                  }
                  ?>
              </span>
              <span class='map-data-prop map-data-dlcount'><?php echo $this->downloadcount . " times"; ?></span>
              <span class='map-data-prop map-data-date'><?php echo $this->editdate->display(); ?></span>
              <?php
                if ($this->comments == 1) {
              ?>

                  <span class='map-data-prop map-data-topic'>
                    <?php
                      $cq = $con->prepare("SELECT `forumthreads`.`id` FROM `forumthreads` WHERE `forumthreads`.`mapid` = :id");
                      $cq->bindValue("id", $this->id, PDO::PARAM_INT);
                      $cq->execute();
                      $ca = $cq->fetch();

                      $rq = $con->prepare("SELECT `forumposts`.`id` FROM `forumposts` WHERE `forumposts`.`threadid` = :id");
                      $rq->bindValue("id", $ca["id"], PDO::PARAM_INT);
                      $rq->execute();
                      $nr = $rq->rowCount();
                      echo "<a href='/forums/".$ca["id"]."'>".$nr." replies</a>";
                    ?>
                  </span>

              <?php
                }
              ?>
            </div>
            <div class='map-data-desc'>
              <?php echo tformat($this->text); ?>
            </div>
            <div class='map-data-actionbar'>
              <span class='map-actionbar-button' id='map-lessinfo-<?php echo $this->id; ?>' onclick='animateDataPanel(this.id)'>Less info</span>
                <?php if ($this->link != null) { ?>
                  <a href='<?php echo $this->link; ?>' target='_blank'><span class='map-actionbar-button'>Download</span></a>
                <?php } else { ?>
                  <span class='map-actionbar-button-disabled'>Download</span>
                <?php } ?>
            </div>
          </div>
        </div>
        <?php

    }

    protected function getPictures() {

        global $con;

        $pictures = Array();

        try {

            $squery = $con->prepare("SELECT `pictures`.`id` FROM `pictures` WHERE `pictures`.`mapid` = :id");
            $squery->bindValue("id", $this->id, PDO::PARAM_INT);
            $squery->execute();

        } catch (PDOException $e) {

            echo "An error occured while trying to fetch the pictures. (" . $e->getMessage() . ")";

        }

        if ($squery->rowCount() != 0) {

            while ($srow = $squery->fetch()) {

                $pictures[] = new picture($srow["id"]);

            }

        }

        return $pictures;

    }

}

?>
