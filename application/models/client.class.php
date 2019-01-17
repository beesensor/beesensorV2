<?php
Class Client extends Model {
     /**
     * @var string
     */
    public $cif;

    /**
     * @var string
     */
    public $nom;

    /**
     * @var string
     */
    public $dir;

    /**
     * @var string
     */
    public $cp;

    /**
     * @var string
     */
    public $poblacio;

    /**
     * @var string
     */
    public $provincia;

    /**
     * @var string
     */
    public $pais;

    /**
     * @var string
     */
    public $tel1;

    /**
     * @var string
     */
    public $tel2;

    /**
     * @var string
     */
    public $fax;

    /**
     * @var string
     */
    public $mail;

    /**
     * @var string
     */
    public $url;

    /**
     * @var bool
     */
    public $sms;

    /**
     * @var string|null
     */
    public $smsLogin;

    /**
     * @var string|null
     */
    public $smsPassword;


    /**
     * @var string
     */
    const cacheKey = "CLIENTS";

    public function __construct($cif="", $nom="", $dir="", $cp="", $poblacio="", $provincia="", $pais="", $tel1="", $tel2="", $fax="", $mail="", $url="", 
                                $sms=false, $smsLogin="", $smsPassword="", $fecAlt=null, $fecMod=null, $fecBaj=null) {
        parent::__construct($fecAlt, $fecMod, $fecBaj);
        $this->cif = $cif;
        $this->nom = $nom;
        $this->dir = $dir;
        $this->cp  = $cp;
        $this->poblacio = $poblacio;
        $this->provincia = $provincia;
        $this->pais = $pais;
        $this->tel1 = $tel1;
        $this->tel2 = $tel2;
        $this->fax  = $fax;
        $this->mail = $mail;
        $this->url  = $url;
        $this->sms  = $sms;
        $this->smsLogin = $smsLogin;
        $this->smsPassword = $smsPassword;
    }

    public static function get(Db $db, array $redisConfig, string $cif) {
        $cache = new RedisCache(self::cacheKey, $redisConfig);
        $client = $cache->getItemDeserialized($cif, new Client());
        if ($client==null) {
            $sql = "select cli_cif, cli_nom, cli_dir, cli_cp, cli_pob, cli_prov, cli_pais, cli_tel1, cli_tel2, cli_fax, cli_mail, cli_url, 
            cli_sms, cli_smsLogin, cli_smsPass, fec_alt, fec_mod, fec_baj from bee_clients where cli_cif=:cif";
            $arrValues = array("cif"=>$cif);
            $STH = $db->getInstance()->prepare($sql);
            $STH->execute($arrValues);
            $STH->setFetchMode(PDO::FETCH_OBJ);
            if ($STH->rowCount() == 1) {
                $row = $STH->fetch();
                $client = new Client($row->cli_cif, $row->cli_nom, $row->cli_dir, $row->cli_cp, $row->cli_pob, $row->cli_prov, $row->cli_pais,
                                     $row->cli_tel1, $row->cli_tel2, $row->cli_fax, $row->cli_mail, $row->cli_url, 
                                     $row->cli_sms, $row->cli_smsLogin, $row->cli_smsPass, $row->fec_alt, $row->fec_mod, $row->fec_baj);
                $cache->setItem($client->cif, $client);
                $cache->setCache(24 * 60 * 60);
            }
        }

        return $client;
    }

    public static function all(Db $db, array $redisConfig, bool $deleted = false) {
        $cache = new RedisCache(self::cacheKey, $redisConfig);
        if (!$cache->isCompletelyLoaded()) {
            $sql = "select cli_cif, cli_nom, cli_dir, cli_cp, cli_pob, cli_prov, cli_pais, cli_tel1, cli_tel2, cli_fax, cli_mail, cli_url, 
            cli_sms, cli_smsLogin, cli_smsPass, fec_alt, fec_mod, fec_baj from bee_clients";
            $STH = $db->getInstance()->prepare($sql);
            $STH->execute();
            $STH->setFetchMode(PDO::FETCH_OBJ);
            while ($row = $STH->fetch()) {
                $client = new Client($row->cli_cif, $row->cli_nom, $row->cli_dir, $row->cli_cp, $row->cli_pob, $row->cli_prov, $row->cli_pais,
                                     $row->cli_tel1, $row->cli_tel2, $row->cli_fax, $row->cli_mail, $row->cli_url, 
                                     $row->cli_sms, $row->cli_smsLogin, $row->cli_smsPass, $row->fec_alt, $row->fec_mod, $row->fec_baj);
                $cache->setItem($client->cif, $client);
            }
            $cache->setCompletelyLoaded(true, 24 * 60 * 60);
        }
        $arrClients = $cache->getDeserialized("Client");
        
        if (!$deleted) {
            $arrClients = array_filter($arrClients, function($item){
                if (strtotime($item->fecAlt)<time()) {
                    if (is_null($item->fecBaj) || $item->fecBaj=="") {
                        return true;
                    }
                    if (strtotime($item->fecBaj)>time()) {
                        return true;
                    }
                } 
                return false;
            });
        }
        
        return $arrClients;
    }

    public function insert(Db $db, array $redisConfig) {
        $sql = "insert into bee_clients (cli_cif, cli_nom, cli_dir, cli_cp, cli_pob, cli_prov, cli_pais, cli_tel1, cli_tel2, cli_fax, cli_mail, cli_url, 
        cli_sms, cli_smsLogin, cli_smsPass, fec_alt, fec_mod) values 
        (:cif, :nom, :dir, :cp, :pob, :prov, :pais, :tel1, :tel2, :fax, :mail, :url, :sms, :smsLogin, :smsPass, :fecAlt, :fecMod)";
        $arrValues = array("cif"=>$this->cif,
                           "nom"=>$this->nom,
                           "dir"=>$this->dir,
                           "cp" =>$this->cp,
                           "pob"=>$this->poblacio,
                           "prov"=>$this->provincia,
                           "pais"=>$this->pais,
                           "tel1"=>$this->tel1,
                           "tel2"=>$this->tel2,
                           "fax" =>$this->fax,
                           "mail"=>$this->mail,
                           "url" =>$this->url,
                           "sms" =>$this->sms,
                           "smsLogin"=>$this->smsLogin,
                           "smsPass"=>$this->smsPassword,
                           "fecAlt"=>date("Y-m-d"),
                           "fecMod"=>date("Y-m-d"));
        
        $STH = $db->getInstance()->prepare($sql);
        $STH->execute($arrValues);
        $this->setActualDates(true);
        $cache = new RedisCache(self::cacheKey, $redisConfig);
        $cache->setItem($this->cif, $this);
        $cache->setCache(24 * 60 * 60);
    }

    public function update(Db $db, array $redisConfig) {
        $sql = "update bee_clients set cli_nom=:nom, cli_dir=:dir, cli_cp=:cp, cli_pob=:pob, cli_prov=:prov, cli_pais=:pais, 
                cli_tel1=:tel1, cli_tel2=:tel2, cli_fax=:fax, cli_mail=:mail, cli_url=:url, 
                cli_sms=:sms, cli_smsLogin=:smsLogin, cli_smsPass=:smsPass, fec_mod=:fecMod where cli_cif=:cif";
        $arrValues = array("cif"=>$this->cif,
                           "nom"=>$this->nom,
                           "dir"=>$this->dir,
                           "cp" =>$this->cp,
                           "pob"=>$this->poblacio,
                           "prov"=>$this->provincia,
                           "pais"=>$this->pais,
                           "tel1"=>$this->tel1,
                           "tel2"=>$this->tel2,
                           "fax" =>$this->fax,
                           "mail"=>$this->mail,
                           "url" =>$this->url,
                           "sms" =>$this->sms,
                           "smsLogin"=>$this->smsLogin,
                           "smsPass"=>$this->smsPassword,
                           "fecMod"=>date("Y-m-d"));
        
        $STH = $db->getInstance()->prepare($sql);
        $STH->execute($arrValues);
        $this->setActualDates(true);
        $cache = new RedisCache(self::cacheKey, $redisConfig);
        $cache->setItem($this->cif, $this);
        $cache->setCache(24 * 60 * 60);  
    }

    public function delete(Db $db, array $redisConfig) {
        $sql = "udpate bee_clients set fec_baj=:fecBaj where cli_cif=:cif";
        $arrValues = array("fecBaj"=>date("Y-m-d"),
                           "cif"=>$this->cif);

        $STH = $db->getInstance()->prepare($sql);
        $STH->execute($arrValues);
        $this->setDeletionDate();
        $cache = new RedisCache(self::cacheKey, $redisConfig);
        $cache->setItem($this->cif, $this);
        $cache->setCache(24 * 60 * 60);  
    }
}