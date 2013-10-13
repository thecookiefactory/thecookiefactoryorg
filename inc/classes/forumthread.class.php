<?php

/**
 * forum thread class
 *
 */
class forumthread {

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
    protected $id;
    protected $title;
    protected $text;
    protected $author;
    protected $date;
    protected $editdate;
    protected $lastdate;
    protected $forumcategory;
    protected $map;
    protected $news;
    protected $closed;

    public function __construct($id = null) {

    }

}

?>