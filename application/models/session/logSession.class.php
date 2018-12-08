<?php
Abstract Class LogSession {

    public function __construct() {
        if (!$this->isSessionStarted()) {
            $this->sec_session_start();
        }
    }

    /*
    public function loginCheck($password) {

        if (!LogSession::isSessionStarted()) {
            LogSession::sec_session_start();
        }

        if (isset ( $_SESSION ['user'], $_SESSION ['login_string'] )) {
            $user = $_SESSION ['user'];
            $login_string = $_SESSION ['login_string'];
            $user_browser = $_SERVER ['HTTP_USER_AGENT'];

            try {
                $login_check = hash ( 'sha512', $password . $user_browser );
                return $login_check == $login_string;
            } catch (Exception $exception) {
                return false;
            }
        } else {
            return false;
        }
    }

	public function logout() {
		if (!LogSession::isSessionStarted()) {
            LogSession::sec_session_start();
        }

		unset($_SESSION['user']);
		unset($_SESSION['login_string']);
	}

    public function getUser() {
        if (isset($_SESSION['user'])) {
            return $_SESSION['user'];
        } else {
            return $this->user;
        }
    }
    */
    public function setValue($key, $value) {
        $_SESSION[$key] = $value;
    }

    public function getValue($key) {
        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        } else {
            return null;
        }
    }

    /*
    public static function setLogin($user, $password, $values=null) {
        if (!LogSession::isSessionStarted()) {
            LogSession::sec_session_start();
        }

        $user_browser = $_SERVER ['HTTP_USER_AGENT'];
        $_SESSION ['user'] = $user;
        $_SESSION ['login_string'] = hash ( 'sha512', $password . $user_browser );

        if (isset($values)) {
            foreach($values as $key=>$value) {
                $_SESSION [$key] = $value;
            }
        }
    }
    */

    public static function sec_session_start() {
        $session_name = 'sec_session_id';
        $secure = false;
        $httponly = true;

        if (ini_set ( 'session.use_only_cookies', 1 ) === FALSE) {
            header ( "Location: ../" );
            exit ();
        }

        $cookieParams = session_get_cookie_params ();
        session_set_cookie_params ( $cookieParams ["lifetime"], $cookieParams ["path"], $cookieParams ["domain"], $secure, $httponly );
        // Configura el nom de sessió
        session_name ( $session_name );
        session_start (); // Inicia la sessió PHP.
        session_regenerate_id (); // Regenera la sessió, elimina l'anterior.
    }

    public static function isSessionStarted() {
        if ( php_sapi_name() !== 'cli' ) {
            if ( version_compare(phpversion(), '5.4.0', '>=') ) {
                return session_status() === PHP_SESSION_ACTIVE ? TRUE : FALSE;
            } else {
                return session_id() === '' ? FALSE : TRUE;
            }
        }
        return FALSE;
    }
}
