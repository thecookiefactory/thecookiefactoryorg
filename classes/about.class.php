<?php

if (!isset($r_c)) header('Location: /notfound.php');

require_once str_repeat('../', $r_c) . 'classes/user.class.php';

class about extends master {

    protected $id          = null;
    protected $user        = null;
    protected $firstName   = null;
    protected $lastName    = null;
    protected $description = null;
    protected $linkList = array(
        'website' => null,
        'email'   => null,
        'github'  => null,
        'twitter' => null,
        'twitch'  => null,
        'youtube' => null,
        'steam'   => null,
        'reddit'  => null
    );

    public function __construct($userid = null) {

        global $con;

        if ($userid != null) {

            try {

                $getAboutData = $con->prepare('
                    SELECT *
                    FROM `about`
                    WHERE `about`.`userid` = :userid
                ');
                $getAboutData->bindValue('userid', $userid, PDO::PARAM_STR);
                $getAboutData->execute();

            } catch (PDOException $e) {

                die('Could not get data for the about class.');

            }

            if ($getAboutData->rowCount() == 1) {

                $aboutData = $getAboutData->fetch();

                $this->id           = $aboutData['id'];
                $this->user         = new user($aboutData['userid']);
                $this->firstName    = $aboutData['firstname'];
                $this->lastName     = $aboutData['lastname'];
                $this->description  = $aboutData['description'];

                foreach (array_keys($this->linkList) as $key) {

                    $this->linkList[$key] = ($aboutData[$key] != null) ? $aboutData[$key] : null;

                }

            } else {

                die('Could not find an about with the given id.');

            }

        }

    }

    public function update($description, $linkList = null) {

        global $con;

        $this->description = strip($description);

        try {

            $updateDescription = $con->prepare('
                UPDATE `about`
                SET `about`.`description` = :description
                WHERE `about`.`id` = :id
            ');
            $updateDescription->bindValue('description', $this->description, PDO::PARAM_STR);
            $updateDescription->bindValue('id', $this->id, PDO::PARAM_INT);
            $updateDescription->execute();

            if ($linkList == null) {

                return 'descsuccess';

            }

        } catch (PDOException $e) {

            die('Failed to update the description.');

        }

        if ($linkList != null) {

            $linkcount = 0;

            foreach ($linkList as $key => $value) {

                if (vf($value)) {

                    $this->linkList[$key] = strip($value);

                    $linkcount++;

                } else {

                    $this->linkList[$key] = null;

                }

            }

            if ($linkcount > 6) {

                return 'toomuch';

            } else {

                try {

                    $updateLinks = $con->prepare('
                        UPDATE `about`
                        SET `about`.`website` = :website,
                          `about`.`email` = :email,
                          `about`.`github` = :github,
                          `about`.`twitter` = :twitter,
                          `about`.`twitch` = :twitch,
                          `about`.`youtube` = :youtube,
                          `about`.`steam` = :steam,
                          `about`.`reddit` = :reddit
                        WHERE `about`.`id` = :id
                    ');

                    foreach ($this->linkList as $key => $value) {

                        $updateLinks->bindValue($key, $value, PDO::PARAM_STR);

                    }

                    $updateLinks->bindValue('id', $this->id, PDO::PARAM_INT);
                    $updateLinks->execute();

                    return 'success';

                } catch (PDOException $e) {

                    die('Failed to update your links.');

                }

            }

        }

    }

    public function returnArray() {

        $returnee = array(
            'username' => $this->user->getName(),
            'firstname' => $this->firstName,
            'lastname' => $this->lastName,
            'description' => $this->description,
            'linkList' => array()
        );

        foreach ($this->linkList as $key => $value) {

            if ($this->linkList[$key] != null) {

                $returnee['linkList'][$key] = $value;

            }

        }

        return $returnee;

    }

}
