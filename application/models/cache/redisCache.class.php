<?php
Class RedisCache {

    private $redisConn;
    private $key;
    private $prefix;
    private $arrCache = null;
    private $mapper = null;

    public function __construct($key, $redisConfig, $connectionName="main", $prefix="bsCache_", $autoLoad=true) {
        $this->key = $key;
        $this->prefix = $prefix;
        
        if ($connectionName=="") {
			$connectionName="main";
        }
        
        if ($redisConfig && array_key_exists("connections", $redisConfig)) {
            $redisConnections = $redisConfig["connections"];
            if ($redisConnections && is_array($redisConnections)) {
                if (array_key_exists($connectionName, $redisConnections)) {
                    $this->redisConn = new Predis\Client($redisConnections[$connectionName]);

                    if ($autoLoad) {
                        $this->loadCache();
                    }
                } else {
                    throw new Exception("Redis connection with name ".$connectionName." not found in Redis configuration");
                }
            } else {
                throw new Exception("No Redis connections found.");
            }
        } else {
            throw new Exception("Redis connection not properly configured.");
        }
    }

    public function getItemDeserialized($keyItem, $object) {
        $item = $this->getItem($keyItem);
        if ($item!=null) {
            if ($this->mapper==null) {
                $this->mapper = new JsonMapper();
            }

            if (is_array($item)) {
                $obj = $this->toObject($item);
            }

            return $this->mapper->map($obj, $object);
            
        } else {
            return null;
        }
    }

    public function getDeserialized($object) {
        $this->checkLoadedCache();
        if (!is_null($this->arrCache)) {
            if ($this->mapper==null) {
                $this->mapper = new JsonMapper();
            }
            $arrayItems = array();
            foreach($this->arrCache as $key => $item) {
                if ($key!=".") {
                    $obj = $this->toObject($item);
                    $arrayItems[$key] = $obj;
                }
            }
            return $this->mapper->mapArray($arrayItems, array(), $object);
        } else {
            return null;
        }
    }

    private function toObject($array) {
        $obj = new stdClass();
        foreach ($array as $key => $val) {
            $obj->$key = is_array($val) ? $this->toObject($val) : $val;
        }
        return $obj;
    }

    public function getItem($keyItem) {
        $this->checkLoadedCache();

        if (!is_null($this->arrCache) && key_exists($keyItem, $this->arrCache)) {
            return $this->arrCache[$keyItem];
        } else {
            return null;
        }
    }

    public function setItem($keyItem, $item) {
        if (is_null($this->arrCache)) {
            $this->arrCache = array();
        }
        $this->arrCache[$keyItem] = $item;
    }

    public function setCache($seconds) {
        $this->redisConn->mset($this->prefix.$this->key, json_encode($this->arrCache));
        $this->redisConn->expire($this->prefix.$this->key, $seconds);
        $this->arrCache = null;
    }

    public function clear() {
        $this->redisConn->del($this->prefix.$this->key);
        $this->arrCache = array();
    }

    public function setCompletelyLoaded($setCache=false, $seconds = 24 * 60 * 60) {
        $this->setItem(".",".");
        if ($setCache) {
            $this->setCache($seconds);
        }
    }

    public function isCompletelyLoaded() {
        if (is_null($this->arrCache)) {
            return false;
        } else {
            return array_key_exists(".", $this->arrCache);
        }
    }

    private function checkLoadedCache() {
        if (is_null($this->arrCache)) {
            $this->loadCache();
            if (is_null($this->arrCache)) {
                $this->arrCache = array();
            }
        }
    }

    private function loadCache() {
        $this->arrCache = json_decode($this->redisConn->get($this->prefix.$this->key), true);
    }
}