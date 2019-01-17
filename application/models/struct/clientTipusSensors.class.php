<?php
Class ClientTipusSensors {
    /**
     * @var string
     */
    public $clicif;

    /**
     * @var TipusSensor[]|null
     */
    public $arrTipusSensors;

    public function __construct($clicif = "") {
        $this->clicif = $clicif;
    }

    public function addTipusSensor($tipusSensor) {
        if (is_null($this->arrTipusSensors)) {
            $this->arrTipusSensors = array();
        }
        $this->arrTipusSensors[] = $tipusSensor;
    }
}