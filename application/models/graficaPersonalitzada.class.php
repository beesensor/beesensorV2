<?php
Class GraficaPersonalitzada extends Model {
    /**
     * @var int
     */
    public $codi;

    /**
     * @var Tipus
     */
    public $tipus;

    /**
     * @var Explotacio
     */
    public $explotacio;

    /**
     * @var Node|null
     */
    public $node;

    /**
     * @var string
     */
    public $descripcioES;

    /**
     * @var string
     */
    public $descripcioCA;

    /**
     * @var string
     */
    public $descripcioEN;

    /**
     * @var string
     */
    public $tipusGrafica;

    /**
     * @var string
     */
    public $parametreBase;

    /**
     * @var string
     */
    public $parametreValors;

    /**
     * @var int
     */
    public $tipusValors;

     /**
     * @var string
     */
    const cacheKey = "GRAFIQUESPERSONALITZADES";

    public function __construct($codi = "", $tipus = null, $explotacio = null, $node = null, $descripcioES = "", $descripcioCA = "", $descripcioEN = "",
                                $tipusGrafica = "", $parametreBase = "", $parametreValors = "", $tipusValors = 0, $fecAlt = "", $fecMod = "", $fecBaj = "") {
        
        parent::__construct($fecAlt, $fecMod, $fecBaj);
        $this->codi = $codi;
        $this->tipus = $tipus;
        $this->explotacio = $explotacio; 
        $this->node = $node;
        $this->descripcioES = $descripcioES;
        $this->descripcioEN = $descripcioEN;
        $this->descripcioCA = $descripcioCA;
        $this->tipusGrafica = $tipusGrafica;
        $this->parametreBase = $parametreBase;
        $this->parametreValors = $parametreValors;
        $this->tipusValors = $tipusValors;
    }

    public static function get(Db $db, array $redisConfig, int $codi) {
        $cache = new RedisCache(self::cacheKey, $redisConfig);
        $graficaPersonalitzada = $cache->getItemDeserialized($codi, new GraficaPersonalitzada());
        if ($graficaPersonalitzada == null) {
            $sql = "select g.grf_cod, tg.tip_cod, g.exp_cod, g.nod_cod, g.grf_des_es, g.grf_des_ca, g.grf_des_en, g.grf_type, g.par_cod_base, g.par_cod_valores,
                    g.grf_valores_type, g.fec_alt, g.fec_mod, g.fec_baj 
                    from bee_grafica_per g 
                    inner join bee_tipus_grafica_per tg on g.grf_cod=tg.grf_cod and g.exp_cod=tg.exp_cod
                    where grf_cod=:codi";

            $arrValues = array("codi"=>$codi);
            $STH = $db->getInstance()->prepare($sql);
            $STH->execute($arrValues);
            $STH->setFetchMode(PDO::FETCH_OBJ);

            if ($STH->rowCount() == 1) {
                $row = $STH->fetch();
                $graficaPersonalitzada = new GraficaPersonalitzada( $row->grf_cod,
                                                                    Tipus::get($db, $redisConfig, $row->tip_cod),
                                                                    Explotacio::get($db, $redisConfig, $row->exp_cod),
                                                                    Node::get($db, $redisConfig, intval($row->nod_cod)),
                                                                    $row->grf_des_es,
                                                                    $row->grf_des_ca,
                                                                    $row->grf_des_en,
                                                                    $row->grf_type,
                                                                    $row->par_cod_base,
                                                                    $row->par_cod_valores,
                                                                    $row->grf_valores_type,
                                                                    $row->fec_alt,
                                                                    $row->fec_mod,
                                                                    $row->fec_baj);

                $cache->setItem($tipus->codi, $tipus);
                $cache->setCache(24 * 60 * 60);
            }
        }

        return $graficaPersonalitzada;
    }

    public static function all(Db $db, array $redisConfig, bool $deleted = false) : array {
        $cache = new RedisCache(self::cacheKey, $redisConfig);
        if (!$cache->isCompletelyLoaded()) {
            $sql = "select g.grf_cod, tg.tip_cod, g.exp_cod, g.nod_cod, g.grf_des_es, g.grf_des_ca, g.grf_des_en, g.grf_type, g.par_cod_base, g.par_cod_valores,
                    g.grf_valores_type, g.fec_alt, g.fec_mod, g.fec_baj 
                    from bee_grafica_per g 
                    inner join bee_tipus_grafica_per tg on g.grf_cod=tg.grf_cod and g.exp_cod=tg.exp_cod";
            $STH = $db->getInstance()->prepare($sql);
            $STH->execute();
            $STH->setFetchMode(PDO::FETCH_OBJ);

            while ($row = $STH->fetch()) {
                $graficaPersonalitzada = new GraficaPersonalitzada( $row->grf_cod,
                                                                    Tipus::get($db, $redisConfig, $row->tip_cod),
                                                                    Explotacio::get($db, $redisConfig, $row->exp_cod),
                                                                    Node::get($db, $redisConfig, intval($row->nod_cod)),
                                                                    $row->grf_des_es,
                                                                    $row->grf_des_ca,
                                                                    $row->grf_des_en,
                                                                    $row->grf_type,
                                                                    $row->par_cod_base,
                                                                    $row->par_cod_valores,
                                                                    $row->grf_valores_type,
                                                                    $row->fec_alt,
                                                                    $row->fec_mod,
                                                                    $row->fec_baj);
                $cache->setItem($graficaPersonalitzada->codi, $graficaPersonalitzada);
            }
            $cache->setCompletelyLoaded(true, 24 * 60 * 60);
        }

        $arrGrafiquesPersonalitzades = $cache->getDeserialized("GraficaPersonalitzada");

        if (!$deleted) {
            $arrGrafiquesPersonalitzades = array_filter($arrGrafiquesPersonalitzades, function($item){
                if (strtotime($item->fecAlt)<time()) {
                    if (is_null($item->fecBaj) || $item->fecBaj=="") {
                        return true;
                    }
                    if (strtotime($item->fecAlt)>time()) {
                        return true;
                    }
                } 
                return false;
            });
        }

        return $arrGrafiquesPersonalitzades;
    }

    public function insert(Db $db, array $redisConfig) : void {

    }

    public function update(DB $db, array $redisConfig) : void {

    }

    public function delete(Db $db, array $redisConfig) : void {
    
    }
}
