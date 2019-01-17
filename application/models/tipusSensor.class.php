<?php
Class TipusSensor {
    /**
     * @var int
     */
    public $codiTipus;

    /**
     * @var string
     */
    public $codiSensor;

    /**
     * @var string
     */
    public $clicif;

    /**
     * @var string
     */
    const cacheKey = "TIPUSSENSOR";

    public function __construct($codiTipus = 0, $codiSensor = "", $clicif = "") {
        $this->codiTipus = $codiTipus;
        $this->codiSensor = $codiSensor;
        $this->clicif = $clicif;
    }

    public static function all(Db $db, array $redisConfig) {
        $cache = new RedisCache(self::cacheKey, $redisConfig);
        if (!$cache->isCompletelyLoaded()) {
            $sql = "select tip_cod, par_cod, cli_cif from bee_tipus_par";
            $STH = $db->getInstance()->prepare($sql);
            $STH->execute();
            $STH->setFetchMode(PDO::FETCH_OBJ);

            $arrTipus = array();
            while ($row = $STH->fetch()) {
                if (!array_key_exists($row->cli_cif, $arrTipus)) {
                    $arrTipus[$row->cli_cif] = new ClientTipusSensors($row->cli_cif);
                }
                $arrTipus[$row->cli_cif]->addTipusSensor(new TipusSensor($row->tip_cod, $row->par_cod, $row->cli_cif));
            }

            foreach($arrTipus as $key => $llistaTipus) {
                $cache->setItem($key ,$llistaTipus);
            }

            $cache->setCompletelyLoaded(true, 24 * 60 * 60);
        }

        return $cache->getDeserialized('ClientTipusSensors');
    }

    public static function obteTipusSensorsPerClient(Db $db, array $redisConfig, $clicif) {
        $arrTipus = self::all($db, $redisConfig);
        return $arrTipus[$clicif];
    }
}