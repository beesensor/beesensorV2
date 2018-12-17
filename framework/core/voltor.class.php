<?php
use Rain\Tpl;

Class Voltor {

    private $arrControllerPaths;
    private $arrModelsPaths;
    protected $registry;

    private function __construct()
    {
        $this->init();
        $this->arrControllerPaths = self::getControllerDirectories();
    }

    public static function run()
    {
        $voltor = new Voltor();

        try {
            $voltor->autoload();
            $voltor->dispatch();
        } catch (Exception $ex) {
            Voltor::showError($ex->getMessage(), print_r($ex, true));
        }
    }

    protected static function showError($message, $error) {
        $config = array(
            "tpl_dir"       => CORE_PATH."errortpl/",
            "cache_dir"     => CORE_PATH."errortpl/cache/",
            "debug"         => false,
            "tpl_ext"       => 'html',
            'path_replace'  => false
        );

        Tpl::configure( $config );

        $tpl = new Tpl;
        $tpl->assign("title", $message);
        $tpl->assign("exception", $error);
        $tpl->draw("error");
    }

    private function init()
    {
        error_reporting(0);
        ini_set('display_errors', 0);

        register_shutdown_function(function() {
            $error = error_get_last();

            if (!is_null($error)) {
                $m = explode("Stack trace:", $error["message"]);

                if ($m) {
                    $message = $m[0];
                } else {
                    $message = $error["message"];
                }
    
                Voltor::showError($message, print_r($error, true));
            }
        });

        //definim constants
        define("DS", DIRECTORY_SEPARATOR);

        define("ROOT", getcwd() . DS);

        define("APP_PATH", ROOT . 'application' . DS);

        define("FRAMEWORK_PATH", ROOT . "framework" . DS);

        define("PUBLIC_PATH", ROOT . "public" . DS);

        define("CONFIG_PATH", APP_PATH . "config" . DS);

        define("CONTROLLER_PATH", APP_PATH . "controllers" . DS);

        define("MODEL_PATH", APP_PATH . "models" . DS);

        define("VIEW_PATH", APP_PATH . "views" . DS);

        define("HELPER_PATH", FRAMEWORK_PATH . "helpers" . DS);

        define("CORE_PATH", FRAMEWORK_PATH . "core" . DS);

        define('DB_PATH', FRAMEWORK_PATH . "database" . DS);

        define("LIB_PATH", FRAMEWORK_PATH . "libraries" . DS);

        define("UPLOAD_PATH", PUBLIC_PATH . "uploads" . DS);
    }

    private function autoload()
    {
        //cercam si hi ha l'autoload de composer
        if (file_exists(LIB_PATH."/vendor/autoload.php")) {
            include(LIB_PATH."/vendor/autoload.php");
        }

        spl_autoload_register(array(__CLASS__,'load'));
    }

    private function dispatch()
    {
        $this->registry = new Registry ();

        include CONFIG_PATH . "config.php";
        $db = new Db($dbConfig);
        
        $path = new Path(PUBLIC_PATH, CONFIG_PATH, UPLOAD_PATH, VIEW_PATH, LIB_PATH, $devConfig);
        $this->registry->path = $path;
        $this->registry->db = $db;
        $this->registry->redisConfig=$redisConfig;
        $this->registry->devConfig=$devConfig;

        $router = new Router($this->registry, CONTROLLER_PATH, $this->arrControllerPaths, $appConfig);
        $router->loader();
    }

    private function load($className)
    {

        //miram si la clase és un model
        if (self::inludeFile(MODEL_PATH, $className, '.class.php')) {
            return;
        }
        //miram si la clase és de Core
        if (self::inludeFile(CORE_PATH, $className, '.class.php')) {
            return;
        }
        //miram si la clase és una llibreria
        if (self::inludeFile(LIB_PATH, $className, '.class.php')) {
            return;
        }
        if (self::inludeFile(HELPER_PATH, $className,'_helper.php')) {
            return;
        }
        if (self::inludeFile(DB_PATH, $className, '.class.php')) {
            return;
        }
        if (self::inludeFile(CONTROLLER_PATH, $className, '.php')) {
            return;
        }

        if ($this->arrControllerPaths!=null) {
            foreach($this->arrControllerPaths as $controllerPath) {
                if (self::inludeFile(CONTROLLER_PATH.$controllerPath."/", $className, '.php')) {
                    return;
                }
            }
        }

        $this->getModelDirectories(MODEL_PATH);

        if ($this->arrModelsPaths!=null) {
            foreach($this->arrModelsPaths as $modelPath) {
                if (self::inludeFile(MODEL_PATH.$modelPath."/", $className, '.class.php')) {
                    return;
                }
            }
        }

        throw new Exception("Class ".$className." not found");
    }

    private function getModelDirectories($path) {
        $directories = glob($path."*", GLOB_ONLYDIR);
        if ($directories!=null && count($directories)>0) {
            if ($this->arrModelsPaths==null) {
                $this->arrModelsPaths=array();
            }
            foreach($directories as $directory) {
                $this->arrModelsPaths[] = str_replace($path, "", $directory);
                $this->getModelDirectories($directory."/");
            }
        }
    }

    private static function getControllerDirectories() {
        $directories=glob(CONTROLLER_PATH . "*", GLOB_ONLYDIR);
        $dirRet = array();
        if ($directories!=null && count($directories)>0) {
            foreach($directories as $directory) {
                $dirRet[] = str_replace(CONTROLLER_PATH, "", $directory);
            }
        }
        return $dirRet;
    }

    private static function inludeFile($path, $className, $sufix) {
        $filename = strtolower(substr($className, 0, 1)).substr($className, 1).$sufix;
        $file = $path . $filename;
        if (file_exists ( $file ) == false) {
            $filename = strtolower ( $className ).$sufix;
            $file = $path . $filename;
            if (file_exists($file) == false) {
                return false;
            }
        }
        include($file);
        return true;
    }

    //Carrega un template per mostrar l'error
    private function error($ex) {

    }
}
?>
