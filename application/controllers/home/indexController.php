<?php
Class IndexController extends HomeController {

    public function index() {

        $trad = new Traduccio($this->localesPath, $this->lang, ["index"]);
        $trad->addLocale("lang");
        
        if ($this->isPost()) {
            try {
                if ($this->login()) {
                    $logSession = new LogSession();
                    $lang = $logSession->getValue("userlang");
                    $this->dispatch("/".$lang."/inici");
                } else {
                    $logSession = new LogSession();
                    $lang = $logSession->getValue("userlang");
                    $this->dispatch("/".$lang."/setpassword");
                }
            } catch (Exception $exception) {
                $this->setAlert("error", $trad->get("noAccess"), $trad->get("noOkData"));
            }
        }

        $this->setTemplateVar("trad", $trad);
        $this->header->addJS("/js/sha512.js");
        $this->header->addJS("/js/md5.js");
        $this->loadView("index");
    }

    private function login() {
        try {
            $usuari = Usuari::get($this->db, $this->redisConfig, $this->post["mail"]);

            if (is_null($usuari)) {
                throw new Exception("Invalid user");
            }

            if (is_null($usuari->password) || $usuari->password=="") {
                //login antic
                if ($usuari->pass==$this->post["passMd5"] && ($user->fecBaj=="" || strtotime($user->fecBaj)>time())) {
                    $this->createSession($usuari);
                    return false;
                } else {
                    throw new Exception("Passwords do not match or inactive user");
                }
            } else {
                //login nou
                $password = hash ('sha512', $this->post["pass"] . $usuari->salt);
                            
                if ($usuari->password == $password) {
                    $this->createSession($usuari);
                    return true;
                } else {
                    throw new Exception("Passwords do not match or inactive user");
                }
            }
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    private function createSession($usuari) {
        $arrValues = array(
            "username"=>$usuari->nom,
            "userlang"=>$usuari->lang
        );
        if (!is_null($usuari->client)) {
            $arrValues["userclinom"]=$usuari->client->nom;
            $arrValues["userclicif"]=$usuari->client->cif;
        } else {
            $arrValues["userclinom"]="Beesensor";
            $arrValues["userclicif"]="";
        }

        LogSession::setLogin($usuari->mail, $usuari->password, $arrValues);
    }

    protected function setBreadCrumb() {}
}