<?php
Class IndexController extends HomeController {

    public function index() {
        if ($this->isPost()) {
            $this->setAlert("error", "Sin acceso", "Los datos introducidos no son correctos");
        }
        echo "platform: ".$this->platform."<br/>";
        echo "lang: ".$this->lang."<br/>";
        echo "expl: ".$this->expl;

        print_r($this->args);

        //$this->header->addJS("/js/sha512.js");
        //$this->loadView("index");
    }

    protected function setBreadCrumb() {}
}