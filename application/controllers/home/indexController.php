<?php
Class IndexController extends HomeController {

    public function index() {
        $clients = Client::all($this->db, $this->redisConfig);
        $this->header->addJS("/js/sha512.js");
        $this->loadView("index");
    }

    protected function setBreadCrumb() {}
}