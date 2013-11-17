<?php

class master {

    protected $id = null;

    public function getId() {

        return $this->id;

    }

    public function isReal() {

        return ($this->id != null);

    }

}