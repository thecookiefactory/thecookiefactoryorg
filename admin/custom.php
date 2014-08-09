<?php

session_start();

$r_c = 1;
require_once "../inc/functions.php";
require_once "../classes/custompage.class.php";
require_once "../classes/user.class.php";

$user = new user((isset($_SESSION["userid"]) ? $_SESSION["userid"] : null));

if (!$user->isAdmin()) die("403");

$twig = twigInit();

if (isset($_POST["text"])) {

    if (isset($_POST["live"]) && $_POST["live"] == "on") {

        $live = 1;

    } else {

        $live = 0;

    }

    try {

        $updatePage = $con->prepare("UPDATE `custompages` SET `custompages`.`text` = :text, `custompages`.`title` = :name, `custompages`.`live` = :live, `custompages`.`stringid` = :stringid WHERE `custompages`.`id` = :id");
        $updatePage->bindValue("text", $_POST["text"], PDO::PARAM_STR);
        $updatePage->bindValue("name", strip($_POST["name"]), PDO::PARAM_STR);
        $updatePage->bindValue("live", $live, PDO::PARAM_INT);
        $updatePage->bindValue("stringid", strip($_POST["stringid"]), PDO::PARAM_STR);
        $updatePage->bindValue("id", strip($_POST["id"]), PDO::PARAM_INT);
        $updatePage->execute();

    } catch (PDOException $e) {

        die("Failed to update the page.");

    }

}

if (isset($_POST["create"])) {

    $title = strip($_POST["title"]);

    try {

        $insertPage = $con->prepare("INSERT INTO `custompages` VALUES(DEFAULT, :title, '', now(), DEFAULT, DEFAULT, '')");
        $insertPage->bindValue("title", $title, PDO::PARAM_STR);
        $insertPage->execute();

    } catch (PDOException $e) {

        die("Failed to create the page.");

    }

}

if (isset($_POST["cpage"])) {

    try {

        $selectPage = $con->prepare("SELECT `custompages`.`id` FROM `custompages` WHERE `custompages`.`title` = :cpage");
        $selectPage->bindValue("cpage", strip($_POST["cpage"]), PDO::PARAM_STR);
        $selectPage->execute();

        $pageData = $selectPage->fetch();

        $page = new custompage($pageData["id"], "id");

        $data = $page->returnArray();

        $mode = "update";

    } catch (PDOException $e) {

        die("Failed to fetch page data.");

    }

} else {

    try {

        $selectPages = $con->query("SELECT `custompages`.`id` FROM `custompages` ORDER BY `custompages`.`title` ASC");

        $pages = array();

        while ($pageData = $selectPages->fetch()) {

            $page = new custompage($pageData["id"], "id");
            $pages[] = $page->returnArray();

        }

        $mode = "select";

    } catch (PDOException $e) {

        die("Failed to fetch pages.");

    }

}

echo $twig->render("admin/custom.html", array("mode" => $mode, "pages" => (isset($pages) ? $pages : 0), "data" => (isset($data) ? $data : 0)));
