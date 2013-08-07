<?php

if (!isset($r_c)) header("Location: notfound.php");

include "analyticstracking.php";
include "markdown/markdown.php";

$_SESSION["lp"] = $p;

?>

<script src='js/maps.js'></script>

<?php

$q = $con->query("SELECT * FROM `maps` ORDER BY `maps`.`id` DESC");

if ($q->rowCount() != 0) {

    while ($r = $q->fetch()) {

        $gq = $con->prepare("SELECT * FROM `pictures` WHERE `pictures`.`mapid` = :id");
        $gq->bindValue("id", $r["id"], PDO::PARAM_INT);
        $gq->execute();
        ?>

        <div class='map-name'>
        <a name='<?php echo $r["id"]; ?>'></a><!-- #hashtag --><?php echo $r["name"]; ?></div>
        <div class='map-container'>
          <div class='map-leftarrow map-arrow-disabled' id='map-<?php echo $r["id"]; ?>-left' onclick='startImagerollScrolling(this.id, -1);'></div>
          <div class='map-rightarrow map-arrow-disabled' id='map-<?php echo $r["id"]; ?>-right' onclick='startImagerollScrolling(this.id, 1);'></div>
          <div class='map-actionbar' id='map-actionbar-<?php echo $r["id"]; ?>'>
            <span class='map-actionbar-button' id='map-moreinfo-<?php echo $r["id"]; ?>' onclick='animateDataPanel(this.id)'>More info</span>
              <?php if (!vf($r["link"])) { ?>
                <span class='map-actionbar-button-disabled'>Download</span>
              <?php } else { ?>
                <a href='<?php echo $r["link"]; ?>' target='_blank'><span class='map-actionbar-button'>Download</span></a>
              <?php } ?>
          </div>
          <div class='map-imageroll' id='map-<?php echo $r["id"]; ?>' onload='initialize(this.id);'>
          <script type='text/javascript'> lendict["map-<?php echo $r["id"]; ?>"] = <?php echo ($gq->rowCount()+1); ?>; initialize("map-<?php echo $r["id"]; ?>");</script>

        <?php

        //display the main image

            ?>

            <div class='map-image'>
              <img class='map-image' alt='<?php echo $r["name"]; ?>' src='img/maps/<?php echo $r["id"]; ?>.<?php echo $r["extension"]; ?>'>
            </div>

        <?php
        //display additional images
        while ($gr = $gq->fetch()) {
            ?>

            <div class='map-image'>
              <img class='map-image' src='img/maps/<?php echo $r["id"]; ?>/<?php echo $gr["filename"]; ?>' alt='<?php echo $gr["desc"]; ?>' title='<?php echo $gr["desc"]; ?>'>
            </div>

            <?php
        }

          ?>

          </div>
          <div class='map-data' id='map-data-<?php echo $r["id"]; ?>'>
            <div class='map-data-properties'>
              <span class='map-data-prop map-data-author'><?php echo getname($r["authorid"]); ?></span><br>
              <span class='map-data-prop map-data-game'>

                  <?php
                  $gq = $con->prepare("SELECT * FROM `games` WHERE `games`.`id` = :id");
                  $gq->bindValue("id", $r["gameid"], PDO::PARAM_INT);
                  $gq->execute();
                  $gr = $gq->fetch();
                  ?>

                  <a target='_blank' href='http://store.steampowered.com/app/<?php echo $gr["steamid"]; ?>'><?php echo $gr["name"]; ?></a>
              </span><br>
              <span class='map-data-prop map-data-date'><?php echo displaydate($r["editdate"]); ?></span>
              <?php
                if ($r["comments"] == 1) {
              ?>

                  <span class='map-data-prop map-data-topic'>
                    <?php
                      $cq = $con->prepare("SELECT `forumthreads`.`id` FROM `forumthreads` WHERE `forumthreads`.`mapid` = :id");
                      $cq->bindValue("id", $r["id"], PDO::PARAM_INT);
                      $cq->execute();
                      $ca = $cq->fetch();
                      echo "<a href='?p=forums&amp;id=".$ca["id"]."'># replies</a>";
                    ?>
                  </span>

              <?php
                }
              ?>
            </div>
            <div class='map-data-desc'>
              <?php echo tformat($r["text"]); ?>
            </div>
            <div class='map-data-actionbar'>
              <span class='map-actionbar-button' id='map-lessinfo-<?php echo $r["id"]; ?>' onclick='animateDataPanel(this.id)'>Less info</span>
                <?php if (!vf($r["link"])) { ?>
                  <span class='map-actionbar-button-disabled'>Download</span>
                <?php } else { ?>
                  <a href='<?php echo $r["link"]; ?>' target='_blank'><span class='map-actionbar-button'>Download</span></a>
                <?php } ?>
            </div>
          </div>
        </div>
    <?php
    }

} else {

    echo "The are no maps.";

}
