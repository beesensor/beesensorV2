<?php

use Rain\Tpl;

class Template {
    /*
     * @the registry @access private
     */
    private $registry;

    /*
     * @Variables array @access private
     */
    private $vars = array ();

    /**
     *
     * @constructor
     *
     * @access public
     *
     * @return void
     *
     */
    function __construct($registry) {
        $this->registry = $registry;
    }
    public function __get($index) {
        return $this->vars [$index];
    }

    /**
     *
     * @set undefined vars
     *
     * @param string $index
     *
     * @param mixed $value
     *
     * @return void
     *
     */
    public function __set($index, $value) {
        $this->vars [$index] = $value;
    }
    public function show($name) {

        $debug = false;
        if (isset($this->registry->debug)) {
            $debug = $this->registry->debug;
        }

        $config = array(
            "tpl_dir"       => $this->registry->path->templatesPath . $this->registry->platform."/",
            "cache_dir"     => $this->registry->path->libPath . "vendor/rain/raintpl/cache/",
            "debug"         => $debug, // set to false to improve the speed
            "tpl_ext"       => 'html',
            'path_replace'  => false
        );

        Tpl::configure( $config );
        Tpl::registerPlugin( new Tpl\Plugin\PathReplace() );

        $tpl = new Tpl;

        // Carrega les variables
        foreach ( $this->vars as $key => $value ) {
           $tpl->assign($key, $value);
        }

        $tpl->draw($name);
    }
}
?>
