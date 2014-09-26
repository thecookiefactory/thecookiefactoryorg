<?php

session_start();

$r_c = 1;
require_once '../inc/functions.php';
require_once '../classes/forumcategory.class.php';
require_once '../classes/user.class.php';

$user = new user((isset($_SESSION['userid']) ? $_SESSION['userid'] : null));

if (!$user->isAdmin()) die('403');

$twig = twigInit();

try {

    $selectCategories = $con->prepare('
        SELECT `forumcategories`.`id`
        FROM `forumcategories`
    ');

} catch (PDOException $e) {

    die('Failed to select categories.');

}

$selectCategories->execute();

if (isset($_POST['update'])) {

    while ($categoryData = $selectCategories->fetch()) {

        $id = $categoryData['id'];

        $category = new forumcategory($id);

        $category->update($_POST[$id . 'name'], $_POST[$id . 'longname'], $_POST[$id . 'hexcode'], $_POST[$id . 'hoverhexcode']);

    }

}

if (isset($_POST['addnew'])) {

    $category = new forumcategory();

    $category->create($_POST['name'], $_POST['longname'], $_POST['hexcode'], $_POST['hoverhexcode']);

}

$selectCategories->execute();

$rows = array();

while ($categoryData = $selectCategories->fetch()) {

    $category = new forumcategory($categoryData['id']);
    $rows[] = $category->returnArray();

}

echo $twig->render('admin/forumcategories.html', array('rows' => $rows));
