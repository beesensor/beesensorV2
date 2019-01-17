<?php
Class SetPasswordController extends HomeController {
    public function index() {
        $this->checkLoggedUer();

        $logSession = new LogSession();
        if (is_null($logSession)) {
            $this->dispatch("/index");
        } else {
            $lang = $logSession->getValue("userlang");
            $mail = $logSession->getUser();
            $usuari = Usuari::get($this->db, $this->redisConfig, $mail);
            if (is_null($usuari)) {
                $this->dispatch("/".$lang."/index");
            } else {
                if ($usuari->password!="") {
                    $this->dispatch("/".$lang."/index");
                }
            }
        }

        $trad = new Traduccio($this->localesPath, $this->lang, ["setpassword"]);

        if ($this->isPost()) {
            try {
                $usuari->pass="";
                $usuari->password=$this->post["pass"];
                $usuari->setNewPassword($this->db);
                $this->dispatch("/".$lang."/inici");
            } catch (Exception $exception) {
                $this->setAlert("error", $trad->get("error"), $trad->get("errorTxt"));
            }
        }

        $this->setTemplateVar("trad", $trad);
        $this->header->addJS("/js/sha512.js");
        $this->loadView("setpassword");
    }

    protected function setBreadCrumb() {}
}