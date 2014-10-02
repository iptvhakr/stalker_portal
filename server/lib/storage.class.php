<?php

//namespace Stalker\Lib;

class Storage
{

    private $storage;
    private $max_failures;
    private $stat_period;

    public function __construct($init_info = array()){

        if (empty($init_info)){
            return;
        }

        if (!empty($init_info['id'])){
            $this->storage = $this->getById($init_info['id']);
        }else if (!empty($init_info['name'])){
            $this->storage = $this->getByName($init_info['name']);
        }

        if (empty($this->storage)){
            throw new ErrorException("Storage can not be initialized with values: ".var_export($init_info, true));
        }

        $this->max_failures = Config::getSafe("max_storage_failures", 3);
        $this->stat_period  = Config::getSafe("storage_stat_period", 300);

        return $this->storage;
    }

    private function checkIfInitialized(){
        if (empty($this->storage)){
            throw new ErrorException("Storage not initialized");
        }else{
            return true;
        }
    }

    public function markAsFailed($description = ""){

        $this->checkIfInitialized();

        Mysql::getInstance()
            ->insert('storages_failure', array("storage_id" => $this->storage['id'], 'description' => $description))
            ->insert_id();

        $failures = Mysql::getInstance()->from('storages_failure')
            ->count()
            ->where(array(
                'storage_id' => $this->storage['id'],
                'added>'     => date('Y-m-d H:i:s', time() - $this->stat_period)
            ))
            ->get()->counter();

        if ($failures >= $this->max_failures){
            $this->setOff();
        }
    }

    public function getByName($name){
        return Mysql::getInstance()->from('storages')->where(array('storage_name' => $name))->get()->first();
    }

    public function getById($id){
        return Mysql::getInstance()->from('storages')->where(array('id' => $id))->get()->first();
    }

    public function setOff(){

        $this->checkIfInitialized();

        if ($this->storage['status'] == 0){
            return true;
        }

        $result = Mysql::getInstance()->update('storages', array('status' => 0), array('id' => $this->storage['id']));

        if ($result){
            Mysql::getInstance()->insert('master_log',
                          array(
                              'log_txt' => "Storage ".$this->storage['storage_name']." has been disabled after ".$this->max_failures." failures in ".$this->stat_period."s",
                              'added'   => 'NOW()'
                          ));
        }

        return $result;
    }
}

?>