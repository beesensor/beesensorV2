<?php
Class Alarma extends Model {
    /**
     * @var int
     */
    public $codi;

    /**
     * @var int|null
     */
    public $codiExplotacio;

    /**
     * @var string|null
     */
    public $codiNode;

    /**
     * @var int|null
     */
    public $codiPal;

    /**
     * @var string|null
     */
    public $usuMail;

    /**
     * @var int
     */
    public $tipus;

    /**
     * @var int
     */
    public $status;

    /**
     * @var float|null
     */
    public $lec1;

    /**
     * @var float|null
     */
    public $lec2;

    /**
     * @var string|null
     */
    public $dataAlarma;

    public function __construct($codi = 0, $codiExplotacio = 0, $codiNode = "", $codiPal = 0, $usuMail = "", $tipus = 0, $status = 0, $lec1 = null, $lec2 = null, 
                                $dataAlarma = "", $fecAlt = "", $fecMod = "", $fecBaj = "") {
        parent::__construct($fecAlt, $fecMod, $fecBaj);
        $this->codi = $codi;
        $this->codiExplotacio = $codiExplotacio;
        $this->codiNode = $codiNode;
        $this->codiPal = $codiPal;
        $this->usuMail = $usuMail;
        $this->tipus = $tipus;
        $this->status = $status;
        $this->lec1 = $lec1;
        $this->lec2 = $lec2;
        $this->dataAlarma = $dataAlarma;
    }

    public function insert(Db $db, array $redisConfig) {}

    public function update(Db $db, array $redisConfig) {}

    public function delete(Db $db, array $redisConfig) {}
}