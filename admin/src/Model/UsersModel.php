<?php

namespace Model;

class UsersModel extends \Model\BaseStalkerModel {

    public function __construct() {
        parent::__construct();
    }

    public function getTotalRowsUresList($where = array(), $like = array(), $in = array()) {
        if (!empty($this->reseller_id)) {
            $where['reseller_id'] = $this->reseller_id;
        }
        $this->mysqlInstance->count()->from('users')->where($where);
        if (!empty($in)) {
            list($field, $data) = each($in);
            $this->mysqlInstance->in($field, $data);
        }
        if (!empty($like)) {
            $this->mysqlInstance->like($like, 'OR');
        }
        return $this->mysqlInstance->get()->counter();
    }

    public function getUsersList($param, $report = FALSE) {
        if (!empty($param['select'])) {
            $this->mysqlInstance->select($param['select']);
        }
        $this->mysqlInstance->from('users');
        if (!empty($this->reseller_id)) {
            $param['where']['reseller_id'] = $this->reseller_id;
        }
        if (!$report) {
            $this->mysqlInstance->join('tariff_plan', 'users.tariff_plan_id', 'tariff_plan.id', 'LEFT');
            $this->mysqlInstance->join('reseller', 'users.reseller_id', 'reseller.id', 'LEFT');
        } else {
            $this->mysqlInstance->join('(SELECT @rank := 0) r', '1', '1', 'INNER');
        }

        if (!empty($param['where'])) {
            $this->mysqlInstance->where($param['where']);
        }
        if (!empty($param['like'])) {
            $this->mysqlInstance->like($param['like'], 'OR');
        }

        if (!empty($param['in'])) {
            list($field, $data) = each($param['in']);
            $this->mysqlInstance->in($field, $data);
        }

        if (!empty($param['order'])) {
            $this->mysqlInstance->orderby($param['order']);
        }
        if (!empty($param['limit']['limit'])) {
            $this->mysqlInstance->limit($param['limit']['limit'], $param['limit']['offset']);
        }
        return $this->mysqlInstance->get()->all();
    }

    public function toggleUserStatus($id, $status) {
        $where = array('id' => $id);
        if (!empty($this->reseller_id)) {
            $where['reseller_id'] = $this->reseller_id;
        }
        return $this->mysqlInstance->update('users', array('status' => $status, 'last_change_status' => 'NOW()'), $where)->total_rows();
    }

    public function deleteUserById($id) {
        $where = array('id' => $id);
        if (!empty($this->reseller_id)) {
            $where['reseller_id'] = $this->reseller_id;
        }
        return $this->mysqlInstance->delete('users', $where)->total_rows();
    }
    
    public function updateUserById($data, $id) {
        $where = array('id' => $id);
        if (!empty($this->reseller_id)) {
            $where['reseller_id'] = $this->reseller_id;
        }
        if (array_key_exists('last_change_status', $data) && empty($data['last_change_status'])) {
            $data['last_change_status'] = 'NOW()';
        }
        if (array_key_exists('id', $data) && $data['id'] == $id) {
            unset($data['id']);
        }
        
        if (array_key_exists('password', $data) && empty($data['password'])) {
            unset($data['password']);
        }
        return $this->mysqlInstance->update('users', $data, $where)->total_rows();
    }

    public function insertUsers($data) {
        $data['created']='NOW()';
        if (!empty($this->reseller_id)) {
            $data['reseller_id'] = $this->reseller_id;
        }
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
        return $this->mysqlInstance->select('id, name, user_default')->from('tariff_plan')->orderby('name')->get()->all();
    }
    
    public function getSubChannelsDB($id){
        return $this->mysqlInstance->from('itv_subscription')->where(array('uid' => $id))->get()->first('sub_ch');
    }
    
    public function getCostSubChannelsDB($channels = array()){
        return empty($channels)? 0 : $this->mysqlInstance->select('SUM(cost) as total_cost')->from('itv')->in('id', $channels)->get()->first('total_cost');
    }

    public function getTotalRowsConsoleGroup($where = array(), $like = array()) {
        $params = array(
            /*'select' => array("*"),*/
            'where' => $where,
            'like' => array(),
            'order' => array()
        );
        if (!empty($like)) {
            $params['like'] = $like;
        }
        return $this->getConsoleGroup($params, TRUE);
    }

    public function getConsoleGroup($param, $counter = FALSE){

        if (!empty($param['select'])) {
            $this->mysqlInstance->select($param['select']);
        }
        $this->mysqlInstance->from('stb_groups as Sg')
            ->join('reseller as R', 'Sg.reseller_id', 'R.id', 'LEFT');
        if (!empty($param['where'])) {
            $this->mysqlInstance->where($param['where']);
        }
        if (!empty($param['like'])) {
            $this->mysqlInstance->like($param['like'], 'OR');
        }
        if (!empty($param['order'])) {
            $this->mysqlInstance->orderby($param['order']);
        }

        if (!empty($param['limit']['limit'])) {
            $this->mysqlInstance->limit($param['limit']['limit'], $param['limit']['offset']);
        }

        return ($counter) ? $this->mysqlInstance->count()->get()->counter() : $this->mysqlInstance->get()->all();
    }
    
    public function getConsoleGroupList($param = array()){
        if (!empty($this->reseller_id)) {
            $param['where']['reseller_id'] = $this->reseller_id;
        }
        if (!empty($param['select'])) {
            $this->mysqlInstance->select($param['select']);
        }
        $this->mysqlInstance->from('stb_in_group')
                        ->join('stb_groups', 'stb_in_group.stb_group_id', 'stb_groups.id', 'LEFT');
        if (array_key_exists('where', $param)) {
            $this->mysqlInstance->where($param['where']);
        }
        if (array_key_exists('like', $param)) {
            $this->mysqlInstance->like($param['like'], 'OR');
        }
        if (array_key_exists('order', $param)) {
            $this->mysqlInstance->orderby($param['order']);
        }
        if (!empty($param['limit']['limit'])) {
            $this->mysqlInstance->limit($param['limit']['limit'], $param['limit']['offset']);
        }
        
        return $this->mysqlInstance->get()->all();
    }
    
    public function getTotalRowsConsoleGroupList($where = array(), $like = array()) {
        $this->mysqlInstance->count()->from('stb_in_group')->where($where);
        if (!empty($like)) {
            $this->mysqlInstance->like($like, 'OR');
        }
        return $this->mysqlInstance->get()->counter();
    }
    
    public function insertConsoleGroup($param){
        if (!empty($this->reseller_id)) {
            $param['reseller_id'] = $this->reseller_id;
        }
        return $this->mysqlInstance->insert('stb_groups', $param)->insert_id();
    }
    
    public function updateConsoleGroup($data, $param){
        if (!empty($this->reseller_id)) {
            $param['reseller_id'] = $this->reseller_id;
        }
        return $this->mysqlInstance->update('stb_groups', $data, $param)->total_rows();
    }
    
    public function deleteConsoleGroup($param){
        if (!empty($this->reseller_id)) {
            $param['reseller_id'] = $this->reseller_id;
        }
        return $this->mysqlInstance->delete('stb_groups', $param)->total_rows();
    }
    
    public function checkLogin($params) {
        if (!is_array($params)) {
            $params = array('login' => $params);
        }
        return $this->mysqlInstance->count()->from('users')->where($params)->get()->counter();
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
        $this->mysqlInstance->count()->from('user_log');
        if (!empty($this->reseller_id)) {
            $where['reseller_id'] = $this->reseller_id;
            $this->mysqlInstance->join('users', 'user_log.mac', 'users.mac', 'LEFT');
        }
        $this->mysqlInstance->where($where);
        if (!empty($like)) {
            $this->mysqlInstance->like($like, 'OR');
        }
        return $this->mysqlInstance->get()->counter();
    }
    
    public function getLogList($param) {
        if (!empty($param['select'])) {
            $this->mysqlInstance->select($param['select']);
        }
        $this->mysqlInstance->from('user_log');
        if (!empty($this->reseller_id)) {
            $param['where']['reseller_id'] = $this->reseller_id;
            $this->mysqlInstance->join('users', 'user_log.mac', 'users.mac', 'LEFT');
        }
        if (array_key_exists('where', $param)) {
            $this->mysqlInstance->where($param['where']);
        }
        if (array_key_exists('like', $param)) {
            $this->mysqlInstance->like($param['like'], 'OR');
        }
        if (array_key_exists('order', $param)) {
            $this->mysqlInstance->orderby($param['order']);
        }
        if (!empty($param['limit']['limit'])) {
            $this->mysqlInstance->limit($param['limit']['limit'], (array_key_exists('offset', $param['limit'])?$param['limit']['offset']: FALSE));
        }
       
        return $this->mysqlInstance->get()->all();
    }
    
    public function getITV($param) {
        return $this->mysqlInstance->from('itv')->where($param)->get()->first();
    }
    
    public function getVideo($param) {
        return $this->mysqlInstance->from('video')->where($param)->get()->first();
    }    
    
    public function getTarifPlanByUserID($id) {
        $where = array('U.id' => $id);
        if (!empty($this->reseller_id)) {
            $where['reseller_id'] = $this->reseller_id;
        }
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
            ->where($where)
            ->orderby("P_P.optional, S_P.external_id")->get()->all();
    }

    public function getReseller($param, $counter = FALSE) {
        if (!empty($param['select'])) {
            $this->mysqlInstance->select($param['select']);
        }
        $this->mysqlInstance->from("reseller as R");
        if (!empty($param['where'])) {
            $this->mysqlInstance->where($param['where']);
        }
        if (!empty($param['like'])) {
            $this->mysqlInstance->like($param['like'], 'OR');
        }
        if (!empty($param['order'])) {
            $this->mysqlInstance->orderby($param['order']);
        }

        if (!empty($param['limit']['limit'])) {
            $this->mysqlInstance->limit($param['limit']['limit'], $param['limit']['offset']);
        }

        return ($counter) ? $this->mysqlInstance->count()->get()->counter() : $this->mysqlInstance->get()->all();
    }

    public function updateResellerMemberByID($table_name, $id, $target_id){
        return $this->mysqlInstance->update($table_name, array("reseller_id" => $target_id), array("id" => $id))->total_rows();
    }

    public function getFilterSet($params){
        return $this->mysqlInstance->from('filter_set')->where($params)->get()->all();
    }

    public function insertFilterSet($params){
        return $this->mysqlInstance->insert('filter_set', $params)->insert_id();
    }

    public function updateFilterSet($id, $params){
        return $this->mysqlInstance->update('filter_set', $params, array('id'=>$id))->total_rows();
    }

    public function getTotalRowsUsersFilters($where = array(), $like = array()) {
        $params = array(
            'where' => $where
        );
        if (!empty($like)) {
            $params['like'] = $like;
        }
        return $this->getUsersFiltersList($params, TRUE);
    }

    public function getUsersFiltersList($param, $counter = FALSE) {
        $where = array();
        if (!empty($this->admin_login) && $this->admin_login != 'admin') {
            $where = array('admin_id' => $this->admin_id, 'for_all' => 1);
        }
        if (!empty($param['select'])) {
            $this->mysqlInstance->select($param['select']);
        }
        $this->mysqlInstance->from("filter_set as F_S")
            ->join("administrators as A", "F_S.admin_id", "A.id", "LEFT")
            ->join("reseller as R", "A.reseller_id", "R.id", "LEFT");
        if (!empty($param['where'])) {
            $this->mysqlInstance->where($param['where']);
        }
        if (!empty($where)) {
            $this->mysqlInstance->where($where, ' OR ');
        }
        if (!empty($param['like'])) {
            $this->mysqlInstance->like($param['like'], ' OR ');
        }
        if (!empty($param['order'])) {
            $this->mysqlInstance->orderby($param['order']);
        }
        if (!empty($param['limit']['limit'])) {
            $this->mysqlInstance->limit($param['limit']['limit'], $param['limit']['offset']);
        }

        return ($counter) ? $this->mysqlInstance->count()->get()->counter() : $this->mysqlInstance->get()->all();
    }

    public function toggleFilterFavorite($id, $status) {
        return $this->mysqlInstance->update('filter_set', array('favorites' => $status), array('id'=>$id))->total_rows();
    }

    public function deleteFilter($id) {
        $where = array('id'=>$id);
        if (!empty($this->admin_login) && $this->admin_login != 'admin') {
            $where['for_all = 1 OR admin_id'] = $this->admin_id;
        }
        return $this->mysqlInstance->delete('filter_set', $where)->total_rows();
    }

    public function getTVChannelNames($param) {
        return $this->mysqlInstance->from("itv")->like(array('name' => "%$param%"), ' OR ')->orderby('name')->get()->all('name');
    }

    public function getMovieNames($param) {
        return $this->mysqlInstance->from("video")->like(array('name' => "%$param%"), ' OR ')->orderby('name')->get()->all('name');
    }

    public function getStbFirmwareVersion($param){
        return $this->mysqlInstance->from("users")->like(array('version' => "%$param%"), ' OR ')->orderby('version')->get()->all('version');
    }

}
