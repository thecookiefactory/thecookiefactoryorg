<?php

$r_c = 0;

require_once "../inc/functions.php";

if (!in_array($_SERVER['REMOTE_ADDR'], $config["updater_ip_whitelist"])) {
    fwrite(STDOUT, "255");
    die("255");
}

exec($config["python"]["updater"], $output, $return);
fwrite(STDOUT, $output);
fwrite(STDOUT, $return);
