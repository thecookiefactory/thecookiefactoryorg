<?php

/**
 * map class
 *
 */
class map {

    /**
     * variables
     *
     * @var $id int
     * @var $name string
     * @var $text string
     * @var $author object
     * @var $date string
     * @var $editdate string
     * @var $dl string
     * @var $extension string
     * @var $comments int
     * @var $game object
     * @var $link string
     * @var $downloadcount int
     */
    protected $id;
    protected $name;
    protected $text;
    protected $author;
    protected $date;
    protected $editdate;
    protected $dl;
    protected $extension;
    protected $comments;
    protected $game;
    protected $link;
    protected $downloadcount;

    public function __construct($id = null) {

    }

}

?>