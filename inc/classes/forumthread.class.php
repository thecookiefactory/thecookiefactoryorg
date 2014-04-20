<?php

if (!isset($r_c)) header("Location: /notfound.php");

require_once str_repeat("../", $r_c) . "inc/classes/dtime.class.php";
require_once str_repeat("../", $r_c) . "inc/classes/forumcategory.class.php";
require_once str_repeat("../", $r_c) . "inc/classes/forumpost.class.php";
require_once str_repeat("../", $r_c) . "inc/classes/map.class.php";
require_once str_repeat("../", $r_c) . "inc/classes/news.class.php";
require_once str_repeat("../", $r_c) . "inc/classes/user.class.php";

/**
 * forum thread class
 *
 * function __construct
 *
 * function returnArray
 *
 * function commentProcess
 *
 * function addNew
 *
 * function addProcess
 *
 * function addForm
 *
 * function edit
 *
 * function editProcess
 *
 * function editForm
 *
 * function replyCount
 *
 * function isNewsThread
 *
 * function getNewsStringId
 *
 * function isClosed
 */
class forumthread extends master {

    /**
     * variables
     *
     * @var $id int
     * @var $title string
     * @var $text string
     * @var $author object
     * @var $date object
     * @var $editdate object
     * @var $lastdate object
     * @var $forumcategory object
     * @var $map object
     * @var $news object
     * @var $closed int
     */
    protected $id               = null;
    protected $title            = null;
    protected $text             = null;
    protected $author           = null;
    protected $date             = null;
    protected $editdate         = null;
    protected $lastdate         = null;
    protected $forumcategory    = null;
    protected $map              = null;
    protected $news             = null;
    protected $closed           = null;

    public function __construct($id = null) {

        global $con;

        if ($id != null) {

            try {

                $squery = $con->prepare("SELECT *, BIN(`forumthreads`.`closed`) FROM `forumthreads` WHERE `forumthreads`.`id` = :id");
                $squery->bindValue("id", $id, PDO::PARAM_INT);
                $squery->execute();

            } catch (PDOException $e) {

                die("An error occured while trying to fetch data to the class.");

            }

            if ($squery->rowCount() == 1) {

                $srow = $squery->fetch();

                $this->id               = $srow["id"];
                $this->title            = $srow["title"];
                $this->text             = $srow["text"];
                $this->author           = new user($srow["authorid"]);
                $this->date             = new dtime($srow["date"]);
                $this->editdate         = ($srow["editdate"] != null) ? new dtime($srow["editdate"]) : null;
                $this->lastdate         = new dtime($srow["lastdate"]);
                $this->forumcategory    = ($srow["forumcategory"] != 0) ? new forumcategory($srow["forumcategory"]) : null;
                $this->map              = ($srow["mapid"] != 0) ? new map($srow["mapid"]) : null;
                $this->news             = ($srow["newsid"] != 0) ? new news($srow["newsid"]) : null;
                $this->closed           = (int) $srow["BIN(`forumthreads`.`closed`)"];

            } else {

                echo "Could not find a thread with the given id.";

            }

        }

    }

    public function returnArray($loc = null) {

        global $con;
        global $user;

        $a = array(
                    "id" => $this->id,
                    "title" => $this->title,
                    "text" => $this->text,
                    "author" => $this->author->getName(),
                    "date" => $this->date->display(),
                    "editdate" => 0,
                    "lastdate" => $this->lastdate->display(),
                    "categoryname" => 0,
                    "mapname" => 0,
                    "newsstringid" => 0,
                    "closed" => $this->closed,
                    "replycount" => $this->replyCount()
                    );

        if ($this->editdate != null)
            $a["editdate"] = $this->editdate->display();

        if ($this->forumcategory != null)
            $a["categoryname"] = $this->forumcategory->getName();

        if ($this->map != null)
            $a["mapname"] = $this->map->getName();

        if ($this->news != null)
            $a["newsstringid"] = $this->news->getStringId();

        if ($loc == "main") {

            $a["firstpost"] = array(
                                    "id" => "",
                                    "author" => $this->author->getName(),
                                    "date" => $this->date->display(),
                                    "editdate" => 0,
                                    "text" => tformat($this->text),
                                    "userhasrights" => 0
                                    );

            if ($this->editdate != null)
                $a["firstpost"]["editdate"] = $this->editdate->display();

            if (($user->isReal() && $this->author->getId() == $user->getId() && !$this->isClosed()) || $user->isAdmin())
                $a["firstpost"]["userhasrights"] = 1;

            $a["posts"] = array();
            
            try {
            
                $selectPosts = $con->prepare("SELECT `forumposts`.`id` FROM `forumposts` WHERE `forumposts`.`threadid` = :id");
                $selectPosts->bindValue("id", $this->id, PDO::PARAM_INT);
                $selectPosts->execute();

                while ($foundPost = $selectPosts->fetch()) {

                    $post = new forumpost($foundPost["id"]);
                    $a["posts"][] = $post->returnArray("main");

                }
            
            } catch (PDOException $e) {
            
                die("Failed to fetch forum posts.");
            
            }

        }

        return $a;

    }

    public function commentProcess() {

        global $con;
        global $user;

        if (isset($_POST["cp"]) && isset($_POST["text"]) && vf($_POST["text"]) && $user->isReal() && !$this->isClosed()) {

            $author = $user->getId();
            $text = strip($_POST["text"]);

            if (strlen($text) > 20000) {

                echo "Your comment must be less than 20 000 characters long.";

            } else {
            
                try {
                
                    $insertComment = $con->prepare("INSERT INTO `forumposts` VALUES(DEFAULT, :text, :author, DEFAULT, DEFAULT, :id)");
                    $insertComment->bindValue("author", $author, PDO::PARAM_INT);
                    $insertComment->bindValue("text", $text, PDO::PARAM_STR);
                    $insertComment->bindValue("id", $this->id, PDO::PARAM_INT);
                    $insertComment->execute();

                    $updateLastDate = $con->prepare("UPDATE `forumthreads` SET `forumthreads`.`lastdate` = now() WHERE `forumthreads`.`id` = :id");
                    $updateLastDate->bindValue("id", $this->id, PDO::PARAM_INT);
                    $updateLastDate->execute();
                
                } catch (PDOException $e) {
                
                    echo "Failed to post your comment.";
                
                }

            }

        }

    }

    public function addNew() {

        global $con;

        if (isset($_POST["addnew"]) && (isset($_POST["cat"]) && vf($_POST["cat"])) && (isset($_POST["title"]) && vf($_POST["title"])) && (isset($_POST["text"]) && vf($_POST["text"]))) {

            $this->addProcess();

        } else {

            $this->addForm();

        }

    }

    protected function addProcess() {

        global $con;
        global $user;

        $authorid = $user->getId();
        $cat = new forumcategory(strip($_POST["cat"]));

        if (!$cat->isReal()) {

            echo "That does not seem like a real forum category. Sorry, kiddo.";

        } else {

            $title = strip($_POST["title"]);

            if (strlen($title) > 37) {

                echo "Please enter a title shorter than 38 characters.";

            } else {

                $text = strip($_POST["text"]);

                if (strlen($text) > 20000) {

                    echo "Your comment must be less than 20,000 characters long.";

                } else {
                
                    try {
                    
                        $insertThread = $con->prepare("INSERT INTO `forumthreads` VALUES(DEFAULT, :title, :text, :authorid, DEFAULT, DEFAULT, DEFAULT, :cat, DEFAULT, DEFAULT, 0)");
                        $insertThread->bindValue("authorid", $authorid, PDO::PARAM_INT);
                        $insertThread->bindValue("title", $title, PDO::PARAM_STR);
                        $insertThread->bindValue("text", $text, PDO::PARAM_STR);
                        $insertThread->bindValue("cat", $cat->getId(), PDO::PARAM_INT);
                        $insertThread->execute();

                        header("Location: /forums/" . $con->lastInsertId());
                    
                    } catch (PDOException $e) {
                    
                        echo "Failed to post your thread.";
                    
                    }

                }

            }

        }

    }

    protected function addForm() {

        global $con;
        global $twig;

        $categories = array();
        
        try {
        
            $selectCategories = $con->query("SELECT `forumcategories`.`id` FROM `forumcategories` ORDER BY `forumcategories`.`name` ASC");
        
        } catch (PDOException $e) {
        
            die("Failed to fetch forum categories.");
        
        }

        while ($foundCategory = $selectCategories->fetch()) {

            $cat = new forumcategory($foundCategory["id"]);
            $categories[] = $cat->returnArray();

        }

        echo $twig->render("forum-add.html", array("categories" => $categories));

    }

    public function edit() {

        global $con;
        global $user;

        if ((($this->author->getId() != $user->getId()) || $this->isClosed()) && !$user->isAdmin()) {

            echo "You dont have the right!!";

        } else {

            if (isset($_POST["edit"]) && (isset($_POST["cat"]) && vf($_POST["cat"])) && (isset($_POST["title"]) && vf($_POST["title"])) && (isset($_POST["text"]) && vf($_POST["text"]))) {

                $this->editProcess();

            } else {

                $this->editForm();

            }

        }

    }

    protected function editProcess() {

        global $con;
        global $user;

        $cat = new forumcategory(strip($_POST["cat"]));

        if (!$cat->isReal()) {

            echo "That does not seem like a real forum category. Sorry, kiddo.";

        } else {

            $title = strip($_POST["title"]);

            if (strlen($title) > 37) {

                echo "Please enter a title shorter than 38 characters.";

            } else {

                $text = strip($_POST["text"]);

                if (strlen($text) > 20000) {

                    echo "Your comment must be less than 20,000 characters long.";

                } else {
                
                    try {
                    
                        $updateThread = $con->prepare("UPDATE `forumthreads` SET `forumthreads`.`forumcategory` = :cat, `forumthreads`.`title` = :title, `forumthreads`.`text` = :text, `forumthreads`.`editdate` = now() WHERE `forumthreads`.`id` = :tid");
                        $updateThread->bindValue("cat", $cat->getId(), PDO::PARAM_INT);
                        $updateThread->bindValue("title", $title, PDO::PARAM_STR);
                        $updateThread->bindValue("text", $text, PDO::PARAM_STR);
                        $updateThread->bindValue("tid", $this->id, PDO::PARAM_INT);
                        $updateThread->execute();
                    
                    } catch (PDOException $e) {
                    
                        echo "Failed to update the thread.";
                    
                    }

                }

            }

        }

        if ($user->isAdmin()) {

            if (isset($_POST["closed"]) && $_POST["closed"] == "on" && !$this->isClosed()) {
            
                try {
                
                    $closeThread = $con->prepare("UPDATE `forumthreads` SET `forumthreads`.`closed` = b'1' WHERE `forumthreads`.`id` = :id");
                    $closeThread->bindValue("id", $this->getId(), PDO::PARAM_INT);
                    $closeThread->execute();
                
                } catch (PDOException $e) {
                
                    echo "Failed to close the thread.";
                
                }

            } else if (!isset($_POST["closed"]) && $this->isClosed()) {
            
                try {
                    
                    $openThread = $con->prepare("UPDATE `forumthreads` SET `forumthreads`.`closed` = b'0' WHERE `forumthreads`.`id` = :id");
                    $openThread->bindValue("id", $this->getId(), PDO::PARAM_INT);
                    $openThread->execute();
                
                } catch (PDOException $e) {
                
                    echo "Failed to open the thread.";
                
                }

            }

            if (isset($_POST["delete"]) && $_POST["delete"] == "on") {

                if ($this->map != null) {
                
                    try {
                    
                        $updateMapComments = $con->prepare("UPDATE `maps` SET `maps`.`comments` = 0 WHERE `maps`.`id` = :id");
                        $updateMapComments->bindValue("id", $this->map->getId(), PDO::PARAM_INT);
                        $updateMapComments->execute();
                
                    } catch (PDOException $e) {
                    
                        echo "Failed to update map info.";
                    
                    }

                }
                
                try {    
                
                    $deletePosts = $con->prepare("DELETE FROM `forumposts` WHERE `forumposts`.`threadid` = :tid");
                    $deletePosts->bindValue("tid", $this->id, PDO::PARAM_INT);
                    $deletePosts->execute();

                    $deleteThread = $con->prepare("DELETE FROM `forumthreads` WHERE `forumthreads`.`id` = :tid");
                    $deleteThread->bindValue("tid", $this->id, PDO::PARAM_INT);
                    $deleteThread->execute();

                    if ($deleteThread->rowCount() == 1) {

                        header("Location: /forums");

                    }
                
                } catch (PDOException $e) {
                
                    echo "Failed to delete the thread.";
                
                }

            }

        }

        if ($updateThread->rowCount() == 1) {

            header("Location: /forums/" . $this->id);

        }

    }

    protected function editForm() {

        global $con;
        global $twig;
        global $user;

        $categories = array();
        
        try {   

            $selectCategories = $con->query("SELECT `forumcategories`.`id` FROM `forumcategories` ORDER BY `forumcategories`.`name` ASC");
        
        } catch (PDOException $e) {
        
            die("Failed to select categories.");
        
        }
        
        while ($foundCategory = $selectCategories->fetch()) {

            $cat = new forumcategory($foundCategory["id"]);
            $categories[] = $cat->returnArray();

        }

        echo $twig->render("forum-edit.html", array("categories" => $categories, "currentcategory" => $this->forumcategory->getId(), "ispost" => false, "userisadmin" => $user->isAdmin(), "thread" => $this->returnArray()));

    }

    public function replyCount() {

        global $con;
        
        try {
              
            $selectPosts = $con->prepare("SELECT `forumposts`.`id` FROM `forumposts` WHERE `forumposts`.`threadid` = :id");
            $selectPosts->bindValue("id", $this->id, PDO::PARAM_INT);
            $selectPosts->execute();
        
        } catch (PDOException $e) {
        
            die("Failed to select posts.");
        
        }

        return $selectPosts->rowCount();

    }

    public function isNewsThread() {

        return ($this->news != null);

    }

    public function getNewsStringId() {

        return $this->news->getStringId();

    }

    public function isClosed() {

        return ($this->closed != null);

    }

}
