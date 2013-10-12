<?php

/**
 * news class
 *
 */
class news {

    /**
     * variables
     *
     * @var $id int
     * @var $title string
     * @var $text string
     * @var $author object
     * @var $date string
     * @var $editor object
     * @var $editdate string
     * @var $comments int
     * @var $live int
     * @var $stringid string
     */
    protected $id;
    protected $title;
    protected $text;
    protected $author;
    protected $date;
    protected $editor;
    protected $editdate;
    protected $comments;
    protected $live;
    protected $stringid;

    public function __construct($id = null) {

    }

}

?>