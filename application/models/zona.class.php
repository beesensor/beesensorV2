<?php
Class Zona extends Model {
    /**
     * @var int
     */
    public $codi;

    /**
     * @var Explotacio
     */
    public $explotacio;

    /**
     * @var string
     */
    public $descripcio;

    /**
     * @var string
     */
    const cacheKey = "ZONES";

    public function __construct($codi=null, $explotatio=null, $descripcio="", $fecAlt = null, $fecMod = null, $fecBaj = null) {
        parent::__construct($fecAlt, $fecMod, $fecBaj);
        $this->codi = $codi;
        $this->explotacio = $explotacio;
        $this->descripcio = $descripcio;
    }

    public static function get(Db $db, array $redisConfig, int $codi) : Zona {
        $cache = new RedisCache(self::cacheKey, $redisConfig);
        $zona = $cache->getItemDeserialized($codi, new Zona());
        if ($zona==null) {
            $sql = "select zon_cod, exp_cod, zon_des, fec_alt, fec_mod, fec_baj from bee_zon where zon_cod=:codi";
            $arrValues = array("codi"=>$codi);
            $STH = $db->getInstance()->prepare($sql);
            $STH->execute($arrValues);
            $STH->setFetchMode(PDO::FETCH_OBJ);
            if ($STH->rowCount() == 1) {
                $row = $STH->fetch();
                $zona = new Zona($row->zon_cod,
                                 Explotacio::get($db, $redisConfig, $row->exp_cod),
                                 $row->zon_des,
                                 $row->fec_alt,
                                 $row->fec_mod,
                                 $row->fec_baj);
                $cache->setItem($zona->codi, $zona);
                $cache->setCache(24 * 60 * 60);
            }
        }
        return $zona;
    }

    public static function all(Db $db, array $redisConfig, bool $deleted = false) : array {
        $cache = new RedisCache(self::cacheKey, $redisConfig);
        if (!$cache->isCompletelyLoaded()) {
            $sql = "select zon_cod, exp_cod, zon_des, fec_alt, fec_mod, fec_baj from bee_zon";
            $STH = $db->getInstance()->prepare($sql);
            $STH->execute();
            $STH->setFetchMode(PDO::FETCH_OBJ);
            while ($row = $STH->fetch()) {
                $zona = new Zona($row->zon_cod,
                                 Explotacio::get($db, $redisConfig, $row->exp_cod),
                                 $row->zon_des,
                                 $row->fec_alt,
                                 $row->fec_mod,
                                 $row->fec_baj);
                $cache->setItem($zona->codi, $zona);
            }
            $cache->setCompletelyLoaded(true, 24 * 60 * 60);
        }
        $arrZones = $cache->getDeserialized("Zona");

        if (!$deleted) {
            $arrZones = array_filter($arrZones, function($item){
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

        return $arrZones;
    }

    public function insert(Db $db, array $redisConfig) : void {
        $sql = "insert into bee_zon (exp_cod, zon_des, fec_alt, fec_mod) values (:expCod, :zonDes, :fecAlt, :fecMod)";
        $arrValues = array( "expCod"=>$this->explotacio->codi,
                            "zonDes"=>$this->descripcio,
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

    public function update(Db $db, array $redisConfig) : void {
        $sql = "update bee_zon set exp_cod=:expCod, zon_des=:zonDes, fec_mod=:fecMod where zon_cod=:zonCod";
        $arrValues = array( "zonCod"=>$this->codi,
                            "expCod"=>$this->explotacio->codi,
                            "zonDes"=>$this->descripcio,
                            "fecMod"=>date("Y-m-d"));
        $STH = $db->getInstance()->prepare($sql);
        $STH->execute($arrValues);
        $this->setActualDates(false);
        $cache = new RedisCache(self::cacheKey, $redisConfig);
        $cache->setItem($this->codi, $this);
        $cache->setCache(24 * 60 * 60);        
    }

    public function delete(Db $db, array $redisConfig) : void {
        $sql="udpate bee_zon set fec_baj=:fecBaj where zon_cod = :codi";
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