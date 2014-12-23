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
}
