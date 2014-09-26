<?php

if (!isset($r_c)) header('Location: /notfound.php');

require_once str_repeat('../', $r_c) . 'classes/master.class.php';

class game extends master {

    protected $id       = null;
    protected $name     = null;
    protected $steamid  = null;

    public function __construct($id = null) {

        global $con;

        if ($id != null) {

            try {

                $getGameData = $con->prepare('
                    SELECT *
                    FROM `games`
                    WHERE `games`.`id` = :id
                ');
                $getGameData->bindValue('id', $id, PDO::PARAM_INT);
                $getGameData->execute();

            } catch (PDOException $e) {

                die('Could not get data for the game.');

            }

            if ($getGameData->rowCount() == 1) {

                $gameData = $getGameData->fetch();

                $this->id       = $gameData['id'];
                $this->name     = $gameData['name'];
                $this->steamid  = $gameData['steamid'];

            } else {

                echo 'Could not find a game with the given id.';

            }

        }

    }

    public function create($name, $steamid) {

        global $con;

        $this->name = strip($name);
        $this->steamid = strip($steamid);

        try {

            $insertGame = $con->prepare('
                INSERT INTO `games`
                VALUES(DEFAULT, :name, :steamid, DEFAULT)
            ');
            $insertGame->bindValue('name', $this->name, PDO::PARAM_STR);
            $insertGame->bindValue('steamid', $this->steamid, PDO::PARAM_INT);
            $insertGame->execute();

        } catch (PDOException $e) {

            die('Failed to add new game.');

        }

    }

    public function update($name, $steamid) {

        global $con;

        if (!validField($name)) {

            $this->delete();

        } else {

            $this->name = strip($name);
            $this->steamid = strip($steamid);

            try {

                $updateGame = $con->prepare('
                    UPDATE `games`
                    SET `games`.`name` = :name,
                        `games`.`steamid`= :steamid
                    WHERE `games`.`id` = :id
                ');
                $updateGame->bindValue('name', $this->name, PDO::PARAM_STR);
                $updateGame->bindValue('steamid', $this->steamid, PDO::PARAM_INT);
                $updateGame->bindValue('id', $this->id, PDO::PARAM_INT);
                $updateGame->execute();

            } catch (PDOException $e) {

                die('Failed to update the game.');

            }

        }

    }

    public function delete() {

        global $con;

        try {

            $deleteGame = $con->prepare('
                DELETE FROM `games`
                WHERE `games`.`id` = :id
            ');
            $deleteGame->bindValue('id', $this->id, PDO::PARAM_INT);
            $deleteGame->execute();

        } catch (PDOException $e) {

            die('Failed to delete the game.');

        }

    }

    public function returnArray() {

        $returnee = array(
            'id' => $this->id,
            'name' => $this->name,
            'steamid' => $this->steamid
        );

        return $returnee;

    }

    public function getName() {

        return $this->name;

    }

    public function isSteamGame() {

        return ($this->steamid != null);

    }

    public function getSteamId() {

        return $this->steamid;

    }

}
