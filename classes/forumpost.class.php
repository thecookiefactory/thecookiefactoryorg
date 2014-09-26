<?php

if (!isset($r_c)) header('Location: /notfound.php');

require_once str_repeat('../', $r_c) . 'classes/dtime.class.php';
require_once str_repeat('../', $r_c) . 'classes/user.class.php';

class forumpost extends master {

    protected $id       = null;
    protected $text     = null;
    protected $author   = null;
    protected $date     = null;
    protected $editdate = null;
    protected $threadid = null;

    public function __construct($id = null) {

        global $con;

        if ($id != null) {

            try {

                $getPostData = $con->prepare('
                    SELECT *
                    FROM `forumposts`
                    WHERE `forumposts`.`id` = :id
                ');
                $getPostData->bindValue('id', $id, PDO::PARAM_INT);
                $getPostData->execute();

            } catch (PDOException $e) {

                die('Could not get data for the post.');

            }

            if ($getPostData->rowCount() == 1) {

                $postData = $getPostData->fetch();

                $this->id       = $postData['id'];
                $this->text     = $postData['text'];
                $this->author   = new user($postData['authorid']);
                $this->date     = new dtime($postData['date']);
                $this->editdate = ($postData['editdate'] != null) ? new dtime($postData['editdate']) : null;
                $this->threadid = $postData['threadid'];

            } else {

                echo 'Could not find a post with the given id.';

            }

        }

    }
    
    public function create() {
    
    }
    
    public function update() {
    
    }
    
    public function delete() {
    
    }

    public function edit() {

        global $user;

        $thread = new forumthread($this->threadid);

        if (($this->author->getId() != $user->getId() || $thread->isClosed()) && !$user->isAdmin()) {

            die('403');

        } else {

            if (isset($_POST['edit']) && (isset($_POST['text']) && validField($_POST['text']))) {

                $this->editProcess();

            } else {

                $this->editForm();

            }

        }

    }

    protected function editProcess() {

        global $con;
        global $user;

        if ($user->isAdmin() && isset($_POST['delete']) && $_POST['delete'] == 'on') {

            try {

                $deletePost = $con->prepare('
                    DELETE FROM `forumposts`
                    WHERE `forumposts`.`id` = :postid
                ');
                $deletePost->bindValue('postid', $this->id, PDO::PARAM_INT);
                $deletePost->execute();

                $thread = new forumthread($this->threadid);

                if (!$thread->isNewsThread()) {

                    header('Location: /forums/' . $this->threadid);

                } else {

                    header('Location: /news/' . $thread->getNewsStringId());

                }

            } catch (PDOException $e) {

                echo 'An error occurred while trying to delete the post.';

            }

        } else {

            $text = strip($_POST['text']);

            if (strlen($text) > 20000) {

                echo 'Your comment must be less than 20 000 characters long.';

            } else {

                try {

                    $updatePost = $con->prepare('
                        UPDATE `forumposts`
                        SET `forumposts`.`text` = :text
                        WHERE `forumposts`.`id` = :postid
                    ');
                    $updatePost->bindValue('text', $text, PDO::PARAM_STR);
                    $updatePost->bindValue('postid', $this->id, PDO::PARAM_INT);
                    $updatePost->execute();

                    $thread = new forumthread($this->threadid);

                    if (!$thread->isNewsThread()) {

                        header('Location: /forums/' . $this->threadid);

                    } else {

                        header('Location: /news/' . $thread->getNewsStringId());

                    }

                } catch (PDOException $e) {

                    echo 'An error occurred while trying to update the post.';

                }

            }

        }

    }

    protected function editForm() {

        global $index_var;
        global $twig;
        global $user;

        echo $twig->render(
            'forum-edit.html',
            array(
                'index_var' => $index_var,
                'ispost' => true,
                'userisadmin' => $user->isAdmin(),
                'post' => $this->returnArray(),
                'thread' => array('id' => $this->threadid)
            )
        );

    }

    public function returnArray($loc = null) {

        global $user;

        $thread = new forumthread($this->threadid);

        $returnee = array(
            'id' => $this->id,
            'text' => $this->text,
            'author' => $this->author->getName(),
            'date' => $this->date->display(),
            'editdate' => 0,
            'userhasrights' => 0
        );

        if ($loc == 'main')
            $returnee['text'] = tformat($this->text);

        if ($this->editdate != null)
            $returnee['editdate'] = $this->editdate->display();

        if (($user->isReal() && $this->author->getId() == $user->getId() && !$thread->isClosed()) || $user->isAdmin())
            $returnee['userhasrights'] = 1;

        return $returnee;

    }

}
