<?php
Class Adress {
    /**
     * @var string
     */
    public $carrer;

    /**
     * @var string
     */
    public $ciutat;

    public function __construct($carrer="",$ciutat="") {
        $this->carrer = $carrer;
        $this->ciutat = $ciutat;
    }

}