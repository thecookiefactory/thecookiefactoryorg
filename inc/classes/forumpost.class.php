<?php

/**
 * forum post class
 *
 */
class forumpost {

    /**
     * variables
     *
     * @var $id int
     * @var $text string
     * @var $author object
     * @var $date string
     * @var $editdate string
     * @var $thread object
     */
    protected $id;
    protected $text;
    protected $author;
    protected $date;
    protected $editdate;
    protected $thread;

    public function __construct($id = null) {

    }

}

?>