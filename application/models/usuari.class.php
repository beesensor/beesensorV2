<?php
Class Usuari extends Model {
     /**
     * @var string
     */
    public $mail;

     /**
     * @var Client|null
     */
    public $client;

     /**
     * @var string
     */
    public $pass;

    /**
     * @var string
     */
    public $password;

    /**
     * @var string
     */
    public $salt;

     /**
     * @var string
     */
    public $nom;

    /**
     * @var bool
     */
    public $explotacions;

    /**
     * @var bool
     */
    public $zones;

    /**
     * @var bool
     */
    public $nodes;
    
    /**
     * @var bool
     */
    public $tipusSensors;

    /**
     * @var bool
     */
    public $lectures;

    /**
     * @var string
     */
    public $lang;

    /**
     * @var bool
     */
    public $admin;

    /**
     * @var bool
     */
    public $alarms;

    /**
     * @var bool
     */
    public $alarmscomp;

    /**
     * @var bool
     */
    public $maxmin;

    /**
     * @var string
     */
    public $telefono;

    /**
     * @var bool
     */
    public $initOffset;

    /**
     * @var bool
     */
    public $initUpdate;

    public function __construct($mail="", $client=null, $pass="", $password="", $salt="", $nom ="", $explotacions=false, $zones=false, $nodes=false, $tipusSensors=false,
                                $lectures=false, $lang="", $admin=false, $alarms=false, $alarmscomp=false, $maxmin=false, $telefono="", $initOffset=false, $initUpdate=false,
                                $fecAlt = null, $fecMod=null, $fecBaj=null) {
        parent::__construct($fecAlt, $fecMod, $fecBaj);
        $this->mail = $mail;
        $this->client = $client;
        $this->pass = $pass;
        $this->password = $password;
        $this->salt = $salt;
        $this->nom = $nom;
        $this->explotacions = $explotacions;
        $this->zones = $zones;
        $this->nodes = $nodes;
        $this->tipusSensors = $tipusSensors;
        $this->lectures = $lectures;
        $this->lang = $lang;
        $this->admin = $admin;
        $this->alarms = $alarms;
        $this->alarmscomp = $alarmscomp;
        $this->maxmin = $maxmin;
        $this->telefono = $telefono;
        $this->initOffset = $initOffset;
        $this->initUpdate = $initUpdate;
    }

    public static function get(Db $db, array $redisConfig, string $mail) {
        $sql = "select usu_mail, cli_cif, usu_pass, usu_password, usu_salt, usu_nom, usu_exp, usu_zon, usu_nod, usu_tip_sen, usu_lec, usu_lang, usu_admin, usu_alarms,
        usu_alarmscomp, usu_maxmin, usu_telf, usu_initOffset, usu_initUpdate, fec_alt, fec_mod, fec_baj	from bee_usuaris where usu_mail=:mail";
        $arrValues = array("mail"=>$mail);
        $STH = $db->getInstance()->prepare($sql);
        $STH->execute($arrValues);
        $STH->setFetchMode(PDO::FETCH_OBJ);
        if ($STH->rowCount() == 1) {
            $row = $STH->fetch();
            $usuari = new Usuari($row->usu_mail,
                                 Client::get($db, $redisConfig, $row->cli_cif),
                                 $row->usu_pass,
                                 $row->usu_password,
                                 $row->usu_salt,
                                 $row->usu_nom,
                                 $row->usu_exp,
                                 $row->usu_zon,
                                 $row->usu_nod,
                                 $row->usu_tip_sen,
                                 $row->usu_lec,
                                 $row->usu_lang,
                                 $row->usu_admin,
                                 $row->usu_alarms,
                                 $row->usu_alarmscomp,
                                 $row->usu_maxmin,
                                 $row->usu_telf,
                                 $row->usu_initOffset,
                                 $row->usu_initUpdate,
                                 $row->fec_alt,
                                 $row->fec_mod,
                                 $row->fec_baj);
            return $usuari;
        } else {
            return null;
        }
    }

    public static function all(Db $db, array $redisConfig) {
        $sql = "select usu_mail, cli_cif, usu_pass, usu_password, usu_salt, usu_nom, usu_exp, usu_zon, usu_nod, usu_tip_sen, usu_lec, usu_lang, usu_admin, usu_alarms,
        usu_alarmscomp, usu_maxmin, usu_telf, usu_initOffset, usu_initUpdate, fec_alt, fec_mod, fec_baj	from bee_usuaris";
        $STH = $db->getInstance()->prepare($sql);
        $STH->execute();
        $STH->setFetchMode(PDO::FETCH_OBJ);

        $arrUsuaris = array();

        while ($row = $STH->fetch()) {
            $usuari = new Usuari($row->usu_mail,
                                 Client::get($db, $redisConfig, $row->cli_cif),
                                 $row->usu_pass,
                                 $row->usu_password,
                                 $row->usu_salt,
                                 $row->usu_nom,
                                 $row->usu_exp,
                                 $row->usu_zon,
                                 $row->usu_nod,
                                 $row->usu_tip_sen,
                                 $row->usu_lec,
                                 $row->usu_lang,
                                 $row->usu_admin,
                                 $row->usu_alarms,
                                 $row->usu_alarmscomp,
                                 $row->usu_maxmin,
                                 $row->usu_telf,
                                 $row->usu_initOffset,
                                 $row->usu_initUpdat,
                                 $row->fec_alt,
                                 $row->fec_mod,
                                 $row->fec_baj);
            $arrUsuaris[] = $usuari;
        }
        return $arrUsuaris;
    }

    public function insert (Db $db, array $redisConfig) {

        $this->salt = hash("sha512", uniqid(mt_rand(1, mt_getrandmax()), true));
        $this->password = hash("sha512", $this->password.$this->salt);

        $cif = null;
        if (!is_null($this->client)) {
            $cif = $this->client->cif;
        }

        $sql = "insert into bee_usuaris (usu_mail, cli_cif, usu_pass, usu_password, usu_salt, usu_nom, usu_exp, usu_zon, usu_nod, usu_tip_sen, usu_lec, usu_lang, usu_admin, usu_alarms,
                                         usu_alarmscomp, usu_maxmin, usu_telf, usu_initOffset, usu_initUpdate, fec_alt, fec_mod) values (
                                         :mail, :cif, :pass, :password, :salt, :nom, :exp, :zon, :nod, :tipSen, :lec, :lang, :admin, :alarms, :alarmscomp, :maxmin, :telf, :initOffset, :initUpdate, :fecAlt, :fecMod)";
        $arrValues = array("mail" => $this->mail,
                           "cif"  => $cif,
                           "pass" => $this->pass,
                           "password" => $this->password,
                           "salt" => $this->salt,
                           "nom"  => $this->nom,
                           "exp"  => $this->explotacions,
                           "zon"  => $this->zones,
                           "nod"  => $this->nodes,
                           "tipSen" => $this->tipusSensors,
                           "lec"  => $this->lectures,
                           "lang" => $this->lang,
                           "admin"=> $this->admin,
                           "alarms" => $this->alarms,
                           "alarmsComp" => $this->alarmscomp,
                           "maxmin" => $this->maxmin,
                           "telf" => $this->telefono,
                           "initOffset" => $this->initOffset,
                           "initUpdate" => $this->initUpdate,
                           "fecAlt" => date(Y-m-d),
                           "fecMod" => date(Y-m-d));

        $STH = $db->getInstance()->prepare($sql);
        $STH->execute($arrValues);
        $this->setActualDates(true);
    }

    public function update(Db $db, array $redisConfig) {
        $sql = "update bee_usuaris set ";

        $arrValues = array( "mail" => $this->mail,
                            "nom"  => $this->nom,
                            "exp"  => $this->explotacions,
                            "zon"  => $this->zones,
                            "nod"  => $this->nodes,
                            "tipSen" => $this->tipusSensors,
                            "lec"  => $this->lectures,
                            "lang" => $this->lang,
                            "admin"=> $this->admin,
                            "alarms" => $this->alarms,
                            "alarmsComp" => $this->alarmscomp,
                            "maxmin" => $this->maxmin,
                            "telf" => $this->telefono,
                            "initOffset" => $this->initOffset,
                            "initUpdate" => $this->initUpdate,
                            "fecMod" => date("Y-m-d"));
        
        if (!is_null($this->client)) {
            $sql.="cli_cif=:cif, ";
            $arrValues["cif"]=$this->client->cif; 
        } else {
            $sql.="cli_cif=null, ";
        }

        if (isset($this->password) && $this->password!="") {
            $sql.=" usu_password=:password,";
            if (!isset($this->salt) || is_null($this->salt) || $this->salt=="") {
                $this->salt = hash("sha512", uniqid(mt_rand(1, mt_getrandmax()), true));
                $sql.=" usu_salt=:salt,";
                $arrValues["salt"]=$this->salt;
            }
            $arrValues["password"] = hash("sha512", $this->password.$this->salt);
        }

        if (isset($this->pass) && $this->pass!="") {
            $sql.=" usu_pass=:pass,";
            $arrValues["pass"] = $this->pass;
        }

        $sql.= " usu_nom=:nom, usu_exp=:exp, usu_zon=:zon, usu_nod=:nod, usu_tip_sen=:tipSen, usu_lec=:lec, usu_lang=:lang, 
                usu_admin=:admin, usu_alarms=:alarms, usu_alarmscomp=:alarmsComp, usu_maxmin=:maxmin, usu_telf=:telf, usu_initOffset=:initOffset, usu_initUpdate=:initUpdate, fec_mod=:fecMod where usu_mail=:mail";
        
        $STH = $db->getInstance()->prepare($sql);
        $STH->execute($arrValues);
        $this->setActualDates(true);
    }

    public function delete(Db $db, array $redisConfig) {
        $sql = "udpate bee_usuaris set fec_baj = :fecBaj where usu_mail=:mail";
        $arrValues = array("mail" => $this->mail, "fecBaj"=>date("Y-m-d"));
        $STH = $db->getInstance()->prepare($sql);
        $STH->execute($arrValues);
        $this->setDeletionDate();
    }

    public function setNewPassword(Db $db) {
        $this->salt = hash("sha512", uniqid(mt_rand(1, mt_getrandmax()), true));
        $this->password = hash("sha512", $this->password.$this->salt);
        $sql = "update bee_usuaris set usu_password=:password, usu_salt=:salt where usu_mail=:mail";
        $arrValues=array("mail"=>$this->mail,
                         "password"=>$this->password,
                         "salt"=>$this->salt);
        $STH = $db->getInstance()->prepare($sql);
        $STH->execute($arrValues);
    }
}