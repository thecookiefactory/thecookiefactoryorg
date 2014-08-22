<?php

session_start();

$r_c = 1;
require_once '../inc/functions.php';
require_once '../classes/custompage.class.php';
require_once '../classes/user.class.php';

$user = new user((isset($_SESSION['userid']) ? $_SESSION['userid'] : null));

if (!$user->isAdmin()) die('403');

$twig = twigInit();

if (isset($_POST['text'])) {

    if (isset($_POST['live']) && $_POST['live'] == 'on') {

        $live = 1;

    } else {

        $live = 0;

    }

    $page = new custompage($_POST['stringid']);
    $page->update($_POST['title'], $_POST['text'], $live, $_POST['stringid']);

}

if (isset($_POST['create'])) {

    $page = new custompage();
    $page->create($_POST['stringid']);

}

if (isset($_POST['selectedpage'])) {

    $page = new custompage($_POST['selectedpage']);

    $data = $page->returnArray();

    $mode = 'update';

} else {

    try {

        $selectPages = $con->query('
            SELECT `custompages`.`stringid`
            FROM `custompages`
            ORDER BY `custompages`.`title` ASC
        ');

        $stringids = array();

        while ($pageData = $selectPages->fetch()) {

            $stringids[] = $pageData['stringid'];

        }

        $mode = 'select';

    } catch (PDOException $e) {

        die('Failed to fetch pages.');

    }

}

echo $twig->render('admin/custompages.html', array('mode' => $mode, 'stringids' => (isset($stringids) ? $stringids : 0), 'data' => (isset($data) ? $data : 0)));
