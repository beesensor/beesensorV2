<?php
Class Path {
    public $publicPath;
    public $configPath;
    public $uploadPath;
    public $templatesPath;
    public $sitePath;
    public $libPath;
    public $debug;

    public function __construct($public, $config, $upload, $templates, $lib, $devConfig) {
        $this->publicPath = $public;
        $this->configPath = $config;
        $this->uploadPath = $upload;
        $this->templatesPath = $templates;
        $this->libPath = $lib;
        $this->debug = false;
        
        $devDir = "";
        if ($devConfig) {
            if (is_array($devConfig)) {
                if (array_key_exists("development", $devConfig)) {
                    if ($devConfig["development"] === true) {
                        $this->debug = true;
                        if (array_key_exists('devdir', $devConfig)) {
                            if ($devConfig['devdir']!="") {
                                $devDir = "/".$devConfig['devdir'];
                            }
                        }
                    }
                }
            }
        }

        $this->sitePath = "http://".$_SERVER["SERVER_NAME"].$devDir;
    }
}
