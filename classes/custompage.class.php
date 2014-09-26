<?php

if (!isset($r_c)) header('Location: /notfound.php');

require_once str_repeat('../', $r_c) . 'classes/dtime.class.php';
require_once str_repeat('../', $r_c) . 'classes/master.class.php';

class custompage extends master {

    protected $id       = null;
    protected $title    = null;
    protected $text     = null;
    protected $date     = null;
    protected $editdate = null;
    protected $live     = null;
    protected $stringid = null;

    public function __construct($stringid = null) {

        global $con;

        if ($stringid != null) {

            try {

                $getPageData = $con->prepare('
                    SELECT *, BIN(`custompages`.`live`)
                    FROM `custompages`
                    WHERE `custompages`.`stringid` = :stringid
                ');
                $getPageData->bindValue('stringid', $stringid, PDO::PARAM_STR);
                $getPageData->execute();

            } catch (PDOException $e) {

                die('Could not get data for the custom page.');

            }

            if ($getPageData->rowCount() == 1) {

                $pageData = $getPageData->fetch();

                $this->id       = $pageData['id'];
                $this->title    = $pageData['title'];
                $this->text     = $pageData['text'];
                $this->date     = new dtime($pageData['date']);
                $this->editdate = ($pageData['editdate'] != null) ? new dtime($pageData['editdate']) : null;
                $this->live     = (int) $pageData['BIN(`custompages`.`live`)'];
                $this->stringid = $pageData['stringid'];

            } else {

                echo 'Could not find a custom page with the given id.';

            }

        }

    }

    public function create($stringid = null) {

        global $con;

        if ($this->id == null) {

            $this->stringid = strip($stringid);

            try {

                $createPage = $con->prepare('
                    INSERT INTO `custompages`
                    VALUES(DEFAULT, "", "", DEFAULT, DEFAULT, DEFAULT, :stringid)
                ');
                $createPage->bindValue('stringid', $this->stringid, PDO::PARAM_STR);
                $createPage->execute();

            } catch (PDOException $e) {

                die('Failed to create the page.');

            }

        }

    }

    public function update($title, $text, $live, $stringid) {

        global $con;

        $this->title = strip($title);
        $this->text = strip($text);
        $this->live = strip($live);
        $this->stringid = strip($stringid);

        try {

            $updatePage = $con->prepare('
                UPDATE `custompages`
                SET `custompages`.`title` = :title,
                    `custompages`.`text` = :text,
                    `custompages`.`live` = :live,
                    `custompages`.`stringid` = :stringid
                WHERE `custompages`.`id` = :id
            ');
            $updatePage->bindValue('title', $this->title, PDO::PARAM_STR);
            $updatePage->bindValue('text', $this->text, PDO::PARAM_STR);
            $updatePage->bindValue('live', (int) $this->live, PDO::PARAM_INT);
            $updatePage->bindValue('stringid', $this->stringid, PDO::PARAM_STR);
            $updatePage->bindValue('id', $this->id, PDO::PARAM_INT);
            $updatePage->execute();

        } catch (PDOException $e) {

            die('Failed to update the page.');

        }

    }

    public function returnArray() {

        $returnee = array(
            'id' => $this->id,
            'title' => $this->title,
            'text' => $this->text,
            'live' => $this->live,
            'stringid' => $this->stringid
        );

        return $returnee;

    }

}
