<?php
Class Grafica extends Model {
    /**
     * @var int
     */
    public $codi;

    /**
     * @var Explotacio|null
     */
    public $explotacio;

    /**
     * @var string
     */
    public $usumail;

    /**
     * @var int
     */
    public $tipus;

    /**
     * @var string
     */
    public $nom;

    /**
     * @var string
     */
    public $data;

    /**
     * @var string
     */
    const cacheKey = "GRAFIQUES";

    public function __construct($codi="", $explotacio=null, $usumail="", $tipus=0, $nom="", $data="",  $fecAlt=null, $fecMod=null, $fecBaj=null) {
        parent::__construct($fecAlt, $fecMod, $fecBaj);
        $this->codi = $codi;
        $this->explotacio = $explotacio;
        $this->usumail = $usumail;
        $this->tipus = $tipus;
        $this->nom = $nom;
        $this->data = $data;
    }

    public static function get(Db $db, array $redisConfig, int $codi) {
        $cache = new RedisCache(self::cacheKey, $redisConfig);
        $grafica = $cache->getItemDeserialized($codi, new Grafica());

        if ($grafica==null) {
            $sql = "select grs_cod, exp_cod, usu_mail, tip_cod, grs_nom, grs_data, fec_alt, fec_mod, fec_baj from bee_grafica_save where grs_cod=:codi";
            $arrValues = array("codi"=>$codi);
            $STH = $db->getInstance()->prepare($sql);
            $STH->execute($arrValues);
            $STH->setFetchMode(PDO::FETCH_OBJ);

            if ($STH->rowCount() == 1) {
                $row = $STH->fetch();
                $grafica = new Grafica( $row->grs_cod,
                                        Explotacio::get($db, $redisConfig, $row->exp_cod),
                                        $row->usu_mail,
                                        $row->tip_cod,
                                        $row->grs_nom,
                                        $row->grs_data,
                                        $row->fec_alt,
                                        $row->fec_mod,
                                        $row->fec_baj);

                $cache->setItem($grafica->codi, $grafica);
                $cache->setCache(24 * 60 * 60);
            }
        }

        return $grafica;
    }

    public static function all(Db $db, array $redisConfig, bool $deleted = false) : array {
        $cache = new RedisCache(self::cacheKey, $redisConfig);
        if (!$cache->isCompletelyLoaded()) {
            $sql = "select grs_cod, exp_cod, usu_mail, tip_cod, grs_nom, grs_data, fec_alt, fec_mod, fec_baj from bee_grafica_save";
            $STH = $db->getInstance()->prepare($sql);
            $STH->execute();
            $STH->setFetchMode(PDO::FETCH_OBJ);

            while ($row = $STH->fetch()) {
                $grafica = new Grafica( $row->grs_cod,
                                        Explotacio::get($db, $redisConfig, $row->exp_cod),
                                        $row->usu_mail,
                                        $row->tip_cod,
                                        $row->grs_nom,
                                        $row->grs_data,
                                        $row->fec_alt,
                                        $row->fec_mod,
                                        $row->fec_baj);

                $cache->setItem($grafica->codi, $grafica);
            }

            $cache->setCompletelyLoaded(true, 24 * 60 * 60);
        }

        $arrGrafiques = $cache->getDeserialized("Grafica");

        if (!$deleted) {
            $arrGrafiques = array_filter($arrGrafiques, function($item){
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

        return $arrGrafiques;
    }

    public function insert(Db $db, array $redisConfig) : void {

    }

    public function update(DB $db, array $redisConfig) : void {

    }

    public function delete(Db $db, array $redisConfig) : void {
    
    }
}