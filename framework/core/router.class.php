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

    private $file;
    private $platform;
    private $controller;
    private $action;
    private $controllersPath;
    private $controllersDirectories;
    private $appConfig;

    function __construct($registry, $controllersPath, $controllerDirectories, $appConfig) {
        $this->registry = $registry;
        $this->controllersPath = $controllersPath;
        $this->controllersDirectories = $controllerDirectories;
        $this->appConfig = $appConfig;
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
        $this->registry->appConfig = $this->appConfig;
        $this->registry->template = new Template($this->registry);

        /**
         * * a new controller class instance **
         */
        $class = $this->controller . 'Controller';
        $controller = new $class ( $this->registry );
        $controller->args = $this->args;
        $controller->parameters = $this->parameters;

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
     *
     * @get the controller
     *
     * @access private
     *
     * @return void
     *
     */
    private function getController() {

        /**
         * * get the route from the url **
         */
        $route = (empty ( $_GET ['rt'] )) ? '' : $_GET ['rt'];
        $c = null;

        if (empty($route)) {
            $this->platform = "home";
            $this->controller = "index";
            $this->action = "index";
        } else {
            if (substr($route, -1)=="/") {
                $route = substr($route, 0, strlen($route)-1);
            }

            $parts = explode("/", $route);
            $directories = $this->controllersDirectories;

            if (count($parts)==1) {

                if (in_array($parts[0], $directories)) {
                    $this->platform = $parts[0];
                    $c=1;
                } else {
                    $this->platform = "home";
                }

                if ($c==1) {
                    $this->controller = "index";
                } else {
                    $this->controller = $parts[0];
                }

                $this->action = "index";
                $c=1;
            } elseif (count($parts)==2) {

                if (in_array($parts[0], $directories)) {
                    $this->platform = $parts[0];
                    $c=1;
                } else {
                    $this->platform = "home";
                    $c=0;
                }
                $this->controller=$parts[$c];
                $c++;
                if ($c==1) {
                    $this->action=$parts[$c];
                } else {
                    $this->action="index";
                }
                $c++;
            } elseif (count($parts)==3) {
                $directories = $this->controllersDirectories;
                if (in_array($parts[0], $directories)) {
                    $this->platform = $parts[0];
                    $c=1;
                } else {
                    $this->platform = "home";
                    $c=0;
                }
                $this->controller=$parts[$c];
                $c++;
                if ($c==2) {
                    $this->action=$parts[$c];
                } else {
                    $this->action="index";
                }
                $c++;
            } else {
                $this->platform = $parts[0];
                $this->controller = $parts[1];
                $this->action = $parts[2];
                $c=3;

            }

            while (isset($parts[$c])) {
                $this->args[]=$parts[$c];
                $c++;
            }
        }

        if (isset($_GET)) {
            foreach ($_GET as $key => $value) {
                if ($key!="rt") {
                    $this->parameters[$key] = $value;
                }
            }
        }

        if (empty ( $this->controller )) {
            $this->controller = 'index';
        }

        /**
         * * Get action **
         */
        if (empty ( $this->action )) {
            $this->action = 'index';
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
