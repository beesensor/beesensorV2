<?php
Class Node extends Model {
    /**
     * @var string
     */
    public $codi;

    /**
     * @var Explotacio
     */
    public $explotacio;

    /**
     * @var Zona|null
     */
    public $zona;

    /**
     * @var string
     */
    public $descripcio;

    /**
     * @var string
     */
    public $descripcioLlarga;

    /**
     * @var bool
     */
    public $actiu;

    /**
     * @var float
     */
    public $lat;

    /**
     * @var float
     */
    public $lng;

    /**
     * @var float
     */
    public $alt;

    /**
     * @var int
     */
    public $alarm;

    /**
     * @var string
     */
    public $alarmTime;

    /**
     * @var bool
     */
    public $gps;

    /**
     * @var float|null
     */
    public $gpsDistanciaMaxima;

    /**
     * @var string
     */
    public $apiKey;

    /**
     * @var bool
     */
    public $inhibit;

    /**
     * @var bool
     */
    public $previsio;

    /**
     * @var int
     */
    public $sleep;

    /**
     * @var string|null
     */
    public $OMMail;

    /**
     * @var string|null
     */
    public $OMToken;

    /**
     * @var string|null
     */
    public $hereAppId;

    /**
     * @var string|null
     */
    public $hereAppToken;

    /**
     * @var string
     */
    const cacheKey = "NODES";

    public function __construct($codi="", $explotacio=null, $zona=null, $descripcio="", $descripcioLlarga="", $actiu=false, $lat=0, $lng=0, $alt=0, $alarm=0, $alarmTime="",
                                $gps=false, $gpsDistanciaMaxima=0, $apiKey="", $inhibit=false, $previsio=false, $sleep=0, $OMMail="", $OMToken="", $hereAppId="", $hereAppToken="",
                                $fecAlt="", $fecMod="", $fecBaj="") {
                                    
        parent::__construct($fecAlt, $fecMod, $fecBaj);
        $this->codi = $codi;
        $this->explotacio = $explotacio;
        $this->zona = $zona;
        $this->descripcio = $descripcio;
        $this->descripcioLlarga = $descripcioLlarga;
        $this->actiu = $actiu;
        $this->lat = $lat;
        $this->lng = $lng;
        $this->alt = $alt;
        $this->alarm = $alarm;
        $this->alarmTime = $alarmTime;
        $this->gps = $gps;
        $this->gpsDistanciaMaxima = $gpsDistanciaMaxima;
        $this->apiKey = $apiKey;
        $this->inhibit = $inhibit;
        $this->previsio = $previsio;
        $this->sleep = $sleep;
        $this->OMMail = $OMMail;
        $this->OMToken = $OMToken;
        $this->hereAppId = $hereAppId;
        $this->hereAppToken = $hereAppToken;
    }

    public static function get(Db $db, array $redisConfig, string $codi) {
        $cache = new RedisCache(self::cacheKey, $redisConfig);
        $node = $cache->getItemDeserialized($codi, new Node());
        if ($node==null) {
            $sql = "SELECT nod_cod, exp_cod, zon_cod, nod_des, nod_deslng, nod_act, nod_lat, nod_long, nod_alt, nod_alarm, nod_alarmtime, nod_gps, nod_gps_dist_max,
                    nod_api_key, nod_inhibit, nod_previsio, nod_sleep, nod_ommail, nod_omtoken, nod_here_appid, nod_here_appcode, fec_alt, fec_mod, fec_baj 
                    FROM bee_nodos where nod_cod=:codi";
            $arrValues = array("codi"=>$codi);
            $STH = $db->getInstance()->prepare($sql);
            $STH->execute($arrValues);
            $STH->setFetchMode(PDO::FETCH_OBJ);

            if ($STH->rowCount() == 1) {
                $row = $STH->fetch();
                $zona = null;
                if($row->zon_cod!=null && $row->zon_cod!="") {
                    $zona = Zona::get($db, $redisConfig, $row->zon_cod);
                }

                $node = new Node($row->nod_cod,
                                 Explotacio::get($db, $redisConfig, $row->exp_cod),
                                 $zona,
                                 $row->nod_des,
                                 $row->nod_deslng,
                                 $row->nod_act,
                                 $row->nod_lat,
                                 $row->nod_long,
                                 $row->nod_alt,
                                 $row->nod_alarm,
                                 $row->nod_alarmtime,
                                 $row->nod_gps,
                                 $row->nod_gps_dist_max,
                                 $row->nod_api_key,
                                 $row->nod_inhibit,
                                 $row->nod_previsio,
                                 $row->nod_sleep,
                                 $row->nod_ommail,
                                 $row->nod_omtoken,
                                 $row->nod_here_appid,
                                 $row->nod_here_appcode,
                                 $row->fec_alt,
                                 $row->fec_mod,
                                 $row->fec_baj);

                $cache->setItem($node->codi, $node);
                $cache->setCache(24 * 60 * 60);
            }
        }
        return $node;
    }

    public static function all(Db $db, array $redisConfig, bool $deleted = false) : array {
        $cache = new RedisCache(self::cacheKey, $redisConfig);
        if (!$cache->isCompletelyLoaded()) {
            $sql = "SELECT nod_cod, exp_cod, zon_cod, nod_des, nod_deslng, nod_act, nod_lat, nod_long, nod_alt, nod_alarm, nod_alarmtime, nod_gps, nod_gps_dist_max,
            nod_api_key, nod_inhibit, nod_previsio, nod_sleep, nod_ommail, nod_omtoken, nod_here_appid, nod_here_appcode, fec_alt, fec_mod, fec_baj 
            FROM bee_nodos";
            $STH = $db->getInstance()->prepare($sql);
            $STH->execute();
            $STH->setFetchMode(PDO::FETCH_OBJ);
            while ($row = $STH->fetch()) {
                $zona = null;
                if($row->zon_cod!=null && $row->zon_cod!="") {
                    $zona = Zona::get($db, $redisConfig, $row->zon_cod);
                }

                $node = new Node($row->nod_cod,
                                 Explotacio::get($db, $redisConfig, $row->exp_cod),
                                 $zona,
                                 $row->nod_des,
                                 $row->nod_deslng,
                                 $row->nod_act,
                                 $row->nod_lat,
                                 $row->nod_long,
                                 $row->nod_alt,
                                 $row->nod_alarm,
                                 $row->nod_alarmtime,
                                 $row->nod_gps,
                                 $row->nod_gps_dist_max,
                                 $row->nod_api_key,
                                 $row->nod_inhibit,
                                 $row->nod_previsio,
                                 $row->nod_sleep,
                                 $row->nod_ommail,
                                 $row->nod_omtoken,
                                 $row->nod_here_appid,
                                 $row->nod_here_appcode,
                                 $row->fec_alt,
                                 $row->fec_mod,
                                 $row->fec_baj);

                $cache->setItem($node->codi, $node);
            }
            $cache->setCompletelyLoaded(true, 24 * 60 * 60);
        }
        $arrNodes = $cache->getDeserialized("Node");

        if (!$deleted) {
            $arrNodes = array_filter($arrNodes, function($item){
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

        return $arrNodes;
    }

    public function insert(Db $db, array $redisConfig) : void {

    }

    public function update(DB $db, array $redisConfig) : void {

    }

    public function delete(Db $db, array $redisConfig) : void {
        
    }
}