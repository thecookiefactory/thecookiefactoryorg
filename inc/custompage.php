<?php

if (!isset($r_c)) header('Location: /notfound.php');

require_once 'classes/custompage.class.php';

$_SESSION['lp'] = $p;

$page = new custompage(strip($_GET['p']));

$text = $page->returnArray()['text'];

echo $twig->render('custompage.html', array('index_var' => $index_var, 'text' => $text));
