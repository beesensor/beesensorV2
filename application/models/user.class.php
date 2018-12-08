<?php
Class User {
    /**
     * @var string
     */
    public $userName;

    /**
     * @var string
     */
    public $password;

    /**
     * @var Adress
     */
    public $adress;

    public function __construct($userName = "", $password = "", $adress = null) {
        $this->userName = $userName;
        $this->password = $password;
        $this->adress = $adress;
    }
}