<?php
Class Header {
    private $arrJS = array();
    private $arrCss = array();
    private $sitePath;
    private $debug;

    public function __construct(string $sitePath, $debug=false) {
        $this->sitePath = $sitePath;
        $this->debug = $debug;
    }

    public function addCSS($css, $media = "screen", $IE = "no", $integrity = "", $crossorigin = "", $forcecrossorigin = false) {
        $trobat = false;
        foreach($this->arrCss as $cssObj) {
            if ($cssObj->css == $css) {
                $trobat = true;
            }
        }

        if (!$trobat) {
            $this->arrCss [] = new Css($css, $media, $IE, $integrity, $crossorigin, $forcecrossorigin);
        }
    }

    public function addJS($js, $IE = "no", $integrity = "", $crossorigin = "", $forcecrossorigin = false)
    {
        $trobat = false;
        foreach ($this->arrJS as $JS) {
            if ($JS->js == $js) {
                $trobat = true;
                break;
            }
        }
        if (! $trobat) {
            $this->arrJS [] = new JS($js, $IE, $integrity, $crossorigin, $forcecrossorigin);
        }
    }

    public function getCss() {
        $stRet = "";
        foreach ($this->arrCss as $css) {
            $stRet .= $css->toString($this->sitePath);
        }
        return $stRet;
    }

    public function getJS() {
        $stRet = "";

        $d=date("Y-m-d");

        foreach ($this->arrJS as $JS) {
            $stRet .= $JS->toString($this->sitePath, $this->debug);
        }
        return $stRet;
    }

    public function getAll() {
        $stRet = $this->getCss();
        $stRet .= $this->getJS();
        return $stRet;
    }
}

class Css
{
    public $css;
    public $media;
    public $IE;
    public $integrity;
    public $crossorigin;
    public $forcecrossorigin;

    public function __construct($css, $media, $IE, $integrity, $crossorigin, $forcecrossorigin) {
        $this->css = $css;
        $this->media = $media;
        $this->IE = $IE;
        $this->integrity = $integrity;
        $this->crossorigin = $crossorigin;
        $this->forcecrossorigin = $forcecrossorigin;
    }

    public function toString($sitePath) {
        $stRet = "";
        if ($this->IE != "no") {
            $stRet .= "<!--[if lt IE " . $this->IE . "]>";
        }

        $sCss = "";
        if (substr($this->css, 0, 4)=="http") {
            $sCss = $this->css;
        } else {
            $sCss = $sitePath .$this->css;
        }

        $stRet .= '<link type="text/css" href="' . $sCss . '" rel="StyleSheet" media="' . $this->media . '"';
        if ($this->integrity != "") {
            $stRet .= ' integrity="'.$this->integrity.'"';
        }
        if ($this->crossorigin != "" || $this->forcecrossorigin) {
            $stRet .= ' crossorigin="'.$this->crossorigin.'"';
        }
        $stRet .= ' />';
        if ($this->IE != "no") {
            $stRet .= "<![endif]-->";
        }
        return $stRet;
    }
}

class JS {
    public $js;
    public $IE;
    public $integrity;
    public $crossorigin;
    public $forcecrossorigin;

    public function __construct($js, $IE, $integrity, $crossorigin, $forcecrossorigin) {
        $this->js = $js;
        $this->IE = $IE;
        $this->integrity = $integrity;
        $this->crossorigin = $crossorigin;
        $this->forcecrossorigin = $forcecrossorigin;
    }

    public function toString($sitePath, $debug) {
        $stRet = "";
        if ($this->IE != "no") {
            $stRet .= "<!--[if lt IE " . $this->IE . "]>";
        }
        $sJS = "";
        if (substr($this->js, 0, 4)=="http") {
            $sJS = $this->js;
        } else {
            $sJS = $sitePath .$this->js;
        }

        $stRet .= '<script type="text/javascript" src="' . $sJS;
        if (!$debug) {
            $d = time();
            $stRet .= '?d=' . $d .'"';
        } else {
            $stRet .='"';
        }

        if ($this->integrity != "") {
            $stRet .= ' integrity="'.$this->integrity.'"';
        }
        if ($this->crossorigin != "" || $this->forcecrossorigin) {
            $stRet .= ' crossorigin="'.$this->crossorigin.'"';
        }

        $stRet .= '></script>';

        if ($this->IE != "no") {
            $stRet .= "<![endif]-->";
        }

        return $stRet;
    }
}