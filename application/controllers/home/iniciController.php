<?php
Class IniciController extends HomeController {

    private $trad;

    public function index() {
        $this->checkLoggedUser();

        $this->trad = new Traduccio($this->localesPath, $this->lang, ["lang", "base", "inici"]);
        $this->setTemplateVar("trad", $this->trad);
        $this->setBreadCrumb();

        $explotacioActiva = $this->explotacioActiva;
        $this->setTemplateVar("explotacioActiva", $explotacioActiva);
        $this->setTemplateVar("lang", $this->lang);
        
        $nodes = Node::all($this->db, $this->redisConfig, false);
        if ($nodes) {
            $nodes = array_filter($nodes, function($item) use ($explotacioActiva){
                return $item->explotacio->codi == $explotacioActiva->codi && $item->actiu==true;
            });
        }

        $mapaOpcio = -1;

        if ($nodes) {
            $hihaGPS = false;
            foreach($nodes as $node) {
                if ($node->gps) {
                    $hihaGPS = true;
                    break;
                }
            }

            if ($explotacioActiva->inici==0) {
                if ($hihaGPS) {
                    $mapaOpcio = 1;
                } else {
                    //Inici normal -> cercam el centre del mapa en funció dels nodes
                    $lat = 0;
                    $lng = 0;
                    if (count($nodes)>0) {
                        if (count($nodes)>1){
                            $maxLat = null;
                            $maxLng = null;
                            $minLat = null;
                            $minLng = null;

                            foreach($nodes as $node) {
                                if (is_null($maxLat) || $maxLat<$node->lat) {
                                    $maxLat = $node->lat;
                                }
                                if (is_null($minLat) || $minLat>$node->lat) {
                                    $minLat = $node->lat;
                                }
                                if (is_null($maxLng) || $maxLng<$node->lng) {
                                    $maxLng = $node->lng;
                                }
                                if (is_null($minLng) || $minLng>$node->lng) {
                                    $minLng = $node->lng;
                                }
                            }

                            $lat = (($maxLat - $minLat)/2) + $minLat;
                            $lng = (($maxLng - $minLng)/2) + $minLng;
                        } else {
                            $lat = reset($nodes)->lat;
                            $lng = reset($nodes)->lng;
                        }
                    }
                    $this->setTemplateVar("mapLat", $lat);
                    $this->setTemplateVar("mapLng", $lng);
                    $this->setTemplateVar("nodes", $nodes);

                    if (is_null($explotacioActiva->OMtoken) || $explotacioActiva->OMtoken=="") {
                        $this->setTemplateVar("OMToken", $this->appConfig["OMToken"]);
                    } else {
                        $this->setTemplateVar("OMToken", $explotacioActiva->OMtoken);
                    }
                    $mapaOpcio = 0;
                }
            } elseif ($explotacioActiva->inici==1) {
                $mapaOpcio = 2;
            } else {
                $mapaOpcio = 3;
            }
        } else {
            //no hi ha nodes, hauriem de redirigir cap a un altra lloc
        }

        $this->setHeader($mapaOpcio);
        $this->setTemplateVar("mapaOpcio",$mapaOpcio);
        
        $this->loadView("inici");
    }

    protected function setBreadCrumb() {
        $this->setTemplateVar("h2", $this->trad->get("home"));

        $breadCrumb = new BreadCrumb();
        $breadCrumb->addBreadCrumbItem($this->explotacions[$this->expl]->descripcio, "./inici", "fa fa-dashboard");
        $breadCrumb->addBreadCrumbItem($this->trad->get("home"), null, null);
        $this->setTemplateVar("breadCrumb", $breadCrumb->toString());
    }

    private function setHeader() {
        $this->header->addCSS("https://unpkg.com/leaflet@1.4.0/dist/leaflet.css", "screen", "no", "sha512-puBpdR0798OZvTTbP4A8Ix/l+A4dHDD0DGqYW6RQ+9jxkRFclaxxQb/SJAWZfWAkuyeQUytO7+7N4QKrDh+drA==", "true");
        $this->header->addJS("https://unpkg.com/leaflet@1.4.0/dist/leaflet.js", "no", "sha512-QVftwZFqvtRNi0ZyCtsznlKSWOStnDORoefr1enyq5mVL4tmKB3S/EnC3rRJcxCPavG10IcrVGSmPh6Qw5lwrg==", "true");
    }
}