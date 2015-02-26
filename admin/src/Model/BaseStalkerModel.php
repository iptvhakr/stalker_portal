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
    
    public function getCountUnreadedMsgsByUid($uid){
        return $this->mysqlInstance->query("select count(moderators_history.id) as counter from moderators_history,moderator_tasks where moderators_history.task_id = moderator_tasks.id and moderators_history.to_usr=$uid and moderators_history.readed=0 and moderator_tasks.archived=0 and moderator_tasks.ended=0")->first('counter');
    }
    
    public function getControllerAccess($uid){
        $params = array(' hidden ' => 1);
        if (!empty($uid)){
            $params[" group_id"]=$uid;
        } else {
            $params[" isnull(group_id) and 1"]='1';
        }
        return $this->mysqlInstance->from("adm_grp_action_access")->where($params, 'OR')->get()->all();
    }
    
    public function getDropdownAttribute($param) {
        return $this->mysqlInstance->from('admin_dropdown_attributes')->where($param)->get()->first();
    }
}
