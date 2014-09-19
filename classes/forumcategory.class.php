<?php

if (!isset($r_c)) header('Location: /notfound.php');

require_once str_repeat('../', $r_c) . 'classes/dtime.class.php';
require_once str_repeat('../', $r_c) . 'classes/master.class.php';

class forumcategory extends master {

    protected $id           = null;
    protected $name         = null;
    protected $longname     = null;
    protected $hexcode      = null;
    protected $hoverhexcode = null;
    protected $date         = null;

    public function __construct($id = null, $field = null) {

        global $con;

        if ($id != null) {

            if ($field == 'name') {

                try {

                    $getCategoryData = $con->prepare('
                        SELECT *
                        FROM `forumcategories`
                        WHERE `forumcategories`.`name` = :id
                    ');
                    $getCategoryData->bindValue('id', $id, PDO::PARAM_STR);
                    $getCategoryData->execute();

                } catch (PDOException $e) {

                    die('Could not get data for the category.');

                }

            } else {

                try {

                    $getCategoryData = $con->prepare('
                        SELECT *
                        FROM `forumcategories`
                        WHERE `forumcategories`.`id` = :id
                    ');
                    $getCategoryData->bindValue('id', $id, PDO::PARAM_INT);
                    $getCategoryData->execute();

                } catch (PDOException $e) {

                    die('Could not get data for the category.');

                }

            }

            if ($getCategoryData->rowCount() == 1) {

                $categoryData = $getCategoryData->fetch();

                $this->id           = $categoryData['id'];
                $this->name         = $categoryData['name'];
                $this->longname     = $categoryData['longname'];
                $this->hexcode      = $categoryData['hexcode'];
                $this->hoverhexcode = $categoryData['hoverhexcode'];
                $this->date         = new dtime($categoryData['date']);

            } else {

                echo 'Could not find a category with the given id.';

            }

        }

    }

    public function create($name, $longname, $hexcode, $hoverhexcode) {

        global $con;

        $this->name = strip($name);
        $this->longname = strip($longname);
        $this->hexcode = strip($hexcode);
        $this->hoverhexcode = strip($hoverhexcode);

        try {

            $insertCategory = $con->prepare('
                INSERT INTO `forumcategories`
                VALUES(DEFAULT, :name, :longname, :hexcode, :hoverhexcode, DEFAULT)
            ');
            $insertCategory->bindValue('name', $this->name, PDO::PARAM_STR);
            $insertCategory->bindValue('longname', $this->longname, PDO::PARAM_STR);
            $insertCategory->bindValue('hexcode', $this->hexcode, PDO::PARAM_STR);
            $insertCategory->bindValue('hoverhexcode', $this->hoverhexcode, PDO::PARAM_STR);
            $insertCategory->execute();

        } catch (PDOException $e) {

            die('Failed to add new category.');

        }

    }

    public function update($name, $longname, $hexcode, $hoverhexcode) {

        global $con;

        if (!validField($name)) {

            $this->delete();

        } else {

            $this->name = strip($name);
            $this->longname = strip($longname);
            $this->hexcode = strip($hexcode);
            $this->hoverhexcode = strip($hoverhexcode);

            try {

                $updateCategory = $con->prepare('
                    UPDATE `forumcategories`
                    SET `forumcategories`.`name` = :name,
                        `forumcategories`.`longname` = :longname,
                        `forumcategories`.`hexcode` = :hexcode,
                        `forumcategories`.`hoverhexcode` = :hoverhexcode
                    WHERE `forumcategories`.`id` = :id
                ');
                $updateCategory->bindValue('name', $this->name, PDO::PARAM_STR);
                $updateCategory->bindValue('longname', $this->longname, PDO::PARAM_STR);
                $updateCategory->bindValue('hexcode', $this->hexcode, PDO::PARAM_STR);
                $updateCategory->bindValue('hoverhexcode', $this->hoverhexcode, PDO::PARAM_STR);
                $updateCategory->bindValue('id', $this->id, PDO::PARAM_INT);
                $updateCategory->execute();

            } catch (PDOException $e) {

                die('Failed to update the category.');

            }

        }

    }

    public function delete() {

        global $con;

        try {

            $deleteCategory = $con->prepare('
                DELETE FROM `forumcategories`
                WHERE `forumcategories`.`id` = :id
            ');
            $deleteCategory->bindValue('id', $this->id, PDO::PARAM_INT);
            $deleteCategory->execute();

        } catch (PDOException $e) {

            die('Failed to delete the category.');

        }

    }

    public function returnArray() {

        $returnee = array(
            'id' => $this->id,
            'name' => $this->name,
            'longname' => $this->longname,
            'hexcode' => $this->hexcode,
            'hoverhexcode' => $this->hoverhexcode
        );

        return $returnee;

    }

    public function getName() {

        return $this->name;

    }

    public function getLongName() {

        return $this->longname;

    }

}
