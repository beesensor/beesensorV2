<?php
abstract class HomeController extends BaseController {
    /**
     * @var Header
     */
    protected $header;

    protected $sitePath;
    protected $localesPath;
    protected $token = null;
    protected $development = false;
    protected $cli = null;
    protected $explotacions;
    protected $clients;
    protected $usuari;
    protected $explotacioActiva = null;
    protected $appConfig = null;

    abstract protected function setBreadCrumb();

    public function __construct($registry) {
        parent::__construct($registry);
        ini_set('memory_limit','1024M');
        $this->setTemplateVar("alert", $this->getAlert());
        $this->setTemplateVar("nomApp", $registry->appConfig["nomApp"]);
        $this->setTemplateVar("menuNomApp", $registry->appConfig['menuNomApp']);
        $this->setTemplateVar("menuNomAppS", $registry->appConfig['menuNomAppS']);
        $this->setTemplateVar("appDesc", $registry->appConfig['appDesc']);
        $this->setTemplateVar("author", $registry->appConfig['author']);
        $this->setTemplateVar("publicPath", $this->path->sitePath . '/public/');
        $this->sitePath = $this->path->sitePath . '/' .$this->platform . '/';
        $this->setTemplateVar("sitePath", $this->sitePath);
        $this->localesPath = $registry->path->localesPath;
        $this->development = $registry->devConfig["development"];
        $this->appConfig = $registry->appConfig;

        $controllerName = get_class($this);
        $addSelectize = ($controllerName!="IndexController" && $controllerName !="SetPasswordController");
        $this->getHeader($addSelectize);
    }

    protected function setAlertDispatch($type, $header, $msg) {
        if (!$this->isSessionStarted()) {
            $this->sec_session_start();
        }

        $alert = new Alert($type, $header, $msg);
        $_SESSION["alert"]=$alert;
    }

    protected function setAlert($type, $header, $msg) {
        $alert = new Alert($type, $header, $msg);
        $this->setTemplateVar("alert", $alert->toString());
    }

    protected function checkLoggedUserAjax() {
        $logSession = new LogSession();

        if (is_null($logSession->getUser())) {
            return false;
        }

        $usuari = Usuari::get($this->db, $this->redisConfig, $logSession->getUser());
        if (!$usuari || !$logSession->loginCheck($usuari->password)) {
            return false;
        } else {
            $this->usuari = $usuari;
        }

        $clients = array();

        $explotacions = Explotacio::all($this->db, $this->redisConfig);
        if ($usuari->client!=null) {
            $clients[] = $usuari->client;
            $explotacions = array_filter($explotacions, function($explotacio) use ($usuari) {
                return $explotacio->client->cif == $usuari->client->cif;
            });
        } else {
            $clients = Client::all($this->db, $this->redisConfig);
        }

        if (count($explotacions)>0) {

            $this->explotacioActiva=null;

            if (!$this->expl) {
                $this->explotacioActiva = reset($explotacions);
                $this->expl = $this->explotacioActiva->codi;
                $this->cli = $this->explotacioActiva->client->cif;
            } else {
                foreach($explotacions as $explotacio) {
                    if ($explotacio->codi==$this->expl) {
                        $this->explotacioActiva = $explotacio;
                        $this->cli = $explotacio->client->cif;
                        break;
                    }
                }
            }

            if ($this->explotacioActiva!=null) {
                //només volem les explotacions del client que tenim seleccionat en aquest moments
                $clicif = $this->cli;
                $explotacions = array_filter($explotacions, function($explotacio) use ($clicif) {
                    return $explotacio->client->cif == $clicif;
                });

                $this->clients = $clients;
                $this->explotacions = $explotacions;
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    protected function checkLoggedUser() {
        $logSession = new LogSession();

        if (is_null($logSession->getUser())) {
            $this->dispatch("/");
        }

        $usuari = Usuari::get($this->db, $this->redisConfig, $logSession->getUser());
        if (!$usuari || !$logSession->loginCheck($usuari->password)) {
            $this->dispatch("/");
        } else {

            //analitzam si estam rebent info per post donat que es fa un canvi de client o de explotació
            if ($this->isPost()) {
                if (isset($this->post["cboClients"])) {
                    $clicif = $this->post['cboClients'];
                    if ($usuari->client!=null && $usuari->client->cif!=$clicif) {
                        //redireccionam cap a una página d'error
                        $this->dispatch("/");
                    } else {
                        //es correcte, redireccionam cap a la primera explotació que trobem d'aquest client
                        $explotacions = Explotacio::all($this->db, $this->redisConfig);
                        $explotacions = array_filter($explotacions, function($explotacio) use ($clicif) {
                            return $explotacio->client->cif == $clicif;
                        });

                        if (count($explotacions)>0) {
                            $expcodi = reset($explotacions)->codi;
                            
                            //obtenim el controlador
                            $controllerName = get_class($this);
                            $controllerName = strtolower(str_replace("Controller", "", $controllerName));

                            //construim la url
                            $this->dispatch("/".$this->lang."/".$expcodi."/".$controllerName);

                        } else {
                            //quelcom no ha anat bé
                            $this->dispatch("/");
                        }
                    }
                }

                if (isset($this->post["cboExplotacions"])) {
                    $expcodi = $this->post["cboExplotacions"];
                    
                    $explotacions = Explotacio::all($this->db, $this->redisConfig);

                    if ($usuari->client!=null) {
                        //si no és un usuari Beesensor, cercam les explotacions de l'usuari
                        $explotacions = array_filter($explotacions, function($explotacio) use ($usuari) {
                            return $explotacio->client->cif == $usuari->client->cif;
                        });
                    }

                    if (array_key_exists($expcodi, $explotacions)) {
                        $controllerName = get_class($this);
                        $controllerName = strtolower(str_replace("Controller", "", $controllerName));

                        //construim la url
                        $this->dispatch("/".$this->lang."/".$expcodi."/".$controllerName);
                    } else {
                        //no té accés a aquesta explotació
                        $this->dispatch("/");
                    }
                }
            }

            $this->setTemplateVar("userEmail", $logSession->getUser());
            $this->setTemplateVar("userName", $logSession->getValue("username"));
            $this->setTemplateVar("userLang", $logSession->getValue("userlang"));
            $this->setTemplateVar("userCliName", $logSession->getValue("userclinom"));
            $this->usuari = $usuari;

            $this->setTemplateVar("user", $usuari);

            if ($this->usuari->client==null) {
                $this->setTemplateVar("userCliCif","");
            } else {
                $this->setTemplateVar("userCliCif", $this->usuari->client->cif);
            }

            $clients = array();

            $explotacions = Explotacio::all($this->db, $this->redisConfig);
            if ($usuari->client!=null) {
                $clients[] = $usuari->client;
                $explotacions = array_filter($explotacions, function($explotacio) use ($usuari) {
                    return $explotacio->client->cif == $usuari->client->cif;
                });
            } else {
                $clients = Client::all($this->db, $this->redisConfig);
            }

            if (count($explotacions)>0) {

                $this->explotacioActiva=null;

                if (!$this->expl) {
                    $this->explotacioActiva = reset($explotacions);
                    $this->expl = $this->explotacioActiva->codi;
                    $this->cli = $this->explotacioActiva->client->cif;
                } else {
                    foreach($explotacions as $explotacio) {
                        if ($explotacio->codi==$this->expl) {
                            $this->explotacioActiva = $explotacio;
                            $this->cli = $explotacio->client->cif;
                            break;
                        }
                    }
                }

                if ($this->explotacioActiva!=null) {
                    //només volem les explotacions del client que tenim seleccionat en aquest moments
                    $clicif = $this->cli;
                    $explotacions = array_filter($explotacions, function($explotacio) use ($clicif) {
                        return $explotacio->client->cif == $clicif;
                    });

                    $this->clients = $clients;
                    $this->explotacions = $explotacions;
                    $this->setTemplateVar("explotacions", $this->explotacions);
                    $this->setTemplateVar("clients", $this->clients);
                    $this->setTemplateVar("explotacioSeleccionada", $this->expl);
                    $this->setTemplateVar("clientSeleccionat", $this->cli);
                    $this->setTemplateVar("h1", $this->clients[$this->cli]->nom." ".$this->explotacions[$this->expl]->descripcio);

                    $controllerName = get_class($this);
                    $controllerName = strtolower(str_replace("Controller", "", $controllerName));

                    $this->setTemplateVar("controller", $controllerName);
                    $this->setTemplateVar("lang", $this->lang);

                    $expcodi = $this->expl;

                    $documentacions = Documentacio::all($this->db, $this->redisConfig, false);
                    if ($documentacions) {
                        $documentacions = array_filter($documentacions, function($item) use ($expcodi) {
                            if ($item->explotacio==null || $item->explotacio->codi==$expcodi) {
                                return true;
                            } else {
                                return false;
                            }
                        });
                    }
                    $this->setTemplateVar("documentacions", $documentacions);

                    $grafiques = Grafica::all($this->db, $this->redisConfig, false);
                    if ($grafiques) {
                        $grafiques = array_filter($grafiques, function($item) use ($expcodi, $usuari) {
                            if (($item->explotacio==null || $item->explotacio->codi==$expcodi) && $item->usumail==$usuari->mail) {
                                return true;
                            } else {
                                return false;
                            } 
                        });

                        usort($grafiques, function($a, $b){
                            return strtotime($a->fecAlt)<strtotime($b->fecAlt);
                        });
                    }

                    $this->setTemplateVar("grafiques", $grafiques);

                    $tipus = Tipus::allLang($this->db, $this->redisConfig, $this->lang, false);
                    if ($tipus) {
                        $tipus = array_filter($tipus, function($item) use ($clicif) {
                            return ($item->clicif == $clicif && $item->menu==true);
                        });
                    }

                    $this->setTemplateVar("tipus", $tipus);

                    $grafiquesPerTipus = array();
                    if ($tipus) {
                        $grafiquesPersonalitzades = GraficaPersonalitzada::all($this->db, $this->redisConfig, false);
                        if ($grafiquesPersonalitzades) {
                            foreach($tipus as $tipo) {
                                $grafiquesPerTipus[$tipo->codi]=array_filter($grafiquesPersonalitzades, function($item) use ($tipo, $expcodi){
                                    return $item->explotacio->codi==$expcodi && $item->tipus->codi == $tipo->codi;
                                });
                            }
                        }
                    }
                    $this->setTemplateVar("grafiquesPertipus", $grafiquesPerTipus);

                    $mail = "";
                    if ($usuari->client!=null) {
                        $mail = $usuari->mail;
                    }

                    //carregam les alarmes actives de l'explotació
                    $alarmesActives = $this->explotacioActiva->obteAlarmesActives($this->db, $this->redisConfig, $mail);
                    $this->setTemplateVar("alarmesActives", $alarmesActives);

                    $this->setActiveMenuNumber($controllerName);
                } else {
                    $this->dispatch("/");
                }
            } else {
                $this->dispatch("/");
            }
        }
    }

    private function setActiveMenuNumber($controller) {
        if ($controller=="inici" || $controller=="sortir") {
            $this->setTemplateVar("menuOpen", 1);
            return;
        }

        if ($controller=="clients" || $controller=="usuaris" || $controller=="explotacions" || $controller=="documentacio") {
            $this->setTemplateVar("menuOpen", 2);
            return;
        }
    }

    private function getAlert() {
        if (!$this->isSessionStarted()) {
            $this->sec_session_start();
        }

        if (isset($_SESSION["alert"])) {
            $alert = $_SESSION["alert"];
            unset($_SESSION["alert"]);
            return $alert->toString();
        } else {
            return "";
        }
    }

    private function getHeader($addSelectize) {
        $this->header = new Header($this->path->sitePath."/public", $this->development);
        $this->header->addCSS("https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css", "screen", "no", "sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u", "anonymous");
        $this->header->addCSS("https://use.fontawesome.com/releases/v5.6.3/css/all.css", "screen", "no", "sha384-UHRtZLI+pbxtHCWp1t77Bi1L4ZtiqrqD80Kn4Z8NTSRyMA2Fd33n5dQ8lWUE00s/", "anonymous");
        $this->header->addCSS("https://unpkg.com/ionicons@4.5.0/dist/css/ionicons.min.css");
        $this->header->addCSS( "https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic" );
        $this->header->addCSS("/css/AdminLTE.min.css");
        $this->header->addCSS("/css/skin-green.css");
        $this->header->addJS ("https://oss.maxcdn.com/libs/html5shiv/3.7.2/html5shiv.js", "9" );
        $this->header->addJS ("https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js", "9" );
        $this->header->addJS ("https://code.jquery.com/jquery-3.3.1.min.js", "no", "sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=", "anonymous");
        $this->header->addJS ("https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js");
        $this->header->addJS ("/js/AdminLTE/adminlte.min.js");

        if ($addSelectize) {
            $this->header->addCSS("/css/selectize/selectize.css");
            $this->header->addCSS("/css/selectize/selectize.bootstrap3.css");
            $this->header->addJS("/js/selectize/selectize.min.js");
        }
    }

    /**
     * @method loadView
     * @param string $view Nom de la vista a carregar
     * @return void
     */
    protected function loadView($view) {
        $this->setTemplateVar("JSCSS", $this->header->getAll());
        parent::loadView($view);
    }
}