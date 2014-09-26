<?php

if (!isset($r_c)) header('Location: /notfound.php');

require_once str_repeat('../', $r_c) . 'classes/dtime.class.php';
require_once str_repeat('../', $r_c) . 'classes/forumthread.class.php';
require_once str_repeat('../', $r_c) . 'classes/game.class.php';
require_once str_repeat('../', $r_c) . 'classes/picture.class.php';
require_once str_repeat('../', $r_c) . 'classes/user.class.php';

class map extends master {

    protected $id               = null;
    protected $name             = null;
    protected $text             = null;
    protected $author           = null;
    protected $date             = null;
    protected $editdate         = null;
    protected $dl               = null;
    protected $extension        = null;
    protected $comments         = null;
    protected $game             = null;
    protected $link             = null;
    protected $downloadcount    = null;

    public function __construct($id = null) {

        global $con;

        if ($id != null) {

            try {

                $getMapData = $con->prepare('
                    SELECT *, BIN(`maps`.`comments`)
                    FROM `maps`
                    WHERE `maps`.`id` = :id
                ');
                $getMapData->bindValue('id', $id, PDO::PARAM_INT);
                $getMapData->execute();

            } catch (PDOException $e) {

                die('Could not get data for the map.');

            }

            if ($getMapData->rowCount() == 1) {

                $mapData = $getMapData->fetch();

                $this->id               = $mapData['id'];
                $this->name             = $mapData['name'];
                $this->text             = $mapData['text'];
                $this->author           = new user($mapData['authorid']);
                $this->date             = new dtime($mapData['date']);
                $this->editdate         = ($mapData['editdate'] != null) ? new dtime($mapData['editdate']) : null;
                $this->dl               = $mapData['dl'];
                $this->comments         = (int) $mapData['BIN(`maps`.`comments`)'];
                $this->game             = new game($mapData['gameid']);
                $this->link             = $mapData['link'];
                $this->downloadcount    = $mapData['downloadcount'];

            } else {

                echo 'Could not find a map with the given id.';

            }

        }

    }

    public function create($name, $text, $authorid, $download, $comments, $gameid) {

        global $con;

        $this->name = strip($name);
        $this->text = strip($text, true);
        $this->author = new user(strip($authorid));
        $this->dl = strip($download);
        $this->game = new game(strip($gameid));
        $this->link = strip($link);

        try {

            $createMap = $con->prepare('
                INSERT INTO `maps`
                VALUES(
                    DEFAULT,
                    :name,
                    :text,
                    :author,
                    now(),
                    DEFAULT,
                    :download,
                    :comments,
                    :game,
                    "",
                    0
                )
            ');
            $createMap->bindValue('name', $name, PDO::PARAM_STR);
            $createMap->bindValue('text', $text, PDO::PARAM_STR);
            $createMap->bindValue('author', $author, PDO::PARAM_INT);
            $createMap->bindValue('download', $download, PDO::PARAM_STR);
            $createMap->bindValue('comments', $comments, PDO::PARAM_INT);
            $createMap->bindValue('game', $game, PDO::PARAM_INT);
            $createMap->execute();

        } catch (PDOException $e) {

            die('Query failed.');

        }

        $id = $con->lastInsertId();

    }

    public function update($name, $authorid, $gameid, $text, $download, $link) {

        global $con;

        $this->name = strip($name);
        $this->author = new user(strip($authorid));
        $this->game = new game(strip($gameid));
        $this->text = strip($text, true);
        $this->dl = strip($download);
        $this->link = strip($link);

        try {

            $updateMapData = $con->prepare('
                UPDATE `maps`
                SET `maps`.`name` = :name,
                    `maps`.`authorid` = :authorid,
                    `maps`.`gameid` = :game,
                    `maps`.`text` = :text,
                    `maps`.`dl` = :download,
                    `maps`.`link` = :link
                WHERE `maps`.`id` = :id
            ');
            $updateMapData->bindValue('id', $this->id, PDO::PARAM_INT);
            $updateMapData->bindValue('name', $this->name, PDO::PARAM_STR);
            $updateMapData->bindValue('authorid', $this->author->getId(), PDO::PARAM_INT);
            $updateMapData->bindValue('game', $this->game->getId(), PDO::PARAM_INT);
            $updateMapData->bindValue('text', $this->text, PDO::PARAM_STR);
            $updateMapData->bindValue('download', $this->dl, PDO::PARAM_STR);
            $updateMapData->bindValue('link', $this->link, PDO::PARAM_STR);
            $updateMapData->execute();

        } catch (PDOException $e) {

            die('Query failed.');

        }

    }

    public function delete() {

        global $con;

        foreach ($this->getPictures() as $picture) {

            $picture->delete();

        }
////////////////////////////////////////////////////////////////////6
        $mapthread = new forumthread($this->threadid);
        $mapthread->delete();

        try {

            //deleting the forum thread
            $selectThreadData = $con->prepare('SELECT `forumthreads`.`id` FROM `forumthreads` WHERE `forumthreads`.`mapid` = :id');
            $selectThreadData->bindValue('id', $this->id, PDO::PARAM_INT);
            $selectThreadData->execute();

            $threadData = $selectThreadData->fetch();

            $mapthread = new forumthread($threadData['id']);
            $mapthread->delete('r_c');

        } catch (PDOException $e) {

            die('Query failed.');

        }

        try {

            $deleteMap = $con->prepare('
                DELETE FROM `maps`
                WHERE `maps`.`id` = :id
            ');
            $deleteMap->bindValue('id', $this->id, PDO::PARAM_INT);
            $deleteMap->execute();

        } catch (PDOException $e) {

            die('Query failed.');

        }

    }

    protected function getPictures() {

        global $con;

        $pictures = array();

        try {

            $selectPictures = $con->prepare('
                SELECT `pictures`.`id`
                FROM `pictures`
                WHERE `pictures`.`mapid` = :id
                ORDER BY `pictures`.`ordernumber` ASC
            ');
            $selectPictures->bindValue('id', $this->id, PDO::PARAM_INT);
            $selectPictures->execute();

        } catch (PDOException $e) {

            echo 'Could not get the pictures.';

        }

        if ($selectPictures->rowCount() != 0) {

            while ($foundPicture = $selectPictures->fetch()) {

                $pictures[] = new picture($foundPicture['id']);

            }

        }

        return $pictures;

    }

    public function returnArray() {

        global $con;
        global $config;

        $returnee = array(
            'id' => $this->id,
            'name' => $this->name,
            'text' => $this->text,
            'author' => $this->author->getName(),
            'editdate' => $this->editdate->display(),
            'comments' => $this->comments,
            'game' => array('name' => $this->game->getName(), 'steamid' => $this->game->getSteamId()),
            'link' => $this->link,
            'downloadcount' => $this->downloadcount,
            'picturecount' => count($this->getPictures()),
            'pictures' => array()
        );

        if ($this->comments) {

            try {

                $selectThreadId = $con->prepare('
                    SELECT `forumthreads`.`id`
                    FROM `forumthreads`
                    WHERE `forumthreads`.`mapid` = :id
                ');
                $selectThreadId->bindValue('id', $this->id, PDO::PARAM_INT);
                $selectThreadId->execute();
                $threadData = $selectThreadId->fetch();

                $thread = new forumthread($threadData['id']);

                $returnee['thread'] = array('id' => $thread->getId(), 'replycount' => $thread->replyCount());

            } catch (PDOException $e) {

                echo 'Failed to fetch the related forum thread.';

            }

        }

        foreach ($this->getPictures() as $picture) {

            $returnee['pictures'][] = array('url' => $picture->getUrl(), 'text' => $picture->getText());

        }

        return $returnee;

    }

    public function getName() {

        return $this->name;

    }

}
