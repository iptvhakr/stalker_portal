<?php

namespace Model;

class AdminsModel extends \Model\BaseStalkerModel {

    public function __construct() {
        parent::__construct();
    }
    
    public function getAdminsTotalRows($where = array(), $like = array()) {
        $params = array(
            'select' => array("*"),
            'where' => $where,
            'like' => array(),
            'order' => array()
        );
        if (!empty($like)) {
            $params['like'] = $like;
        }
        return $this->getAdminsList($params, TRUE);
    }
    
    public function getAdminsList($param, $counter = FALSE) {
        $where = array();
        if (!empty($this->reseller_id)) {
            $where['A.reseller_id'] = $this->reseller_id;
        }
        $obj = $this->mysqlInstance->select($param['select'])
                        ->from("administrators as A")
                        ->join("admin_groups as A_G", "A.gid", "A_G.id", "LEFT")
                        ->join("reseller as R", "A.reseller_id", "R.id", "LEFT")
                        ->where($param['where'])
                        ->where($where)
                        ->like($param['like'], 'OR')
                        ->orderby($param['order']);

        if (!empty($param['limit']['limit'])) {
            $obj = $obj->limit($param['limit']['limit'], $param['limit']['offset']);
        }
        
        return ($counter) ? $obj->count()->get()->counter() : $obj->get()->all();
    }
    
    public function insertAdmin($param){
        if (!empty($this->reseller_id)) {
            $param[0]['reseller_id'] = $this->reseller_id;
        }
        return $this->mysqlInstance->insert('administrators', $param)->insert_id();
    }
    
    public function updateAdmin($param){
        return $this->mysqlInstance->update('administrators', $param[0], array('id' => $param['id']))->total_rows();
    }
    
    public function deleteAdmin($param){
        return $this->mysqlInstance->delete('administrators', $param)->total_rows();
    }
    
    public function getAdminGropsTotalRows($where = array(), $like = array()) {
        $params = array(
            'select' => array("*"),
            'where' => $where,
            'like' => array(),
            'order' => array()
        );
        if (!empty($like)) {
            $params['like'] = $like;
        }
        return $this->getAdminGropsList($params, TRUE);
    }
    
    public function getAdminGropsList($param, $counter = FALSE) {
        $where = array();
        if (!empty($this->reseller_id)) {
            $where['A_G.reseller_id'] = $this->reseller_id;
        }
        $obj = $this->mysqlInstance->select($param['select'])
                        ->from("admin_groups as A_G")
                        ->join("reseller as R", "A_G.reseller_id", "R.id", 'LEFT');
        if (!empty($param['where'])) {
            $obj = $obj->where($param['where']);
        }

        $obj = $obj->where($where);

        if (!empty($param['like'])) {
            $obj = $obj->like($param['like'], 'OR');
        }
        if (!empty($param['order'])) {
            $obj = $obj->orderby($param['order']);
        }

        if (!empty($param['limit']['limit'])) {
            $obj = $obj->limit($param['limit']['limit'], $param['limit']['offset']);
        }

        return ($counter) ? $obj->count()->get()->counter() : $obj->get()->all();
    }
    
    public function insertAdminsGroup($param){
        if (!empty($this->reseller_id)) {
            $param[0]['reseller_id'] = $this->reseller_id;
        }
        return $this->mysqlInstance->insert('admin_groups', $param)->insert_id();
    }
    
    public function updateAdminsGroup($param){
        return $this->mysqlInstance->update('admin_groups', $param[0], array('id' => $param['id']))->total_rows();
    }
    
    public function deleteAdminsGroup($param){
        return $this->mysqlInstance->delete('admin_groups', $param)->total_rows();
    }
    
    public function getAdminGroupPermissions($gid = FALSE) {
        $obj = $this->mysqlInstance->from("adm_grp_action_access")->where(array('hidden<>'=>1));
        if ($gid !== FALSE) {
            $obj = $obj->where(array('group_id'=>$gid));
        } else {
            $obj = $obj->groupby('concat(`controller_name`, `action_name`)');
        }
        return $obj->orderby(array('concat(`controller_name`, `action_name`)'=>'asc'))->get()->all();
    }
    
    public function setAdminGroupPermissions($param) {
        return $this->mysqlInstance->insert("adm_grp_action_access", $param)->insert_id();
    }
    
    public function deleteAdminGroupPermissions($gid) {
        return $this->mysqlInstance->delete('adm_grp_action_access', array('group_id'=>$gid))->total_rows();
    }

    public function getResellersTotalRows($where = array(), $like = array()) {
        $params = array(
            'select' => array("*"),
            'where' => $where,
            'like' => array(),
            'order' => array()
        );
        if (!empty($like)) {
            $params['like'] = $like;
        }
        return $this->getResellersList($params, TRUE);
    }

    public function getResellersList($param, $counter = FALSE) {
        $obj = $this->mysqlInstance->select($param['select'])
            ->from("reseller as R")
            ->where($param['where'])
            ->like($param['like'], 'OR')
            ->orderby($param['order']);

        if (!empty($param['limit']['limit'])) {
            $obj = $obj->limit($param['limit']['limit'], $param['limit']['offset']);
        }

        return ($counter) ? $obj->count()->get()->counter() : $obj->get()->all();
    }


    public function insertReseller($param){
        $param[0]['created'] = 'NOW()';
        return $this->mysqlInstance->insert('reseller', $param)->insert_id();
    }

    public function updateReseller($param){
        return $this->mysqlInstance->update('reseller', $param[0], array('id' => $param['id']))->total_rows();
    }

    public function deleteReseller($param){
        return $this->mysqlInstance->delete('reseller', $param)->total_rows();
    }

    public function getResellerMember($table_name, $reseller_id){
        return $this->mysqlInstance->from($table_name)->where(array('reseller_id'=>$reseller_id))->count()->get()->counter();
    }

    public function updateResellerMember($table_name, $source_id, $target_id){
        return $this->mysqlInstance->update($table_name, array("reseller_id" => $target_id), array("reseller_id" => $source_id))->total_rows();
    }

    public function updateResellerMemberByID($table_name, $id, $target_id){
        return $this->mysqlInstance->update($table_name, array("reseller_id" => $target_id), array("id" => $id))->total_rows();
    }
}