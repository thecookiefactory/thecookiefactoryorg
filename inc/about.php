<?php

if (!isset($r_c)) header('Location: /notfound.php');

require_once 'classes/about.class.php';

$aboutList = array();

$_SESSION['lp'] = $p;

$groupAbout = new about(1);
$description = $groupAbout->returnArray()['description'];

try {

    $getAdmins = $con->query('
        SELECT `users`.`id`
        FROM `users`
        WHERE `users`.`admin` = 1 AND `users`.`id` <> 1
        ORDER BY `users`.`name` ASC
    ');

    while ($admin = $getAdmins->fetch()) {

        $about = new about($admin['id']);

        $aboutList[] = $about->returnArray();

    }

} catch (PDOException $e) {

    echo 'An error occurred while trying to fetch data.';

}

echo $twig->render('about.html', array('index_var' => $index_var, 'description' => $description, 'aboutList' => $aboutList));
