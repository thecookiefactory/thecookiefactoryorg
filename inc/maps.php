<?php

if (!isset($r_c)) header("Location: /notfound.php");

include_once "analyticstracking.php";
require_once "markdown/markdown.php";

$_SESSION["lp"] = $p;

?>

<script src='/js/maps.js'></script>

<?php

$q = $con->query("SELECT `maps`.`id`, `maps`.`name`, `maps`.`text`, `maps`.`authorid`, `maps`.`date`, `maps`.`extension`, BIN(`maps`.`comments`), `maps`.`gameid`, `maps`.`link`, `maps`.`editdate`
                  FROM `maps`
                  ORDER BY `maps`.`id` DESC");

if ($q->rowCount() != 0) {

    $iii = 0;

    while ($r = $q->fetch()) {

        $iii++;

        $gq = $con->prepare("SELECT * FROM `pictures` WHERE `pictures`.`mapid` = :id");
        $gq->bindValue("id", $r["id"], PDO::PARAM_INT);
        $gq->execute();
        ?>

        <div class='map-name' id='<?php echo $r["name"]; ?>'><!-- #hashtag --><?php echo $r["name"]; ?></div>
        <div class='map-container'>
          <div class='map-leftarrow map-arrow-disabled' id='map-<?php echo $r["id"]; ?>-left' onclick='startImagerollScrolling(this.id, -1);'></div>
          <div class='map-rightarrow map-arrow-disabled' id='map-<?php echo $r["id"]; ?>-right' onclick='startImagerollScrolling(this.id, 1);'></div>
          <div class='map-actionbar' id='map-actionbar-<?php echo $r["id"]; ?>'>
            <span class='map-actionbar-button' id='map-moreinfo-<?php echo $r["id"]; ?>' onclick='animateDataPanel(this.id)'>More info</span>
              <?php if (!vf($r["link"])) { ?>
                <span class='map-actionbar-button-disabled'>Download</span>
              <?php } else { ?>
                <a href='/click.php?id=<?php echo $r["id"]; ?>' target='_blank'><span class='map-actionbar-button'>Download</span></a>
              <?php } ?>
          </div>
          <div class='map-imageroll' id='map-<?php echo $r["id"]; ?>' onload='initialize(this.id);'>
          <script type='text/javascript'> lendict["map-<?php echo $r["id"]; ?>"] = <?php echo ($gq->rowCount()+1); ?>; initialize("map-<?php echo $r["id"]; ?>");</script>

        <?php

        //display the main image

            ?><div class='map-image'><img class='map-image' alt='<?php echo $r["name"]; ?>' src='/img/maps/<?php echo $r["id"]; ?>.<?php echo $r["extension"]; ?>'></div><?php
        //display additional images
        while ($gr = $gq->fetch()) {
            ?><div class='map-image'><img class='map-image' src='/img/maps/<?php echo $r["id"]; ?>/<?php echo $gr["filename"]; ?>' alt='<?php echo $gr["text"]; ?>' title='<?php echo $gr["text"]; ?>'></div><?php
        }

          ?></div>
          <div class='map-data' id='map-data-<?php echo $r["id"]; ?>'>
            <div class='map-data-properties'>
              <span class='map-data-prop map-data-author'><?php echo getname($r["authorid"]); ?></span><br>
              <span class='map-data-prop map-data-game'>

                  <?php
                  $gq = $con->prepare("SELECT * FROM `games` WHERE `games`.`id` = :id");
                  $gq->bindValue("id", $r["gameid"], PDO::PARAM_INT);
                  $gq->execute();
                  $gr = $gq->fetch();

                  if (!vf($gr["steamid"]) || $gr["steamid"] == 0) {

                      echo $gr["name"];

                  } else {

                      ?>
                      <a target='_blank' href='http://store.steampowered.com/app/<?php echo $gr["steamid"]; ?>'><?php echo $gr["name"]; ?></a>
                      <?php

                  }
                  ?>


              </span><br>
              <span class='map-data-prop map-data-date'><?php echo displaydate($r["editdate"]); ?></span>
              <?php
                if ($r["BIN(`maps`.`comments`)"] == 1) {
              ?>

                  <span class='map-data-prop map-data-topic'>
                    <?php
                      $cq = $con->prepare("SELECT `forumthreads`.`id` FROM `forumthreads` WHERE `forumthreads`.`mapid` = :id");
                      $cq->bindValue("id", $r["id"], PDO::PARAM_INT);
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
              <?php echo tformat($r["text"]); ?>
            </div>
            <div class='map-data-actionbar'>
              <span class='map-actionbar-button' id='map-lessinfo-<?php echo $r["id"]; ?>' onclick='animateDataPanel(this.id)'>Less info</span>
                <?php if (!vf($r["link"])) { ?>
                  <span class='map-actionbar-button-disabled'>Download</span>
                <?php } else { ?>
                  <a href='/click.php?id=<?php echo $r["id"]; ?>' target='_blank'><span class='map-actionbar-button'>Download</span></a>
                <?php } ?>
            </div>
          </div>
        </div>
        <?php
        if ($iii == 1) {

            ?>
            <div class='map-ad'>
                  <script async src='//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js'></script>
                  <!-- Maps Inline -->
                  <ins class='adsbygoogle'
                       style='display:inline-block;width:728px;height:90px'
                       data-ad-client='ca-pub-8578399795841431'
                       data-ad-slot='8918199475'></ins>
                  <script>
                  (adsbygoogle = window.adsbygoogle || []).push({});
                  </script>
            </div>
            <?php

        }
    }

} else {

    echo "The are no maps.";

}
