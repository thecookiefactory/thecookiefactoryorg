<?php

exec($config["python"]["github"], $output, $return);
if ($return) error_log($return);

exec($config["python"]["twitch"], $output, $return);
if ($return) error_log($return);
