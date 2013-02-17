<?php

checkembed($r_c);
include "analyticstracking.php";

include "markdown/markdown.php";

$_SESSION["lp"] = "maps";

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

while ($r = mysqli_fetch_assoc($q)) {

    $gq = mysqli_query($con, "SELECT * FROM `gallery` WHERE `mapid`=".$r["id"]);
    ?>
    
    <div class='map-name'><?php echo $r["name"]; ?></div>
    <div class='map-container'>
      <div class='map-leftarrow map-arrow-disabled' id='map-<?php echo $r["id"]; ?>-left' onclick='startImagerollScrolling(this.id, -1);'></div>
      <div class='map-rightarrow map-arrow-disabled' id='map-<?php echo $r["id"]; ?>-right' onclick='startImagerollScrolling(this.id, 1);'></div>
      <div class='map-imageroll' id='map-<?php echo $r["id"]; ?>' onload='initialize(this.id);'>
      <script type='text/javascript'> lendict[    "map-<?php echo $r["id"]; ?>    "] = <?php echo (mysqli_num_rows($gq)+1); ?>; initialize(    "map-<?php echo $r["id"]; ?>    ");</script>
    
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
            
            <a target='_blank' href='http://steamcommunity.com/app/<?php echo $gr["steam"]; ?>'><?php echo $gr["name"]; ?></a>
        </span>
        <span class='map-desc'><?php echo nl2br($r["desc"], false); ?></span>
        <span class='map-dl'>
        
        <?php
        switch ($r["dltype"]) {
            case 0: ?><a href='<?php echo $r["dl"]; ?>' target='_blank'>DOWNLOAD</a><?php break;
            case 1: ?><a href='http://steamcommunity.com/sharedfiles/filedetails/?id=<?php echo $r["dl"]; ?>' target='_blank'>DOWNLOAD</a><?php break;
            case 2: ?>No dowload available yet.<?php break;
        }
        ?>
        
        </span>
      </div>

      <?php
      //comments
      $cq = mysqli_query($con, "SELECT * FROM `mapscomments` WHERE `mapid`=".$r["id"]) or die(mysqli_error());
      ?>
      
      <div class='comments'>
      
      <?php
        if (mysqli_num_rows($cq) > 0) {
          while ($cr = mysqli_fetch_assoc($cq)) {
            ?>
            
            <?php echo getname($cr["authorid"]); ?> said on <?php echo displaydate($cr["dt"]); ?>: <p><?php echo nl2br($cr["text"], false); ?></p>
            
            <?php
          }
        } else {
          ?>
          no comments yet
          <?php
        }
      if (checkuser()) {
        ?>
        
        <form action='?p=maps' method='post'>
        <textarea name='text' required></textarea>
        <input type='hidden' name='m_id' value='<?php echo $r["id"]; ?>'>
        <input type='submit' name='submit'>
        </form>
      
        <?php
      } else {
        ?>
        you have to be logged in to post comments
        <?php
      }
      ?>
      </div>
    </div>
<?php
}
