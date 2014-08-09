<?php

session_start();

$r_c = 1;
require_once "../inc/functions.php";
require_once "../classes/forumcategory.class.php";
require_once "../classes/user.class.php";

$user = new user((isset($_SESSION["userid"]) ? $_SESSION["userid"] : null));

if (!$user->isAdmin()) die("403");

$twig = twigInit();

try {

    $selectCategories = $con->query("SELECT `forumcategories`.`id` FROM `forumcategories`");

    if (isset($_POST["update"])) {

        while ($categoryData = $selectCategories->fetch()) {

            $id = $categoryData["id"];
            $name = strip($_POST[$id."name"]);
            $longname = strip($_POST[$id."longname"]);
            $hexcode = strip($_POST[$id."hexcode"]);
            $hoverhexcode = strip($_POST[$id."hoverhexcode"]);

            if (!vf($name)) {

                try {

                    $deleteCategory = $con->prepare("DELETE FROM `forumcategories` WHERE `forumcategories`.`id` = :id");
                    $deleteCategory->bindValue("id", $id, PDO::PARAM_INT);
                    $deleteCategory->execute();

                } catch (PDOException $e) {

                    die("Failed to delete categories.");

                }

            } else {

                try {

                    $updateCategory = $con->prepare("UPDATE `forumcategories`
                                                     SET `forumcategories`.`name` = :name, `forumcategories`.`longname` = :longname, `forumcategories`.`hexcode` = :hexcode, `forumcategories`.`hoverhexcode` = :hoverhexcode
                                                     WHERE `forumcategories`.`id` = :id");
                    $updateCategory->bindValue("name", $name, PDO::PARAM_STR);
                    $updateCategory->bindValue("longname", $longname, PDO::PARAM_STR);
                    $updateCategory->bindValue("hexcode", $hexcode, PDO::PARAM_STR);
                    $updateCategory->bindValue("hoverhexcode", $hoverhexcode, PDO::PARAM_STR);
                    $updateCategory->bindValue("id", $id, PDO::PARAM_INT);
                    $updateCategory->execute();

                } catch (PDOException $e) {

                    die("Failed to update categories.");

                }

            }

        }

    }

    if (isset($_POST["addnew"])) {

        $name = strip($_POST["name"]);
        $longname = strip($_POST["longname"]);
        $hexcode = strip($_POST["hexcode"]);
        $hoverhexcode = strip($_POST["hoverhexcode"]);

        try {

            $insertCategory = $con->prepare("INSERT INTO `forumcategories` VALUES(DEFAULT, :name, :longname, :hexcode, :hoverhexcode, DEFAULT)");
            $insertCategory->bindValue("name", $name, PDO::PARAM_STR);
            $insertCategory->bindValue("longname", $longname, PDO::PARAM_STR);
            $insertCategory->bindValue("hexcode", $hexcode, PDO::PARAM_STR);
            $insertCategory->bindValue("hoverhexcode", $hoverhexcode, PDO::PARAM_STR);
            $insertCategory->execute();

        } catch (PDOException $e) {

            die("Failed to add new category.");

        }

    }

    $selectCategories = $con->query("SELECT `forumcategories`.`id` FROM `forumcategories`");

    $rows = array();

    while ($categoryData = $selectCategories->fetch()) {

        $category = new forumcategory($categoryData["id"]);
        $rows[] = $category->returnArray();

    }

} catch (PDOException $e) {

    die("Failed to select categories.");

}

echo $twig->render("admin/forumcategories.html", array("rows" => $rows));
