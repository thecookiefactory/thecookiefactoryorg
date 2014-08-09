<?php

if (!isset($r_c)) header("Location: /notfound.php");

/**
 * master class
 *
 * function getId
 *
 * function isReal
 */
class master {

    /**
     * variables
     *
     * @var $id int
     */
    protected $id = null;

    public function getId() {

        return $this->id;

    }

    public function isReal() {

        return ($this->id != null);

    }

}
