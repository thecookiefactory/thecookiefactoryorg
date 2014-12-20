<?php

$r_c = 0;

require_once '../inc/functions.php';

if ($_POST['access_key'] == $config['updaterkey']) {
    exec($config['python']['updater'], $output, $return);
    print_r($output);
} else {
    echo 'You shall not pass!';
}
