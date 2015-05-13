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
        $obj = $this->mysqlInstance->select($param['select'])
                        ->from("administrators as A")
                        ->join("admin_groups as A_G", "A.gid", "A_G.id", "LEFT")
                        ->where($param['where'])
                        ->like($param['like'], 'OR')
                        ->orderby($param['order']);

        if (!empty($param['limit']['limit'])) {
            $obj = $obj->limit($param['limit']['limit'], $param['limit']['offset']);
        }
        
        return ($counter) ? $obj->count()->get()->counter() : $obj->get()->all();
    }
    
    public function insertAdmin($param){
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
        $obj = $this->mysqlInstance->select($param['select'])
                        ->from("admin_groups as A_G")
                        ->where($param['where'])
                        ->like($param['like'], 'OR')
                        ->orderby($param['order']);

        if (!empty($param['limit']['limit'])) {
            $obj = $obj->limit($param['limit']['limit'], $param['limit']['offset']);
        }

        return ($counter) ? $obj->count()->get()->counter() : $obj->get()->all();
    }
    
    public function insertAdminsGroup($param){
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
        return $obj = $this->mysqlInstance->insert("adm_grp_action_access", $param)->insert_id();
    }
    
    public function deleteAdminGroupPermissions($gid) {
        return $this->mysqlInstance->delete('adm_grp_action_access', array('group_id'=>$gid))->total_rows();
    }
}