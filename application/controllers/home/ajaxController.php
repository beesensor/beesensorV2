<?php
Class AjaxController extends HomeController {
    
    public function index() {
        $response = new Response(501, null, "Method not implemented");
        $response->printData();
    } 

    public function dadesNode() {
        if ($this->checkLoggedUserAjax()) {
            if ($this->parameters["node"]) {
                $node = Node::get($this->db, $this->redisConfig, $this->parameters["node"]);
                $sensors = Sensor::allLang($this->db, $this->redisConfig, $this->lang, $this->explotacioActiva->codi, false);

                if ($sensors) {
                    $sensors = array_filter($sensors, function($item) use ($node) {
                        return $item->codiNode == $node->codi;
                    });

                    $tipusSensorsPerClient = TipusSensor::obteTipusSensorsPerClient($this->db, $this->redisConfig, $this->cli);
                    $tipusPerClient = null;
                    $arrTipus = array();

                    if ($tipusSensorsPerClient && !is_null($tipusSensorsPerClient->arrTipusSensors)) {
                        $tipusPerClient = $tipusSensorsPerClient->arrTipusSensors;
                        $tipusClient = Tipus::allLang($this->db, $this->redisConfig, $this->lang, false);
                        if ($tipusClient) {
                            $cif = $this->cli;
                            $tipusClient = array_filter($tipusClient, function($item) use ($cif) {
                                return $item->clicif == $cif && $item->globus == true;
                            });
                        }
                    }

                    $arrLectures = array();
                    $darreraLectura = null;
                    foreach($sensors as $sensor) {
                        $lectura = $sensor->darreraLectura($this->db, $this->redisConfig);
                        $arrLectures[$sensor->codi] = $lectura;
                        if ($darreraLectura==null || strtotime($darreraLectura)<strtotime($lectura->tempsLectura)) {
                            $darreraLectura = $lectura->tempsLectura;
                        }

                        $t = array_filter($tipusPerClient, function($item) use ($sensor) {
                            return $item->codiSensor == $sensor->codi;
                        });

                        if (count($t)>0) {
                            foreach ($t as $ts) {
                                if (array_key_exists($ts->codiTipus, $tipusClient)) {
                                    $tc = $tipusClient[$ts->codiTipus];
    
                                    if (!array_key_exists($tc->codi, $arrTipus)) {
                                        $arrTipus[$tc->codi] = $tc;
                                    }
                                }
                            }
                            
                        }
                    }

                    
                    $this->setTemplateVar("node", $node);
                    $this->setTemplateVar("sensors", $sensors);
                    $this->setTemplateVar("lectures", $arrLectures);
                    $this->setTemplateVar("darreraLectura", $darreraLectura);
                    $this->setTemplateVar("tipusSensors", $arrTipus);
                    $this->setTemplateVar("ok", true);
                } else {
                    $this->setTemplateVar("ok", false);
                }

                $this->loadView("dadesNodeAjax");
            } else {
                $response = new Response(400, null, "Bad Request");
                $response->printData();
            }
        } else {
            $response = new Response(401, null, "Unauthorised");
            $response->printData();
        }
    }

    protected function setBreadCrumb() {}
}