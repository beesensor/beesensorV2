<?php
abstract class Model {
    /**
     * @var string|null
     */
    public $fecAlt;

    /**
     * @var string|null
     */
    public $fecMod;

    /**
     * @var string|null
     */
    public $fecBaj;

    protected function __construct($fecAlt = null, $fecMod=null, $fecBaj=null) {
        $this->fecAlt = $fecAlt;
        $this->fecMod = $fecMod;
        $this->fecBaj = $fecBaj;
    }

    protected function setActualDates(bool $insereix) {
        $this->fecMod = date("Y-m-d");
        if ($insereix) {
            $this->fecCre = date("Y-m-d");
        }
    }

    protected function setDeletionDate() {
        $this->fecBaj = date("Y-m-d");
    }

    abstract public function insert(Db $db, array $redisConfig);

    abstract public function update(Db $db, array $redisConfig);

    abstract public function delete(Db $db, array $redisConfig);
}