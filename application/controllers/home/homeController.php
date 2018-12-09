<?php
abstract class HomeController extends BaseController {
    protected $header;
    protected $sitePath;
    protected $token = null;

    abstract protected function setBreadCrumb();

    public function __construct($registry) {
        parent::__construct($registry);
        $this->setTemplateVar("alert", $this->getAlert());
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
}