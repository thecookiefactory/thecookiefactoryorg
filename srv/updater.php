<?php

$r_c = 0;

require_once "../inc/functions.php";

if (!in_array($_SERVER['REMOTE_ADDR'], $config["updater_ip_whitelist"])) {
    die("255");
}

exec($config["python"]["updater"], $output, $return);
if ($output) error_log($output);

print_r($output);
print_r($return);
