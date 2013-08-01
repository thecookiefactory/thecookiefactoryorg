<?php

if (!isset($r_c)) header("Location: notfound.php");

include "analyticstracking.php";
include "markdown/markdown.php";

$_SESSION["lp"] = $p;

$q = $con->query("SELECT `streams`.`id`,`streams`.`authorid`,`streams`.`twitchname`,`streams`.`active` FROM `streams` WHERE `streams`.`active` = 1");

?>

<ul class='stream-menu'>

<?php

if ($q->rowCount() != 0) {

    while ($r = $q->fetch(PDO::FETCH_ASSOC)) {

        ?>

        <a href='?p=streams&amp;id=<?php echo $r["id"]; ?>'>

        <?php

        if (isset($_GET["id"]) && $r["id"] == $_GET["id"]) {

        ?>
            <li class='stream-button stream-button-selected

            <?php

            if (islive($r["twitch"])) {

                ?>

                stream-live

                <?php

            }

        }

        else {

            ?>

            <li class='stream-button

            <?php

            if (islive($r["twitch"])) {

                ?>

                stream-live

                <?php

            }

        }

        ?>

        '><?php echo getname($r["authorid"]); ?></li>
        </a>

        <?php

    }

} else {

    echo "There are no active streams.";

}

?>

</ul>

<?php

if (isset($_GET["id"]) && is_numeric($_GET["id"])) {

    // DISPLAY A STREAM
    $id = strip($_GET["id"]);

    $q = $con->prepare("SELECT `streams`.`twitch`,`streams`.`description` FROM `streams` WHERE `streams`.`id` = :id");
    $q->bindValue("id", $id, PDO::PARAM_INT);
    $q->execute();

    if ($q->rowCount() == 1) {

        $r = $q->fetch(PDO::FETCH_ASSOC);

        if (islive($r["twitch"])) {

            ?>

            <div class='stream-title'><h1><?php echo gettitle($r["twitch"]); ?></h1></div>

            <?php

        }

        ?>

        <div class='stream-content'>
            <div class='stream-player'>

                <?php echo streamo($r["twitch"]); ?>

            </div>
            <div class='stream-description'>

                <?php echo Markdown($r["description"]); ?>

            </div>
        </div>

        <?php

    } else {

        //header("Location: ?p=streams");
        // simply not echoing out anything should be fine

    }

}

function streamo($x) {

    return "<object type='application/x-shockwave-flash' height='378' width='620' id='live_embed_player_flash' data='http://www.twitch.tv/widgets/live_embed_player.swf?channel=".$x."' bgcolor='#000000'>
    <param name='allowFullScreen' value='true' />
    <param name='allowScriptAccess' value='always' />
    <param name='allowNetworking' value='all' />
    <param name='movie' value='http://www.twitch.tv/widgets/live_embed_player.swf' />
    <param name='flashvars' value='hostname=www.twitch.tv&channel=".$x."&auto_play=true&start_volume=25' />
    </object>";

}

function gettitle($x) {

    $json_file = @file_get_contents("http://api.justin.tv/api/stream/list.json?channel=$x", 0, null, null);
    $json_array = json_decode($json_file, true);

    if (empty($json_array)) {

        return "";

    }

    return $json_array[0]['channel']['status'];

}
