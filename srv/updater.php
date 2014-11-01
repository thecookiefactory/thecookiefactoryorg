<?php

$r_c = 0;

require_once "../inc/functions.php";
/*
if (!in_array($_SERVER['REMOTE_ADDR'], $config["updater_ip_whitelist"])) {
    error_log("255");
    die("255");
}
*/
exec($config["python"]["updater"], $output, $return);
error_log($output);
error_log($return);

echo 'output: <pre>'; print_r($output); echo '</pre>';
echo 'return: <pre>'; print_r($return); echo '</pre>';
