<?php
abstract class HomeController extends BaseController {
    /**
     * @var Header
     */
    protected $header;

    protected $sitePath;
    protected $token = null;
    protected $development = false;

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
        $this->development = $registry->devConfig["development"];
        $this->getHeader();
    }

    protected function setAlertDispatch($type, $header, $msg) {
        if (!$this->isSessionStarted()) {
            $this->sec_session_start();
        }

        $alert = new Alert($type, $header, $msg);
        $_SESSION["alert"]=$alert;
    }

    protected function setAlert($typw, $header, $msg) {
        $alert = new Alert($type, $header, $msg);
        $this->setTemplateVar("alert", $alert->toString());
    }

    protected function checkLoggedUer() {
        $logSession = new LogSession();
        $usuari = Usuari::get($this->db, $this->redisConfig, $logSession->getUser());
        if (!$usuari || !$logSession->loginCheck($usuari->password)) {
            header("Location: ".$this->sitePath);
            die();
        } else {
            $this->setTemplateVar("userName", $logSession->getValue("username"));
            $this->setTemplateVar("userLastAccess", $logSession->getValue("last_access"));
            $this->setTemplateVar("userEmail", $logSession->getUser());
            $this->setTemplateVar("userFoto", $logSession->getValue("foto"));
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

    private function getHeader() {
        $this->header = new Header($this->path->sitePath."/public", $this->development);
        $this->header->addCSS("https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css", "screen", "no", "sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u", "anonymous");
        $this->header->addCSS("https://use.fontawesome.com/releases/v5.6.1/css/all.css", "screen", "no", "sha384-gfdkjb5BdAXd+lj+gudLWI+BXq4IuLW5IT+brZEZsLFm++aCMlF1V92rMkPaX4PP", "anonymous");
        $this->header->addCSS("https://unpkg.com/ionicons@4.5.0/dist/css/ionicons.min.css");
        $this->header->addCSS( "https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic" );
        $this->header->addCSS("/css/AdminLTE.min.css");
        $this->header->addCSS("/css/skin-green.css");
        $this->header->addJS ("https://oss.maxcdn.com/libs/html5shiv/3.7.2/html5shiv.js", "9" );
        $this->header->addJS ("https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js", "9" );
        $this->header->addJS ("https://code.jquery.com/jquery-3.3.1.min.js", "no", "sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=", "anonymous");
        $this->header->addJS ("https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js");
        $this->header->addJS ("/js/AdminLTE/adminlte.min.js");
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