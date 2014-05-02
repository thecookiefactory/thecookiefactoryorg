<?php

exec($config["python"]["github"], $output, $return);
if ($output) error_log($output);

echo "gaithub: ";
print_r($output);

exec($config["python"]["twitch"], $output, $return);
if ($output) error_log($output);

echo "twtich: ";
print_r($output);
