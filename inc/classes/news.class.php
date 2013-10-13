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
     * @var $date object
     * @var $editor object
     * @var $editdate object
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