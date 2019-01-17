<?php
Class Tipus extends Model {
    /**
     * @var int
     */
    public $codi;

    /**
     * @var string
     */
    public $descEs;

    /**
     * @var string
     */
    public $descCa;

    /**
     * @var string
     */
    public $descEn;

    /**
     * @var bool
     */
    public $admin;

    /**
     * @var bool
     */
    public $menu;

    /**
     * @var bool
     */
    public $lectures;

    /**
     * @var bool
     */
    public $globus;

    /**
     * @var bool
     */
    public $prediccio;

    /**
     * @var bool
     */
    public $fotoperiode;

    /**
     * @var bool
     */
    public $inttermica;

    /**
     * @var bool
     */
    public $horesfred;

    /**
     * @var bool
     */
    public $climaVid;

    /**
     * @var string
     */
    public $clicif;

    /**
     * @var string|null
     */
    public $descripcio;

    /**
     * @var string
     */
    const cacheKey = "TIPUS";

    public function __construct($codi="", $descEs="", $descCa="", $descEn="", $admin=false, $menu=false, $lectures=false, $globus=false, $prediccio=false, 
                                $fotoperiode=false, $inttermica=false, $horesfred=false, $climaVid=false, $clicif="", $fecAlt="", $fecMod="", $fecBaj="") {
        parent::__construct($fecAlt, $fecMod, $fecBaj);
        $this->codi = $codi;
        $this->descEs = $descEs;
        $this->descCa = $descCa;
        $this->descEn = $descEn;
        $this->admin = $admin;
        $this->menu = $menu;
        $this->lectures = $lectures;
        $this->globus = $globus;
        $this->prediccio = $prediccio;
        $this->fotoperiode = $fotoperiode;
        $this->inttermica = $inttermica;
        $this->horesfred = $horesfred;
        $this->climaVid = $climaVid;
        $this->clicif = $clicif;
    }

    public static function get(Db $db, array $redisConfig, int $codi) {
        $cache = new RedisCache(self::cacheKey, $redisConfig);
        $tipus = $cache->getItemDeserialized($codi, new Tipus());
        if ($tipus==null) {
            $sql = "select tip_cod, tip_des_es, tip_des_ca, tip_des_en, tip_admin, tip_menu, tip_lecturas, tip_globus, tip_prediccion, tip_fotoperiodo, tip_inttermica, 
                    tip_horesfrio, tip_climaVid, fec_alt, fec_mod, fec_baj, cli_cif from bee_tipus where tip_cod=:codi";
            $arrValues = array("codi"=>$codi);
            $STH = $db->getInstance()->prepare($sql);
            $STH->execute($arrValues);
            $STH->setFetchMode(PDO::FETCH_OBJ);

            if ($STH->rowCount() == 1) {
                $row = $STH->fetch();
                $tipus = new Tipus($row->tip_cod,
                                $row->tip_des_es,
                                $row->tip_des_ca,
                                $row->tip_des_en,
                                $row->tip_admin,
                                $row->tip_menu,
                                $row->tip_lecturas,
                                $row->tip_globus,
                                $row->tip_prediccion,
                                $row->tip_fotoperiodo,
                                $row->tip_inttermica,
                                $row->tip_horesfrio,
                                $row->tip_climaVid,
                                $row->cli_cif,
                                $row->fec_alt,
                                $row->fec_mod,
                                $row->fec_baj);

                $cache->setItem($tipus->codi, $tipus);
                $cache->setCache(24 * 60 * 60);
            }
        }

        return $tipus;
    }

    public static function all(Db $db, array $redisConfig, bool $deleted = false) : array {
        $cache = new RedisCache(self::cacheKey, $redisConfig);
        if (!$cache->isCompletelyLoaded()) {
            $sql = "select tip_cod, tip_des_es, tip_des_ca, tip_des_en, tip_admin, tip_menu, tip_lecturas, tip_globus, tip_prediccion, tip_fotoperiodo, tip_inttermica, 
                    tip_horesfrio, tip_climaVid, fec_alt, fec_mod, fec_baj, cli_cif from bee_tipus";
            
            $STH = $db->getInstance()->prepare($sql);
            $STH->execute();
            $STH->setFetchMode(PDO::FETCH_OBJ);

            while ($row = $STH->fetch()) {
                $tipus = new Tipus( $row->tip_cod,
                                    $row->tip_des_es,
                                    $row->tip_des_ca,
                                    $row->tip_des_en,
                                    $row->tip_admin,
                                    $row->tip_menu,
                                    $row->tip_lecturas,
                                    $row->tip_globus,
                                    $row->tip_prediccion,
                                    $row->tip_fotoperiodo,
                                    $row->tip_inttermica,
                                    $row->tip_horesfrio,
                                    $row->tip_climaVid,
                                    $row->cli_cif,
                                    $row->fec_alt,
                                    $row->fec_mod,
                                    $row->fec_baj);

                $cache->setItem($tipus->codi, $tipus);
            }

            $cache->setCompletelyLoaded(true, 24 * 60 * 60);
        }

        $arrTipus = $cache->getDeserialized("Tipus");

        if (!$deleted) {
            $arrTipus = array_filter($arrTipus, function($item){
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

        return $arrTipus;
    }

    public static function allLang(Db $db, array $redisConfig, $lang, bool $deleted = false) {
        $arrTipus = self::all($db, $redisConfig, $deleted);
        if ($arrTipus) {
            foreach($arrTipus as $tipus) {
                switch ($lang) {
                    case "es":
                        $tipus->descripcio = $tipus->descEs;
                        break;
                    case "en":
                        $tipus->descripcio = $tipus->descEn;
                        break;
                    case "ca":
                        $tipus->descripcio = $tipus->descCa;
                        break;
                }
            }
        }
        return $arrTipus;
    }

    public function insert(Db $db, array $redisConfig) : void {

    }

    public function update(DB $db, array $redisConfig) : void {

    }

    public function delete(Db $db, array $redisConfig) : void {
    
    }
}