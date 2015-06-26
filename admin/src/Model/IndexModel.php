<?php

namespace Model;

class IndexModel extends \Model\BaseStalkerModel {

    public function __construct() {
        parent::__construct();
    }
    
    public function deleteDropdownAttribute($param) {
        return $this->mysqlInstance->delete('admin_dropdown_attributes', $param)->total_rows();
    }
    
    public function insertDropdownAttribute($param){
        return $this->mysqlInstance->insert('admin_dropdown_attributes', $param)->insert_id();
    }

    public function get_users($state = 'online'){
        return $this->mysqlInstance->from('users')->count()
            ->where(array(
                'UNIX_TIMESTAMP(keep_alive)'.($state == 'online'?'>':'<=') => time()-\Config::get('watchdog_timeout')*2
            ))
            ->get()->counter();
    }

    public function getCountForStatistics($table, $where=array(), $groupby=''){
        $this->mysqlInstance->from($table)->count();
        if (!empty($where)) {
            $this->mysqlInstance->where($where);
        }
        if (!empty($groupby)) {
            $this->mysqlInstance->groupby($groupby);
        }
        return $this->mysqlInstance->get()->counter();
    }
}
