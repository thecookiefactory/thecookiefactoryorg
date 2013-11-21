<?php

if (!isset($r_c)) header("Location: /notfound.php");

include_once "analyticstracking.php";
require_once "classes/stream.class.php";
require_once "inc/markdown/markdown.php";

$_SESSION["lp"] = $p;

$selectStreams = $con->query("SELECT `streams`.`id` FROM `streams`");

?>

<ul class='stream-menu'>

<?php

if ($selectStreams->rowCount() != 0) {

    while ($foundStream = $selectStreams->fetch()) {

        $stream = new stream($foundStream["id"]);

        $stream->button();

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

    $streamer = new user($id, "name");

    if ($streamer->isReal()) {

        $stream = new stream($streamer->getId(), "author");

        if ($stream->isReal()) {

            $stream->display();

        }

    }

}
