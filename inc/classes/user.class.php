<?php

/**
 * user class
 *
 */
class user {

    /**
     * variables
     *
     * @var $id int
     * @var $name string
     * @var $steamid string
     * @var $admin int
     * @var $cookieh string
     * @var $date object
     */
    protected $id;
    protected $name;
    protected $steamid;
    protected $admin;
    protected $cookieh;
    protected $date;
    protected $twitchname;

    public function __construct($id = null) {

    }

}

?>