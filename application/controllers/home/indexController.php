<?php
Class IndexController extends BaseController {

    public function index() {
    
       $clients = Client::all($this->db, $this->redisConfig);
       print_r($clients);
    
       $explotacions = Explotacio::all($this->db, $this->redisConfig);
       print_r($explotacions);
      
       $zones = Zona::all($this->db, $this->redisConfig);
       print_r($zones);
    }
}