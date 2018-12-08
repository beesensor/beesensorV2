<?php
class Db {

	/**
	 * * Declare instance **
	 */
	private $instances = NULL;
	private $config = NULL;
	/**
	 * the constructor is set to private so
	 * so nobody can create a new instance using new
	 */
	public function __construct($configs) {
		$this->config = $configs;
	}

	public function getInstance($connectionName=""):PDO {
		if ($connectionName=="") {
			$connectionName="main";
		}

		if ($this->instances==NULL) {
			$this->instances = array();
		}

		if (!array_key_exists($connectionName, $this->instances)) {
			foreach($this->config['connections'] as $key => $instance) {
				try {
					$instancePDO = new PDO ( "mysql:host=".$instance['server'].";dbname=".$instance['schema'], $instance['user'], $instance['password'], array (
							PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
					));
					$this->instances[$key] = $instancePDO;
				} catch (Exception $ex) {
					throw new Exception("Could not connect to database", 0, $ex);
				}
			}
		}

		return $this->instances[$connectionName];
	}

	/**
	 * Like the constructor, we make __clone private
	 * so nobody can clone the instance
	 */
	private function __clone() {
	}
}
/**
 * * end of class **
 */
?>
