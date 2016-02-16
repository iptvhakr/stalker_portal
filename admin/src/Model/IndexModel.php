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
        $this->mysqlInstance->from('users')->count()
            ->where(array(
                'UNIX_TIMESTAMP(keep_alive)'.($state == 'online'?'>':'<=') => time()-\Config::get('watchdog_timeout')*2
            ));
        if (!empty($this->reseller_id)) {
            $this->mysqlInstance->where(array('reseller_id' => $this->reseller_id));
        }
        return $this->mysqlInstance->get()->counter();
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

    public function getStorages(){
        return $this->mysqlInstance->from('storages')->where(array('status' => 1))->get()->all();
    }

    public function getStoragesRecords($storage_name, $total_storage_loading = FALSE)
    {
        $this->mysqlInstance->select(array('storage_name', 'now_playing_type', 'count(now_playing_type) as `count`'))
            ->from('users')
            ->where(array(
                'UNIX_TIMESTAMP(keep_alive)>' => time() - \Config::get('watchdog_timeout') * 2,
                'storage_name' => $storage_name,
            ));
        if (!empty($this->reseller_id) && !$total_storage_loading) {
            $this->mysqlInstance->where(array('reseller_id' => $this->reseller_id));
        }
        if (!$total_storage_loading) {
            $this->mysqlInstance->in('now_playing_type', array(2, 11, 14));
            return $this->mysqlInstance->groupby('now_playing_type')
                ->get()
                ->all();
        } else {
            return $this->mysqlInstance->groupby('now_playing_type')
                ->get()
                ->first('count');
        }

    }

    public function getStreamServer(){
        return $this->mysqlInstance->from('streaming_servers')->where(array('status' => 1))->orderby('name')->get()->all();
    }

    public function getStreamServerStatus($server_id, $total_server_loading = FALSE){
        $this->mysqlInstance
            ->from('users')
            ->where(array(
                'now_playing_streamer_id' => $server_id,
                'keep_alive>' => date(\Mysql::DATETIME_FORMAT, time() - \Config::get('watchdog_timeout') * 2),
                'now_playing_type' => 1
            ));
        if (!empty($this->reseller_id) && $total_server_loading) {
            $this->mysqlInstance->where(array('reseller_id' => $this->reseller_id));
        }

        return $this->mysqlInstance->count()
            ->get()
            ->counter();

    }

    public function getCurActivePlayingType($type = 100){
        $this->mysqlInstance
            ->from('users')
            ->count()
            ->where(array(
                'now_playing_type' => $type,
                'keep_alive>'      => date(\Mysql::DATETIME_FORMAT, time() - \Config::get('watchdog_timeout')*2)
            ));
        if (!empty($this->reseller_id)) {
            $this->mysqlInstance->where(array('reseller_id' => $this->reseller_id));
        }
        return $this->mysqlInstance->get()->counter();
    }

    public function getUsersActivity(){
        return $this->mysqlInstance->select(array('unix_timestamp(`time`) as `time`', 'users_online'))->from('users_activity')->get()->all();
    }
}
