<?php
abstract class BaseController {

    protected $path;
    protected $db;
    protected $redisConfig;
    protected $template;
    protected $platform;
    protected $lang;
    protected $expl;
    protected $registry;

    public $args = array ();
    public $parameters = array();
    public $post = array();

    function __construct($registry) {
        $this->path = $registry->path;
        $this->db = $registry->db;
        $this->dbi = $this->db->getInstance();
        $this->redisConfig = $registry->redisConfig;
        $this->template = $registry->template;
        $this->platform = $registry->platform;
        $this->lang = $registry->lang;
        $this->expl = $registry->expl;
    }

    abstract public function index();

    protected  function setTemplateVar($var, $value) {
        $this->template->$var=$value;
    }

    protected function dispatch($url) {
        if (substr($url, 0, 1)!="/") {
            $url = "/" . $url;
        }
        header("Location: ".$this->path->sitePath."/" . $this->platform .$url);
    }

    protected function isPost() {
        return ((isset($_POST)) && (count($_POST)>0));
    }

    protected function sec_session_start() {
        LogSession::sec_session_start();
    }

    protected function isSessionStarted() {
        return LogSession::isSessionStarted();
    }

    /**
     * @method loadView
     * @param string $view Nom de la vista a carregar
     * @return void
     */
    protected function loadView($view) {
        $this->template->show($view);
    }
}
