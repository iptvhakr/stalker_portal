<?php

namespace Model;

class BaseStalkerModel {

    protected $mysqlInstance;

    public function __construct() {
        $this->mysqlInstance = \Mysql::getInstance();
    }

    public function __call($name, $arguments) {
        if (!method_exists($this, $name)) {
            return FALSE;
        }
    }

    /**  Начиная с версии PHP 5.3.0  */
    public static function __callStatic($name, $arguments) {
        if (!method_exists($this, $name)) {
            return FALSE;
        }
    }

    public function getTableFields($table_name){
        return $this->mysqlInstance->query("DESCRIBE $table_name")->all();
    }
    
    public function getAllFromTable($table_name, $order = 'name', $groupby=''){
        $result = $this->mysqlInstance->from($table_name)->orderby($order);
        if (!empty($groupby)) {
            $result = $result->groupby($groupby);
        }
        return $result->get()->all();
    }
    
    public function existsTable($tablename, $temporary = FALSE){
        if (!$temporary) {
            return $this->mysqlInstance->query("SHOW TABLES LIKE '$tablename'")->first();
        } else {
            try{
                $result = $this->mysqlInstance->query("SELECT count(*) FROM $tablename")->first();
                return TRUE;
            } catch (\MysqlException $ex) {
                return FALSE;
            }
        }
    }
}
