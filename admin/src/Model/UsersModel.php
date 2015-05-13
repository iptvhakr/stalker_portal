<?php

namespace Model;

class UsersModel extends \Model\BaseStalkerModel {

    public function __construct() {
        parent::__construct();
    }

    public function getTotalRowsUresList($where = array(), $like = array()) {
        $obj = $this->mysqlInstance->count()->from('users')->where($where);
        if (!empty($like)) {
            $obj = $obj->like($like, 'OR');
        }
        return $obj->get()->counter();
    }

    public function getUsersList($param, $report = FALSE) {
        $obj = $this->mysqlInstance->select($param['select'])->from('users');
        if (!$report) {
            $obj = $obj->join('tariff_plan', 'users.tariff_plan_id', 'tariff_plan.id', 'LEFT');
        } else {
            $obj = $obj->join('(SELECT @rank := 0) r', '1', '1', 'INNER');
        }
        $obj = $obj->where($param['where'])->like($param['like'], 'OR');
        if (!empty($param['order'])) {
            $obj = $obj->orderby($param['order']);    
        }
        if (!empty($param['limit']['limit'])) {
            $obj = $obj->limit($param['limit']['limit'], $param['limit']['offset']);
        }
        return $obj->get()->all();
    }

    public function toggleUserStatus($id, $status) {
        return $this->mysqlInstance->update('users', array('status' => $status, 'last_change_status' => 'NOW()'), array('id' => $id))->total_rows();
    }

    public function deleteUserById($id) {
        return $this->mysqlInstance->delete('users', array('id' => $id))->total_rows();
    }
    
    public function updateUserById($data, $id) {
        if (array_key_exists('last_change_status', $data) && empty($data['last_change_status'])) {
            $data['last_change_status'] = 'NOW()';
        }
        if (array_key_exists('id', $data) && $data['id'] == $id) {
            unset($data['id']);
        }
        
        if (array_key_exists('password', $data) && empty($data['password'])) {
            unset($data['password']);
        }
        return $this->mysqlInstance->update('users', $data, array('id' => $id))->total_rows();
    }

    public function insertUsers($data) {
        $data['created']='NOW()';
        return $this->mysqlInstance->insert('users', $data)->insert_id();
    }
    
    public function deleteUserFavItv($id) {
        return $this->mysqlInstance->delete('fav_itv', array('uid' => $id))->total_rows();
    }

    public function getUserFavItv($id) {
        return $this->mysqlInstance->from('fav_itv')->where(array('uid' => $id))->get()->first('fav_ch');
    }
    
    public function updateUserFavItv($data, $id) {
        return $this->mysqlInstance->update('fav_itv', $data, array('uid' => $id))->total_rows();
    }
    
    public function deleteUserFavVclub($id) {
        return $this->mysqlInstance->delete('fav_vclub', array('uid' => $id))->total_rows();
    }

    public function deleteUserFavMedia($id) {
        return $this->mysqlInstance->delete('media_favorites', array('uid' => $id))->total_rows();
    }

    public function getAllTariffPlans() {
        return $this->mysqlInstance->select('id, name')->from('tariff_plan')->orderby('name')->get()->all();
    }
    
    public function getSubChannelsDB($id){
        return $this->mysqlInstance->from('itv_subscription')->where(array('uid' => $id))->get()->first('sub_ch');
    }
    
    public function getCostSubChannelsDB($channels = array()){
        return empty($channels)? 0 : $this->mysqlInstance->select('SUM(cost) as total_cost')->from('itv')->in('id', $channels)->get()->first('total_cost');
    }
    
    public function getConsoleGroup($param = array()){
        return $this->mysqlInstance->select(array('*', '(select count(*) from stb_in_group as Si where Si.stb_group_id = Sg.id) as users_count'))->from('stb_groups as Sg')->where($param)->get()->all();
    }
    
    public function getConsoleGroupList($param = array()){
        $obj = $this->mysqlInstance->select($param['select'])
                        ->from('stb_in_group')
                        ->join('stb_groups', 'stb_in_group.stb_group_id', 'stb_groups.id', 'LEFT')
                        ->where($param['where'])->like($param['like'], 'OR')->orderby($param['order']);
        if (!empty($param['limit']['limit'])) {
            $obj = $obj->limit($param['limit']['limit'], $param['limit']['offset']);
        }
        
        return $obj->get()->all();
    }
    
    public function getTotalRowsConsoleGroupList($where = array(), $like = array()) {
        $obj = $this->mysqlInstance->count()->from('stb_in_group')->where($where);
        if (!empty($like)) {
            $obj = $obj->like($like, 'OR');
        }
        return $obj->get()->counter();
    }
    
    public function insertConsoleGroup($param){
        return $this->mysqlInstance->insert('stb_groups', $param)->insert_id();
    }
    
    public function updateConsoleGroup($data, $param){
        return $this->mysqlInstance->update('stb_groups', $data, $param)->total_rows();
    }
    
    public function deleteConsoleGroup($param){
        return $this->mysqlInstance->delete('stb_groups', $param)->total_rows();
    }
    
    public function checkLogin($name) {
        return $this->mysqlInstance->count()->from('users')->where(array('login' => $name))->get()->counter();
    }
    
    public function checkConsoleName($name) {
        return $this->mysqlInstance->count()->from('stb_groups')->where(array('name' => $name))->get()->counter();
    }
    
    public function deleteConsoleItem($param){
        return $this->mysqlInstance->delete('stb_in_group', $param)->total_rows();
    }
    
    public function insertConsoleItem($param) {
        return $this->mysqlInstance->insert('stb_in_group', $param)->insert_id();
    }
    
    public function getTotalRowsLogList($where = array(), $like = array()) {
        $obj = $this->mysqlInstance->count()->from('user_log')
                ->join('users', 'user_log.mac', 'users.mac', 'LEFT')
                ->where($where);
        if (!empty($like)) {
            $obj = $obj->like($like, 'OR');
        }        
        return $obj->get()->counter();
    }
    
    public function getLogList($param) {
        $obj = $this->mysqlInstance->select($param['select'])
                        ->from('user_log')->join('users', 'user_log.mac', 'users.mac', 'LEFT')
                        ->where($param['where'])->like($param['like'], 'OR')->orderby($param['order']);
        if (!empty($param['limit']['limit'])) {
            $obj = $obj->limit($param['limit']['limit'], $param['limit']['offset']);
        }
       
        return $obj->get()->all();
    }
    
    public function getITV($param) {
        return $this->mysqlInstance->from('itv')->where($param)->get()->first();
    }
    
    public function getVideo($param) {
        return $this->mysqlInstance->from('video')->where($param)->get()->first();
    }    
    
    public function getTarifPlanByUserID($id) {
        
        return $this->mysqlInstance->select(array(
                "P_P . *",
                "S_P.id as services_package_id",
                "S_P.`name` as `name`",
                "S_P.`type` as `type`",
                "S_P.`external_id` as external_id",
                "S_P.description as description",
                "S_P.service_type as service_type",
                "if(P_P.optional = 1, not isnull(U_P_S.id), 1) as `subscribed`"
            ))
            ->from('users as U')
            ->join("tariff_plan as T_P", "T_P.id", "if(U.tariff_plan_id <> 0,  U.tariff_plan_id, (select id FROM tariff_plan where user_default = 1))","LEFT")
            ->join("package_in_plan as P_P", "T_P.id", "P_P.plan_id", "LEFT")
            ->join("services_package as S_P", "S_P.id", "P_P.package_id", "INNER")
            ->join("user_package_subscription as U_P_S", "U.id", "U_P_S.user_id and P_P.package_id = U_P_S.package_id", "LEFT")
            ->where(array('U.id' => $id))
            ->orderby("P_P.optional, S_P.external_id")->get()->all();
    }
}
