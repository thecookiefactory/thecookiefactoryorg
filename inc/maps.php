<?php

if (!isset($r_c)) header("Location: notfound.php");

include "analyticstracking.php";
include "markdown/markdown.php";

$_SESSION["lp"] = $p;

if (isset($_POST["submit"]) && strip($_POST["text"]) != "") {

    $text = strip($_POST["text"]);
    $m_id = strip($_POST["m_id"]);
    $userid = $_SESSION["userid"];
    $dt = time();

    mysqli_query($con, "INSERT INTO `mapscomments` VALUES('','".$userid."','".$text."','".$dt."','".$m_id."')");

}

?>

<script src='js/maps.js'></script>

<?php

$q = mysqli_query($con, "SELECT * FROM `maps` ORDER BY `id` DESC");

if (mysqli_num_rows($q) != 0) {

    while ($r = mysqli_fetch_assoc($q)) {

        $gq = mysqli_query($con, "SELECT * FROM `gallery` WHERE `mapid`=".$r["id"]);
        ?>

        <div class='map-name'>
        <a name='<?php echo $r["id"]; ?>'></a><!-- #hashtag --><?php echo $r["name"]; ?></div>
        <div class='map-container'>
          <div class='map-leftarrow map-arrow-disabled' id='map-<?php echo $r["id"]; ?>-left' onclick='startImagerollScrolling(this.id, -1);'></div>
          <div class='map-rightarrow map-arrow-disabled' id='map-<?php echo $r["id"]; ?>-right' onclick='startImagerollScrolling(this.id, 1);'></div>
          <div class="map-actionbar">
            <span class="map-actionbar-button">More info</span>

              <?php
                switch ($r["dltype"]) {

                  case 0: ?><a href='<?php echo $r["dl"]; ?>' target='_blank'><span class="map-actionbar-button">Download</span></a><?php break;
                  case 1: ?><a href='http://steamcommunity.com/sharedfiles/filedetails/?id=<?php echo $r["dl"]; ?>' target='_blank'><span class="map-actionbar-button">Download</span></a><?php break;
                  case 2: ?><span class='map-actionbar-button-disabled'>Download</span><?php break;

                }
              ?>

          </div>
          <div class='map-imageroll' id='map-<?php echo $r["id"]; ?>' onload='initialize(this.id);'>
          <script type='text/javascript'> lendict["map-<?php echo $r["id"]; ?>"] = <?php echo (mysqli_num_rows($gq)+1); ?>; initialize("map-<?php echo $r["id"]; ?>");</script>

        <?php

        //display the main image

            ?>

            <div class='map-image'>
              <img class='map-image' alt='<?php echo $r["name"]; ?>' src='img/maps/<?php echo $r["id"]; ?>.<?php echo $r["ext"]; ?>'>
            </div>

        <?php
        //display additional images
        while ($gr = mysqli_fetch_assoc($gq)) {
            ?>

            <div class='map-image'>
              <img class='map-image' src='img/maps/<?php echo $r["id"]; ?>/<?php echo $gr["filename"]; ?>' alt='<?php echo $gr["desc"]; ?>' title='<?php echo $gr["desc"]; ?>'>
            </div>

            <?php
        }

          ?>

          </div>
          <div class='map-data'>
            <span class='map-author'><?php echo getname($r["authorid"]); ?></span>
            <span class='map-game'>

                <?php
                $gq = mysqli_query($con, "SELECT * FROM `games` WHERE `id`=".$r["gameid"]);
                $gr = mysqli_fetch_assoc($gq);
                ?>

                <a target='_blank' href='http://store.steampowered.com/app/<?php echo $gr["steam"]; ?>'><?php echo $gr["name"]; ?></a>
            </span>
            <span class='map-desc'><?php echo tformat($r["desc"]); ?></span>
          </div>


          <?php
          if ($r["comments"] == 1) {
          ?>
          <div class='comments'>

          <?php
            $cq = mysqli_query($con, "SELECT `id` FROM `forums` WHERE `mapid`=".$r["id"]);
            $ca = mysqli_fetch_assoc($cq);
            echo "<a href='?p=forums&amp;id=".$ca["id"]."'>Link to the related forum topic if</a>";
          ?>
          </div>

          <?php
          }
          ?>
        </div>
    <?php
    }

} else {

    echo "The are no maps.";

}