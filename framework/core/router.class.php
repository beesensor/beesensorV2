<?php
class Router {
    /*
     * @the registry
     */
    private $registry;

    /*
     * @the controller path
     */
    private $path;
    private $args = array ();
    private $parameters = array();
    private $post = array();

    private $file;
    private $platform;
    private $controller;
    private $action;
    private $lang;
    private $expl;
    private $controllersPath;
    private $controllersDirectories;
    private $appConfig;
    private $localesPath;

    private $locales;

    function __construct($registry, $controllersPath, $controllerDirectories, $appConfig) {
        $this->registry = $registry;
        $this->controllersPath = $controllersPath;
        $this->controllersDirectories = $controllerDirectories;
        $this->appConfig = $appConfig;
        $this->localesPath = $registry->path->localesPath;
    }

    /**
     *
     * @load the controller
     *
     * @access public
     *
     * @return void
     *
     */
    public function loader() {

        /**
         * Setejam els locales que tenim
         */
        $this->getLocales();


        $this->getController();

        /**
         * * check if path i sa directory **
         */
        if (is_dir ( $this->path ) == false) {
            throw new Exception ( 'Invalid controller path: `' . $this->path . '`' );
        }

        /**
         * * if the file is not there diaf **
         */
        if (is_readable ( $this->file ) == false) {
            throw new Exception ( 'File not readable: `' . $this->file . '`' );
            //header ( "Location: " . $this->registry->sitePath );
        }

        /**
         * * include the controller **
         */

        include $this->file;

        /**
         * instanciam la clase del template
         */
        $this->registry->platform = $this->platform;
        $this->registry->lang = $this->lang;
        $this->registry->expl = $this->expl;
        $this->registry->appConfig = $this->appConfig;
        $this->registry->template = new Template($this->registry);

        /**
         * * a new controller class instance **
         */
        $class = $this->controller . 'Controller';
        $controller = new $class ( $this->registry );
        $controller->args = $this->args;
        $controller->parameters = $this->parameters;
        $controller->post = $this->post;

        /**
         * * check if the action is callable **
         */
        if (is_callable ( array ( $controller, $this->action) ) == false) {
            throw new Exception ( 'Action not callable: No function with name `' .$this->action .'` has been found in controller `' . $this->controller . '`' );
        } else {
            $action = $this->action;
        }

        /**
         * * run the action **
         */
       $controller->$action ();
    }

    /**
     * @get the locales
     * 
     * @access private
     * 
     * @return void
     */
    private function getLocales() {
        $this->locales = array();
        $directories = glob($this->localesPath."*", GLOB_ONLYDIR);
        if ($directories) {
            foreach($directories as $directory) {
                $dir = str_replace($this->localesPath, "", $directory);
                if (is_dir($this->localesPath.$dir)) {
                    $this->locales[]=$dir;
                }
            }
        }
    }

    /**
     *
     * @get the controller
     *
     * @access private
     *
     * @return void
     *
     */
    private function getController() {
        $this->platform = "home";
        $this->controller = "index";
        $this->action = "index";
        $this->lang = "es";
        $this->expl = 0;

        $route = (empty ( $_GET ['rt'] )) ? '' : $_GET ['rt'];
        if (!empty($route)) {
            if (substr($route, -1)=="/") {
                $route = substr($route, 0, strlen($route)-1);
            }

            $parts = explode("/", $route);
            $directories = $this->controllersDirectories;

            $fPlatform = false;
            $fLang = false;
            $fExpl = false;
            $fController = false;
            $fAction = false;

            foreach($parts as $part) {
                $goNext = false;
                if (!$fPlatform && in_array($part, $directories)) {
                    $fPlatform=true;
                    $goNext = true;
                    $this->platform = $part;
                }

                if (!$goNext) {
                    if (!$fLang && strlen($part)==2 && !is_numeric($part)) {
                        foreach($this->locales as $locale) {
                            if ($locale==$part) {
                                $this->lang = $locale;
                                $fLang = true;
                                $goNext = true;
                                break;
                            }
                        }
                    }
                }

                if (!$goNext) {
                    if (!$fExpl && is_numeric($part)) {
                        $this->expl = intval($part);
                        $fExpl = true;
                        $goNext = true;
                    }
                }

                if (!$goNext) {
                    if (!$fController) {
                        $this->controller = $part;
                        $fController = true;
                        $goNext = true;
                    }
                }

                if (!$goNext) {
                    if (!$fAction) {
                        $this->action = $part;
                        $fAction = true;
                        $goNext = true;
                    }
                }

                if (!$goNext) {
                    $this->args[] = $part;
                }
            }
        }

        if (isset($_GET)) {
            foreach ($_GET as $key => $value) {
                if ($key!="rt") {
                    $this->parameters[$key] = $value;
                }
            }
        }

        if (isset($_POST)) {
            foreach($_POST as $key => $value) {
                $this->post[$key] = $value;
            }
        }

        /**
         * * set the path **
         */
        $this->path = $this->controllersPath . $this->platform . '/';

        /**
         * * set the file path **
         */
        $this->file = $this->path . $this->controller . 'Controller.php';
    }
}
?>
