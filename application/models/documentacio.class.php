<?php
Class Documentacio extends Model {
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
    public $descripcio;

    /**
     * @var string
     */
    public $url;

    /**
     * @var string
     */
    const cacheKey = "DOCUMENTACIONS";

    public function __construct($codi="", $explotacio=null, $descripcio="", $url="", $fecAlt=null, $fecMod=null, $fecBaj=null) {
        parent::__construct($fecAlt, $fecMod, $fecBaj);
        $this->codi = $codi;
        $this->explotacio = $explotacio;
        $this->descripcio = $descripcio;
        $this->url = $url;
    }

    public static function get(Db $db, array $redisConfig, int $codi) {
        $cache = new RedisCache(self::cacheKey, $redisConfig);
        $documentacio = $cache->getItemDeserialized($codi, new Documentacio());

        if ($documentacio==null) {
            $sql = "select doc_cod, exp_cod, doc_des, doc_url, fec_alt, fec_mod, fec_baj from bee_docugen where doc_cod=:codi";
            $arrValues = array("codi"=>$codi);
            $STH = $db->getInstance()->prepare($sql);
            $STH->execute($arrValues);
            $STH->setFetchMode(PDO::FETCH_OBJ);

            if ($STH->rowCount() == 1) {
                $row = $STH->fetch();

                $explotacio = null;
                if ($row->exp_cod!=null && $row->exp_cod!="") {
                    $explotacio = Explotacio::get($db, $redisConfig, $row->exp_cod);
                }

                $documentacio = new Documentacio($row->doc_cod,
                                                 $explotacio,
                                                 $row->doc_des,
                                                 $row->doc_url,
                                                 $row->fec_alt,
                                                 $row->fec_mod,
                                                 $row->fec_baj);

                $cache->setItem($documentacio->codi, $documentacio);
                $cache->setCache(24 * 60 * 60);
            }
        }

        return $documentacio;
    }

    public static function all(Db $db, array $redisConfig, bool $deleted = false) : array {
        $cache = new RedisCache(self::cacheKey, $redisConfig);
        if (!$cache->isCompletelyLoaded()) {
            $sql = "select doc_cod, exp_cod, doc_des, doc_url, fec_alt, fec_mod, fec_baj from bee_docugen";
            $STH = $db->getInstance()->prepare($sql);
            $STH->execute();
            $STH->setFetchMode(PDO::FETCH_OBJ);
            while ($row = $STH->fetch()) {
                
                $explotacio = null;
                if ($row->exp_cod!=null && $row->exp_cod!="") {
                    $explotacio = Explotacio::get($db, $redisConfig, $row->exp_cod);
                }

                $documentacio = new Documentacio($row->doc_cod,
                                                 $explotacio,
                                                 $row->doc_des,
                                                 $row->doc_url,
                                                 $row->fec_alt,
                                                 $row->fec_mod,
                                                 $row->fec_baj);

                $cache->setItem($documentacio->codi, $documentacio);
            }
            $cache->setCompletelyLoaded(true, 24 * 60 * 60);
        }
        $arrDocumentacions = $cache->getDeserialized("Documentacio");

        if (!$deleted) {
            $arrDocumentacions = array_filter($arrDocumentacions, function($item){
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

        return $arrDocumentacions;
    }

    public function insert(Db $db, array $redisConfig) : void {

    }

    public function update(DB $db, array $redisConfig) : void {

    }

    public function delete(Db $db, array $redisConfig) : void {
    
    }
}