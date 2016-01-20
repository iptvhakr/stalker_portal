<?php

namespace Model;

class BaseStalkerModel {

    protected $mysqlInstance;
    protected $reseller_id;
    protected $admin_id;
    protected $admin_login;

    public function __construct() {
        $this->mysqlInstance = \Mysql::getInstance();
        $this->reseller_id = NULL;
        $this->admin_id = NULL;
        $this->admin_login = NULL;
    }

    public function __call($name, $arguments) {
        if (!method_exists($this, $name)) {
            return FALSE;
        }
    }

    /**  Начиная с версии PHP 5.3.0  */
    public static function __callStatic($name, $arguments) {
        return FALSE;
    }

    public function setReseller($reseller_id){
        $this->reseller_id = $reseller_id;
    }

    public function setAdmin($admin_id, $admin_login){
        $this->admin_id = $admin_id;
        $this->admin_login = $admin_login;
    }

    public function getTableFields($table_name){
        return $this->mysqlInstance->query("DESCRIBE $table_name")->all();
    }
    
    public function getAllFromTable($table_name, $order = 'name', $groupby=''){
        $this->mysqlInstance->from($table_name)->orderby($order);
        if (!empty($groupby)) {
            $this->mysqlInstance->groupby($groupby);
        }
        return $this->mysqlInstance->get()->all();
    }
    
    public function existsTable($tablename, $temporary = FALSE){
        if (!$temporary) {
            return $this->mysqlInstance->query("SHOW TABLES LIKE '$tablename'")->first();
        } else {
            try{
                $this->mysqlInstance->query("SELECT count(*) FROM $tablename")->first();
                return TRUE;
            } catch (\MysqlException $ex) {
                return FALSE;
            }
        }
    }
    
    public function getCountUnreadedMsgsByUid($uid){
        return $this->mysqlInstance->query("select count(moderators_history.id) as counter from moderators_history,moderator_tasks where moderators_history.task_id = moderator_tasks.id and moderators_history.to_usr=$uid and moderators_history.readed=0 and moderator_tasks.archived=0 and moderator_tasks.ended=0")->first('counter');
    }
    
    public function getControllerAccess($uid, $reseller){
        if ($reseller) {
            $params['only_top_admin<>'] = 1;
        }
        if (!empty($uid)){
            $params["group_id"]=$uid;
        } else {
            $params["group_id"]=NULL;
        }
        $params[' 1=1 OR `hidden`='] = 1;

        return $this->mysqlInstance->from("adm_grp_action_access")->where($params)->orderby(array('controller_name' => 'ASC', 'action_name' => 'ASC'))->get()->all();
    }
    
    public function getDropdownAttribute($param) {
        return $this->mysqlInstance->from('admin_dropdown_attributes')->where($param)->get()->first();
    }
}
