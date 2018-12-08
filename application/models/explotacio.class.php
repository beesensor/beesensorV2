<?php
Class Explotacio extends Model {
     /**
     * @var int
     */
    public $codi;

    /**
     * @var Client
     */
    public $client;

     /**
     * @var string
     */
    public $descripcio;

    /**
     * @var string
     */
    public $informacio;

    /**
     * @var float
     */
    public $lat;

    /**
     * @var float
     */
    public $lng;

    /**
     * @var float|null
     */
    public $lz;

    /**
     * @var bool
     */
    public $inici;

    /**
     * @var bool
     */
    public $prediccio;

    /**
     * @var string|null
     */
    public $prediccioCodi;

    /**
     * @var string|null
     */
    public $OMmail;

    /**
     * @var string|null
     */
    public $OMtoken;

    /**
     * @var string
     */
    const cacheKey = "EXPLOTACIONS";

    public function __construct($codi="", $client=null, $descripcio="", $informacio="", $lat=null, $lng=null, $lz=null, $inici=false,
                                $prediccio=false, $prediccioCodi="", $OMmail="", $OMtoken="", $fecAlt=null, $fecMod=null, $fecBaj=null) {
        parent::__construct($fecAlt, $fecMod, $fecBaj);
        $this->codi   = $codi;
        $this->client = $client;
        $this->descripcio = $descripcio;
        $this->informacio = $informacio;
        $this->lat = $lat;
        $this->lng = $lng;
        $this->inici = $inici;
        $this->prediccio = $prediccio;
        $this->prediccioCodi = $prediccioCodi;
        $this->OMmail = $OMmail;
        $this->OMtoken = $OMtoken;
    }

    public static function get(Db $db, array $redisConfig, int $codi) : Explotacio {
        $cache = new RedisCache(self::cacheKey, $redisConfig);
        $explotacio = $cache->getItemDeserialized($codi, new Explotacio());
        if ($explotacio==null) {
            $sql = "select exp_cod, cli_cif, exp_des, exp_info, exp_lat, exp_long, exp_lz, exp_inicio, exp_predict, exp_predictcodi, 
                    exp_ommail, exp_omtoken, fec_alt, fec_mod, fec_baj from bee_expl where exp_codi=:codi";
            $arrValues = array("codi"=>$codi);
            $STH = $db->getInstance()->prepare($sql);
            $STH->execute($arrValues);
            $STH->setFetchMode(PDO::FETCH_OBJ);
            if ($STH->rowCount() == 1) {
                $row = $STH->fetch();
                $explotacio = new Explotacio($row->exp_cod,
                                             Client::get($db, $redisConfig, $row->cli_cif),
                                             $row->exp_des,
                                             $row->exp_info,
                                             $row->exp_lat,
                                             $row->exp_long,
                                             $row->exp_lz,
                                             $row->exp_inicio,
                                             $row->exp_predict,
                                             $row->exp_predictcodi,
                                             $row->exp_ommail,
                                             $row->exp_omtoken,
                                             $row->fec_alt,
                                             $row->fec_mod,
                                             $row->fec_baj);
                $cache->setItem($explotacio->codi, $explotacio);
                $cache->setCache(24 * 60 * 60);
            }
        }

        return $explotacio;
    }
    public static function all(Db $db, array $redisConfig, bool $deleted = false) : array {
        $cache = new RedisCache(self::cacheKey, $redisConfig);
        if (!$cache->isCompletelyLoaded()) {
            $sql = "select exp_cod, cli_cif, exp_des, exp_info, exp_lat, exp_long, exp_lz, exp_inicio, exp_predict, exp_predictcodi, 
                    exp_ommail, exp_omtoken, fec_alt, fec_mod, fec_baj from bee_expl";
            $STH = $db->getInstance()->prepare($sql);
            $STH->execute();
            $STH->setFetchMode(PDO::FETCH_OBJ);
            while ($row = $STH->fetch()) {
                $explotacio = new Explotacio($row->exp_cod,
                                             Client::get($db, $redisConfig, $row->cli_cif),
                                             $row->exp_des,
                                             $row->exp_info,
                                             $row->exp_lat,
                                             $row->exp_long,
                                             $row->exp_lz,
                                             $row->exp_inicio,
                                             $row->exp_predict,
                                             $row->exp_predictcodi,
                                             $row->exp_ommail,
                                             $row->exp_omtoken,
                                             $row->fec_alt,
                                             $row->fec_mod,
                                             $row->fec_baj);
                $cache->setItem($explotacio->codi, $explotacio);
            }
            $cache->setCompletelyLoaded(true, 24 * 60 * 60);
        }
        $arrExplotacions = $cache->getDeserialized("Explotacio");
        
        if (!$deleted) {
            $arrExplotacions = array_filter($arrExplotacions, function($item){
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
        
        return $arrExplotacions;
    }

    public function insert(Db $db, array $redisConfig) : void {
        $sql = "insert into bee_expl (cli_cif, exp_des, exp_info, exp_lat, exp_long, exp_lz, exp_inicio, exp_predict, exp_predictcodi, exp_ommail, exp_omtoken, fec_alt, fec_mod)
                            values (:cif, :des, :info, :lat, :lng, :lz, :inicio, :predict, :predictCodi, :ommail, :omtoken, :fecAlt, :fecMod)";
        $arrValues = array( "cif"=>$this->client->cif,
                            "des"=>$this->descripcio,
                            "info"=>$this->informacio,
                            "lat" =>$this->lat,
                            "lng"=>$this->lng,
                            "lz"=>$this->lz,
                            "inicio"=>$this->inici,
                            "predict"=>$this->prediccio,
                            "predictCodi"=>$this->prediccioCodi,
                            "ommail" =>$this->OMmail,
                            "omtoken"=>$this->OMtoken,
                            "fecAlt"=>date("Y-m-d"),
                            "fecMod"=>date("Y-m-d"));
        $STH = $db->getInstance()->prepare($sql);
        $STH->execute($arrValues);
        $this->codi = $db->getInstance()->lastInsertId();
        $this->setActualDates(true);
        $cache = new RedisCache(self::cacheKey, $redisConfig);
        $cache->setItem($this->codi, $this);
        $cache->setCache(24 * 60 * 60);                    
    }

    public function update(DB $db, array $redisConfig) : void {
        $sql = "update bee_expl set cli_cif=:cif, exp_des=:des, exp_info=:info, exp_lat=:lat, exp_long=:lng, exp_lz=:lz, exp_inicio=:inicio, exp_predict=:predict, 
                exp_predictcodi=:predictCodi, exp_ommail=:ommail, exp_omtoken=:omtoken, fec_mod=:fecMod where exp_codi=:codi";
        $arrValues = array( "codi"=>$this->codi,
                            "cif"=>$this->client->cif,
                            "des"=>$this->descripcio,
                            "info"=>$this->informacio,
                            "lat" =>$this->lat,
                            "lng"=>$this->lng,
                            "lz"=>$this->lz,
                            "inicio"=>$this->inici,
                            "predict"=>$this->prediccio,
                            "predictCodi"=>$this->prediccioCodi,
                            "ommail" =>$this->OMmail,
                            "omtoken"=>$this->OMtoken,
                            "fecMod"=>date("Y-m-d"));
        $STH = $db->getInstance()->prepare($sql);
        $STH->execute($arrValues);
        $this->setActualDates(false);
        $cache = new RedisCache(self::cacheKey, $redisConfig);
        $cache->setItem($this->codi, $this);
        $cache->setCache(24 * 60 * 60);     
    }

    public function delete(Db $db, array $redisConfig) : void {
        $sql="udpate bee_expl set fec_baj=:fecBaj where exp_codi = :codi";
        $arrValues = array("fecBaj"=>date("Y-m-d"),
                           "codi"=>$this->codi);

        $STH = $db->getInstance()->prepare($sql);
        $STH->execute($arrValues);
        $this->setDeletionDate();
        $cache = new RedisCache(self::cacheKey, $redisConfig);
        $cache->setItem($this->codi, $this);
        $cache->setCache(24 * 60 * 60);  
    }
}