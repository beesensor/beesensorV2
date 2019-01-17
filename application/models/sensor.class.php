<?php
Class Sensor extends Model {
    /**
     * @var string
     */
    public $codi;

    /**
     * @var int|null
     */
    public $codiExplotacio;

    /**
     * @var string
     */
    public $clicif;

    /**
     * @var string
     */
    public $codiNode;

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
    public $abreviaturaES;

    /**
     * @var string
     */
    public $abreviaturaCA;

    /**
     * @var string
     */
    public $abreviaturaEN;

    /**
     * @var string
     */
    public $unitat;

    /**
     * @var string
     */
    public $formula;

    /**
     * @var bool|null
     */
    public $taulaConversio;

    /**
     * @var int
     */
    public $decimals;

    /**
     * @var bool|null
     */
    public $limitMax;

    /**
     * @var float|null
     */
    public $valorMax;

    /**
     * @var bool|null
     */
    public $limitMin;

    /**
     * @var float|null
     */
    public $valorMin;

    /**
     * @var string
     */
    public $tipusCalcul;

    /**
     * @var string|null
     */
    public $tipusGrafica;

    /**
     * @var bool|null
     */
    public $subSensor;

    /**
     * @var string
     */
    public $descripcioSubsensorES;

    /**
     * @var string
     */
    public $descripcioSubsensorCA;

    /**
     * @var string
     */
    public $descripcioSubsensorEN;

     /**
     * @var string
     */
    public $abreviaturaSubsensorES;

    /**
     * @var string
     */
    public $abreviaturaSubsensorCA;

    /**
     * @var string
     */
    public $abreviaturaSubsensorEN;

    /**
     * @var string
     */
    public $tipusSubsensorCalcul;

    /**
     * @var string|null
     */
    public $tipusSubsensorGrafica;

    /**
     * @var float|null
     */
    public $altura;

    /**
     * @var string|null
     */
    public $descripcio;

    /**
     * @var string|null
     */
    public $abrevitura;

    /**
     * @var string|null
     */
    public $descripcioSubsensor;

    /**
     * @var string|null
     */
    public $abreviaturaSubsensor;


    /**
     * @var string
     */
    const cacheKey = "SENSORS";

    public function __construct($codi = "", $codiExplotacio = null, $clicif = "", $codiNode = "", $descripcioES ="", $descripcioCA ="", $descripcioEN = "", $abreviaturaES = "", $abreviaturaCA = "", $abreviaturaEN = "",
                                $unitat = "", $formula = "", $taulaConversio = false, $decimals = 0, $limitMax = false, $valorMax = 0, $limitMin = false, $valorMin = 0, $tipusCalcul = "",
                                $tipusGrafica = "", $subSensor = false, $descripcioSubsensorES = "", $descripcioSubsensorCA = "", $descripcioSubsensorEN = "", $abreviaturaSubsensorES ="",
                                $abreviaturaSubsensorCA = "", $abreviaturaSubsensorEN = "", $tipusSubsensorCalcul = "", $tipusSubsensorGrafica ="", $altura = 0, $fecAlt ="", $fecMod = "", $fecBaj ="") {
        parent::__construct($fecAlt, $fecMod, $fecBaj);
        $this->codi = $codi;
        $this->codiExplotacio = $codiExplotacio;
        $this->clicif = $clicif;
        $this->codiNode = $codiNode;
        $this->descripcioES = $descripcioES;
        $this->descripcioCA = $descripcioCA;
        $this->descripcioEN = $descripcioEN;
        $this->abreviaturaES = $abreviaturaES;
        $this->abreviaturaCA = $abreviaturaCA;
        $this->abreviaturaEN = $abreviaturaEN;
        $this->unitat = $unitat;
        $this->formula = $formula;
        $this->taulaConversio = $taulaConversio;
        $this->decimals = $decimals;
        $this->limitMax = $limitMax;
        $this->valorMax = $valorMax;
        $this->limitMin = $limitMin;
        $this->valorMin = $valorMin;
        $this->tipusCalcul = $tipusCalcul;
        $this->tipusGrafica = $tipusGrafica;
        $this->subSensor = $subSensor;
        $this->descripcioSubsensorES = $descripcioSubsensorES;
        $this->descripcioSubsensorCA = $descripcioSubsensorCA;
        $this->descripcioSubsensorEN = $descripcioSubsensorEN;
        $this->abreviaturaSubsensorES = $abreviaturaSubsensorES;
        $this->abreviaturaSubsensorCA = $abreviaturaSubsensorCA;
        $this->abreviaturaSubsensorEN = $abreviaturaSubsensorEN;
        $this->tipusSubsensorCalcul =$tipusSubsensorCalcul;
        $this->tipusSubsensorGrafica = $tipusSubsensorGrafica;
        $this->altura = $altura;
    }

    public static function get(Db $db, array $redisConfig, int $codi, string $codiNode, int $codiExplotacio, string $clicif) {
        $cache = new RedisCache(self::cacheKey, $redisConfig);
        $sensor = $cache->getItemDeserialized($codi."-".$codiNode."-".$codiExplotacio."-".$clicif, new Sensor());
        if ($sensor==null) {
            $sql = "SELECT p.par_cod, np.exp_cod, p.cli_cif, np.nod_cod, p.par_des, p.par_des_ca, p.par_des_en, p.par_abr_es, p.par_abr_ca, p.par_abr_en, p.par_unit, p.par_form, p.par_table, p.par_dec,
                           p.par_max_i, p.par_max, p.par_min_i, p.par_min, p.par_tipcalc, p.par_tipgraf, p.par_sub, p.par_des2, p.par_des_ca2, p.par_des_en2, p.par_abr_es2, p.par_abr_ca2,
                           p.par_abr_en2, p.par_tipcalc2, p.par_tipgraf2, p.par_alt, p.fec_alt, p.fec_mod, p.fec_baj
                           FROM bee_par p 
                           inner join bee_nodos_par np on p.par_cod = np.par_cod 
                           inner join bee_nodos n on n.nod_cod = np.nod_cod and n.exp_cod = np.exp_cod
                           inner join bee_expl e on e.exp_cod = np.exp_cod and p.cli_cif = e.cli_cif
                           where p.par_cod = :codi, and np.exp_cod = :codiExplotacio and p.cli_cif = :clicif";
            $arrValues = array("codi"=>$codi,
                               "codiExplotacio"=>$codiExplotacio,
                               "clicif"=>$clicif);

            $STH = $db->getInstance()->prepare($sql);
            $STH->execute($arrValues);
            $STH->setFetchMode(PDO::FETCH_OBJ);

            if ($STH->rowCount() == 1) {
                $row = $STH->fetch();

                $sensor = new Sensor($row->par_cod, $row->exp_cod, $row->cli_cif, $row->nod_cod, $row->par_des, $row->par_des_ca, $row->par_des_en, $row->par_abr_es, $row->par_abr_ca, $row->par_abr_en, $row->par_unit,
                                $row->par_form, $row->par_table, $row->par_dec, $row->par_max_i, $row->par_max, $row->par_min_i, $row->par_min, $row->par_tipcalc, $row->par_tipgraf, $row->par_sub,
                                $row->par_des2, $row->par_des_ca2, $row->par_des_en2, $row->par_abr_es2, $row->par_abr_ca2, $row->par_abr_en2, $row->par_tipcalc2, $row->par_tipgraf2, $row->par_alt,
                                $row->fec_alt, $row->fec_mod, $row->fec_baj);

                $cache->setItem($sensor->codi."-".$sensor->codiNode."-".$sensor->codiExplotacio."-".$sensor->clicif, $sensor);
                $cache->setCache(24 * 60 * 60);
            }
        }

        return $sensor;
    }

    public static function all(Db $db, array $redisConfig, int $codiExplotacio = null, bool $deleted = false) {
        $cache = new RedisCache(self::cacheKey, $redisConfig);
        if (!$cache->isCompletelyLoaded()) {
            $sql = "SELECT p.par_cod, np.exp_cod, p.cli_cif, np.nod_cod, p.par_des, p.par_des_ca, p.par_des_en, p.par_abr_es, p.par_abr_ca, p.par_abr_en, p.par_unit, p.par_form, p.par_table, p.par_dec,
                           p.par_max_i, p.par_max, p.par_min_i, p.par_min, p.par_tipcalc, p.par_tipgraf, p.par_sub, p.par_des2, p.par_des_ca2, p.par_des_en2, p.par_abr_es2, p.par_abr_ca2,
                           p.par_abr_en2, p.par_tipcalc2, p.par_tipgraf2, p.par_alt, p.fec_alt, p.fec_mod, p.fec_baj
                           FROM bee_par p 
                           inner join bee_nodos_par np on p.par_cod = np.par_cod
                           inner join bee_nodos n on n.nod_cod = np.nod_cod and n.exp_cod = np.exp_cod
                           inner join bee_expl e on e.exp_cod = np.exp_cod and p.cli_cif = e.cli_cif";
            $STH = $db->getInstance()->prepare($sql);
            $STH->execute();
            $STH->setFetchMode(PDO::FETCH_OBJ);
            while ($row = $STH->fetch()) {
                $sensor = new Sensor($row->par_cod, $row->exp_cod, $row->cli_cif, $row->nod_cod, $row->par_des, $row->par_des_ca, $row->par_des_en, $row->par_abr_es, $row->par_abr_ca, $row->par_abr_en, $row->par_unit,
                                $row->par_form, $row->par_table, $row->par_dec, $row->par_max_i, $row->par_max, $row->par_min_i, $row->par_min, $row->par_tipcalc, $row->par_tipgraf, $row->par_sub,
                                $row->par_des2, $row->par_des_ca2, $row->par_des_en2, $row->par_abr_es2, $row->par_abr_ca2, $row->par_abr_en2, $row->par_tipcalc2, $row->par_tipgraf2, $row->par_alt,
                                $row->fec_alt, $row->fec_mod, $row->fec_baj);
                $cache->setItem($sensor->codi."-".$sensor->codiNode."-".$sensor->codiExplotacio."-".$sensor->clicif, $sensor);
            }
            $cache->setCompletelyLoaded(true, 24 * 60 * 60);
        }
        $arrSensors = $cache->getDeserialized("Sensor");

        if (!$deleted) {
            $arrSensors = array_filter($arrSensors, function($item){
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

        if ($codiExplotacio!=null) {
            $arrSensors = array_filter($arrSensors, function($item) use ($codiExplotacio) {
                return $item->codiExplotacio == $codiExplotacio;
            });
        }

        return $arrSensors;
    }

    public static function allLang(Db $db, array $redisConfig, $lang, int $codiExplotacio = null, bool $deleted = false) {
        $arrSensors = self::all($db, $redisConfig, $codiExplotacio, $deleted);
        if ($arrSensors) {
            switch ($lang) {
                case "es":
                    foreach ($arrSensors as $sensor) {
                        $sensor->descripcio = $sensor->descripcioES;
                        $sensor->abreviatura = $sensor->abreviaturaES;
                        $sensor->descripcioSubsensor = $sensor->descripcioSubsensorES;
                        $sensor->abreviaturaSubsensor = $sensor->abreviaturaSubsensorES;
                    }
                    break;
                case "en":
                    foreach ($arrSensors as $sensor) {
                        $sensor->descripcio = $sensor->descripcioEN;
                        $sensor->abreviatura = $sensor->abreviaturaEN;
                        $sensor->descripcioSubsensor = $sensor->descripcioSubsensorEN;
                        $sensor->abreviaturaSubsensor = $sensor->abreviaturaSubsensorEN;
                    }
                    break;
                case "ca":
                    foreach ($arrSensors as $sensor) {
                        $sensor->descripcio = $sensor->descripcioCA;
                        $sensor->abreviatura = $sensor->abreviaturaCA;
                        $sensor->descripcioSubsensor = $sensor->descripcioSubsensorCA;
                        $sensor->abreviaturaSubsensor = $sensor->abreviaturaSubsensorCA;
                    }
                    break;
            }
            
        }
        return $arrSensors;
    }

    public function darreraLectura(Db $db, array $redisConfig) {
        $cache = new RedisCache("DARRERESLECTURES", $redisConfig);
        $lectura = $cache->getItemDeserialized($this->clicif."-".$this->codiExplotacio."-".$this->codiNode."-".$this->codi, new Lectura());
        if ($lectura==null) {
            $sql = "SELECT lec_cod, cli_cif, exp_cod, nod_cod, par_cod, lec_val, lec_valcalc, lec_offset, lec_time, lec_lat, lec_lon, lec_latg, lec_long, lec_agrup
            FROM bee_lect where cli_cif=:clicif and exp_cod=:expcod and nod_cod=:nodcod and par_cod=:parcod order by 1 desc limit 1";
            $arrValues = array("clicif"=>$this->clicif,
                                "expcod"=>$this->codiExplotacio,
                                "nodcod"=>$this->codiNode,
                                "parcod"=>$this->codi);

            $STH = $db->getInstance()->prepare($sql);
            $STH->execute($arrValues);
            $STH->setFetchMode(PDO::FETCH_OBJ);

            if ($STH->rowCount() == 1) {
                $row = $STH->fetch();
                $lectura = new Lectura($row->lec_cod, $row->cli_cif, $row->exp_cod, $row->nod_cod, $row->par_cod, $row->lec_val, $row->lec_valcalc, $row->lec_offset, $row->lec_time,
                                       $row->lec_lat, $row->lec_lon, $row->lec_latg, $row->lec_long, $row->lec_agrup);
                
                $cache->setItem($this->clicif."-".$this->codiExplotacio."-".$this->codiNode."-".$this->codi, $lectura);
                $cache->setCache(24 * 60 * 60);
            }
        }
        return $lectura;
    }

    public function insert(Db $db, array $redisConfig) : void {

    }

    public function update(DB $db, array $redisConfig) : void {

    }

    public function delete(Db $db, array $redisConfig) : void {
    
    }
}