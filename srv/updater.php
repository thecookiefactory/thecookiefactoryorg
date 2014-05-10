<?php

$r_c = 0;

require_once "../inc/functions.php";

exec($config["python"]["updater"], $output, $return);
if ($output) error_log($output);

print_r($output);
print_r($return);
