<?php

exec($config["python"]["github"], $output, $return);
if ($return) error_log($return);

echo "gaithub: ";
print_r($return);

exec($config["python"]["twitch"], $output, $return);
if ($return) error_log($return);

echo "twtich: ";
print_r($return);
