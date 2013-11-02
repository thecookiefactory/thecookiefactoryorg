<?php

if (!isset($r_c)) header("Location: /notfound.php");

include_once "analyticstracking.php";
require_once "markdown/markdown.php";

$_SESSION["lp"] = $p;

$q = $con->query("SELECT `streams`.`id`,`streams`.`authorid`,`streams`.`twitchname` FROM `streams`");

?>

<ul class='stream-menu'>

<?php

if ($q->rowCount() != 0) {

    while ($r = $q->fetch()) {

        ?>

        <a href='/streams/<?php echo getname($r["authorid"]); ?>'>

        <?php

        if (isset($_GET["id"]) && getname($r["authorid"]) == $_GET["id"]) {

        ?>
            <li class='stream-button stream-button-selected

            <?php

            if (islive($r["twitchname"])) {

                ?>

                stream-live

                <?php

            }

        }

        else {

            ?>

            <li class='stream-button

            <?php

            if (islive($r["twitchname"])) {

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

if (isset($_GET["id"])) {

    // DISPLAY A STREAM
    $id = strip($_GET["id"]);

    $sq = $con->prepare("SELECT `users`.`id` FROM `users` WHERE `users`.`name` = :name");
    $sq->bindValue("name", $id, PDO::PARAM_STR);
    $sq->execute();

    if ($sq->rowCount() == 1) {

        $sr = $sq->fetch();

        $id = $sr["id"];

    }

    $q = $con->prepare("SELECT `streams`.`twitchname`,`streams`.`text` FROM `streams` WHERE `streams`.`authorid` = :id");
    $q->bindValue("id", $id, PDO::PARAM_INT);
    $q->execute();

    if ($q->rowCount() == 1) {

        $r = $q->fetch();

        if (islive($r["twitchname"])) {

            ?>

            <div class='stream-title'><h1><?php echo $r["title"]; ?></h1></div>

            <?php

        }

        ?>

        <div class='stream-content'>
            <div class='stream-player'>

                <?php echo streamo($r["twitchname"]); ?>

            </div>
            <div class='stream-description'>

                <?php echo Markdown($r["text"]); ?>

            </div>
        </div>

        <?php

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
