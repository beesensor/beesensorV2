<?php
Class Lectura extends Model {
    /**
     * @var int
     */
    public $codi;

    /**
     * @var string
     */
    public $clicif;

    /**
     * @var int
     */
    public $codiExplotacio;

    /**
     * @var string
     */
    public $codiNode;

    /**
     * @var string
     */
    public $codiSensor;

    /**
     * @var float|null
     */
    public $valor;

    /**
     * @var float|null
     */
    public $valorCalculat;

    /**
     * @var float|null
     */
    public $offset;

    /**
     * @var string|null
     */
    public $tempsLectura;

    /**
     * @var float|null
     */
    public $lat;

    /**
     * @var float|null
     */
    public $lng;

    /**
     * @var float|null
     */
    public $latCalculat;

    /**
     * @var float|null
     */
    public $lngCalculat;

    /**
     * @var int|null
     */
    public $agrupacio;

    public function __construct($codi = 0, $clicif = "", $codiExplotacio = 0, $codiNode = "", $codiSensor = "", $valor = null, $valorCalculat = null, $offset = null,
                                $tempsLectura = null, $lat = null, $lng = null, $latCalculat = null, $lngCalculat = null, $agrupacio = null) {
        parent::__construct(null, null, null);
        $this->codi = $codi;
        $this->clicif = $clicif;
        $this->codiExplotacio = $codiExplotacio;
        $this->codiNode = $codiNode;
        $this->codiSensor = $codiSensor;
        $this->valor = $valor;
        $this->valorCalculat = $valorCalculat;
        $this->offset = $offset;
        $this->tempsLectura = $tempsLectura;
        $this->lat = $lat;
        $this->lng = $lng;
        $this->latCalculat = $latCalculat;
        $this->lngCalculat = $lngCalculat;
        $this->agrupacio = $agrupacio;
    }

    public function insert(Db $db, array $redisConfig) : void {

    }

    public function update(DB $db, array $redisConfig) : void {

    }

    public function delete(Db $db, array $redisConfig) : void {
    
    }
}