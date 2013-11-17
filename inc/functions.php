<?php

if (!isset($r_c)) header("Location: /notfound.php");

require_once str_repeat("../", $r_c) . "inc/config.php";

$con = new PDO("mysql:host=" . $config["db"]["host"] . ";dbname=" . $config["db"]["dbname"] . ";charset=utf8", $config["db"]["username"], $config["db"]["password"]);
$con->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
$con->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function strip($x) {

    global $con;

    $x = trim($x);
    $x = htmlspecialchars($x, ENT_QUOTES, "UTF-8");
    return $x;

}

function tformat($x) {

    return nl2br($x, false);

}

function vf($x) {

    return (strip($x) != "" && strip($x) != null);

}

function cookieh() {

    return str_shuffle(hash("sha256", microtime()));

}
