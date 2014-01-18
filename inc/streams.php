<?php

if (!isset($r_c)) header("Location: /notfound.php");

include_once "analyticstracking.php";
require_once "classes/stream.class.php";

$streams = array();

$_SESSION["lp"] = $p;

try {

    $selectStreams = $con->query("SELECT `streams`.`id` FROM `streams`");

} catch (PDOException $e) {

    die("Failed to fetch streams.");

}

while ($foundStream = $selectStreams->fetch()) {

    $stream = new stream($foundStream["id"]);

    $streams[$foundStream["id"]] = $stream->returnArray();

}

$selectedId = 0;

if (isset($_GET["id"])) {

    $id = strip($_GET["id"]);

    $streamer = new user($id, "name");

    if ($streamer->isReal()) {

        $stream = new stream($streamer->getId(), "author");

        if ($stream->isReal()) {

            $selectedId = $stream->getId();

        }

    }

}

echo $twig->render("streams.html", array("streams" => $streams, "selectedid" => $selectedId));
