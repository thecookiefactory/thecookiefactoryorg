<?php

if (!isset($r_c)) header("Location: /notfound.php");

require_once str_repeat("../", $r_c) . "inc/classes/dtime.class.php";
require_once str_repeat("../", $r_c) . "inc/classes/master.class.php";

/**
 * user class
 *
 * function __construct
 *
 * function login
 *
 * function register
 *
 * function getName
 *
 * function getTwitchName
 *
 * function isAdmin
 */
class user extends master {

    /**
     * variables
     *
     * @var $id int
     * @var $name string
     * @var $steamid string
     * @var $admin int
     * @var $cookieh string
     * @var $date object
     * @var $twitchname string
     */
    protected $id           = null;
    protected $name         = null;
    protected $steamid      = null;
    protected $admin        = null;
    protected $cookieh      = null;
    protected $date         = null;
    protected $twitchname   = null;

    public function __construct($id = null, $field = null) {

        global $con;

        if ($id != null) {

            if ($field == "name") {

                try {

                    $squery = $con->prepare("SELECT *, BIN(`users`.`admin`) FROM `users` WHERE `users`.`name` = :id");
                    $squery->bindValue("id", $id, PDO::PARAM_STR);
                    $squery->execute();

                } catch (PDOException $e) {

                    echo "An error occured while trying to fetch data to the class.";

                }

            } else {

                try {

                    $squery = $con->prepare("SELECT *, BIN(`users`.`admin`) FROM `users` WHERE `users`.`id` = :id");
                    $squery->bindValue("id", $id, PDO::PARAM_INT);
                    $squery->execute();

                } catch (PDOException $e) {

                    echo "An error occured while trying to fetch data to the class.";

                }

            }

            if ($squery->rowCount() == 1) {

                $srow = $squery->fetch();

                $this->id           = $srow["id"];
                $this->name         = $srow["name"];
                $this->steamid      = $srow["steamid"];
                $this->admin        = (int) $srow["BIN(`users`.`admin`)"];
                $this->cookieh      = $srow["cookieh"];
                $this->date         = new dtime($srow["date"]);
                $this->twitchname   = $srow["twitchname"];

            } else {

                echo "Could not find a user with the given id.";

            }

        }

    }

    public function login() {

        global $con;
        global $config;

        if ($this->isReal()) {

            $rs = "<span class='menu-item' class='actionbar-logindata'>logged in as <span class='actionbar-username'> " . $this->getName() . "</span></span>";

            if ($this->isAdmin()) {

                $rs .= "<span class='menu-item'><a href='/admin/index.php' target='_blank'>admin menu</a></span>";

            }

            $rs .= "<span class='menu-item'><a href='/logout'>log out</a></span>";

            if (isset($_GET["p"]) && $_GET["p"] == "logout") {

                setcookie("userid", "", time() - 100000, "/");
                unset($_SESSION["steamauth"]);
                unset($_SESSION["steamid"]);
                unset($_SESSION["userid"]);

                if (isset($_SESSION["lp"])) {

                    header("Location: /" . $_SESSION["lp"]);

                } else {

                    header("Location: /news");

                }

            }

            return $rs;

        } else {

            if (!isset($OpenID)) {

                $OpenID = new LightOpenID($config["domain"]);

            }

            if (!$OpenID->mode) {

                if (isset($_GET["p"]) && $_GET["p"] == "login") {

                    $OpenID->identity = "http://steamcommunity.com/openid";
                    header("Location: {$OpenID->authUrl()}");

                }

                if (!isset($_SESSION["userid"])) {

                    return "<a class='menu-item' href='/login'><span class='login-text faux-link'>sign in via steam</span><img class='login-button login-button-image' src='http://cdn.steamcommunity.com/public/images/signinthroughsteam/sits_small.png' alt='login steam button'></a>";

                }

            } elseif ($OpenID->mode == "cancel") {

                return "user canceled auth";

            } else {

                if (!isset($_SESSION["userid"])) {

                    $_SESSION["steamauth"] = $OpenID->validate() ? $OpenID->identity : null;
                    $_SESSION["steamid"] = str_replace("http://steamcommunity.com/openid/id/", "", $_SESSION["steamauth"]);

                    $selectUserId = $con->prepare("SELECT `users`.`id` FROM `users` WHERE `users`.`steamid` = :steamid");
                    $selectUserId->bindValue("steamid", $_SESSION["steamid"], PDO::PARAM_STR);
                    $selectUserId->execute();

                    if ($selectUserId->rowCount() == 1) {

                        // yes
                        $userData = $selectUserId->fetch();

                        $_SESSION["userid"] = $userData["id"];

                        $cookieh = cookieh();

                        $updateCookieh = $con->prepare("UPDATE `users` SET `users`.`cookieh` = :cookieh WHERE `users`.`id` = :id");
                        $updateCookieh->bindValue("cookieh", hash("sha256", $cookieh), PDO::PARAM_STR);
                        $updateCookieh->bindValue("id", $userData["id"], PDO::PARAM_INT);
                        $updateCookieh->execute();

                        setcookie("userid", $cookieh, time() + 2592000, "/");

                        if (isset($_SESSION["lp"])) {

                            header("Location: /" . $_SESSION["lp"]);

                        } else {

                            header("Location: /news");

                        }

                    } else {

                        // no
                        header("Location: /register");

                    }

                }

            }

        }

    }

    public function register($username) {

        global $con;

        $username = strip($username);

        //checking if the username has valid characters only and is of the specified length
        if (!ctype_alnum(str_replace('-', '', $username))) {

            echo "Your username can contain English letters, numbers, and underscores only.";
            return;

        }

        if (strlen($username) < 2 || strlen($username) > 10) {

            echo "Your username must be 2 to 10 characters long.";
            return;

        }

        //checking if that user already exists
        $selectUserId = $con->prepare("SELECT `users`.`id` FROM `users` WHERE `users`.`name` = :username");
        $selectUserId->bindValue("username", $username, PDO::PARAM_STR);
        $selectUserId->execute();

        if ($selectUserId->rowCount() != 0) {

            echo "Sorry, that username is already taken.";
            return;

        }

        $cookieh = cookieh();

        //registering the user and redirecting to the login form
        $iquery = $con->prepare("INSERT INTO `users` VALUES(DEFAULT, :username, :steamid, DEFAULT, :cookieh, DEFAULT, DEFAULT)");
        $iquery->bindValue("username", $username, PDO::PARAM_STR);
        $iquery->bindValue("steamid", $_SESSION["steamid"], PDO::PARAM_INT);
        $iquery->bindValue("cookieh", hash("sha256", $cookieh), PDO::PARAM_STR);
        $iquery->execute();

        $id = $con->lastInsertId();

        $_SESSION["userid"] = $id;
        setcookie("userid", $cookieh, time() + 2592000, "/");

        if (isset($_SESSION["lp"])) {

            header("Location: /" . $_SESSION["lp"]);

        } else {

            header("Location: /news");

        }

    }

    public function getName($span = false) {

        if (!$this->isAdmin() || $span !== true) {

            return $this->name;

        } else if (isset($span) && $span === true) {

            return "<span class='admin-name'>" . $this->name . "</span>";

        }

    }

    public function getTwitchName() {

        return $this->twitchname;

    }

    public function isAdmin() {

        return ($this->admin != 0);

    }

}
