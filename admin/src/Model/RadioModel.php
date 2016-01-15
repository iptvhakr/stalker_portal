<?php

namespace Model;

class RadioModel extends \Model\BaseStalkerModel {

    public function __construct() {
        parent::__construct();
    }

    public function getTotalRowsRadioList($where = array(), $like = array()) {
        $this->mysqlInstance->count()->from('radio')->where($where);
        if (!empty($like)) {
            $this->mysqlInstance->like($like, 'OR');
        }
        return $this->mysqlInstance->get()->counter();
    }
   
    public function getRadioList($param) {
        if (!empty($param['select'])) {
            $this->mysqlInstance->select($param['select']);
        }
        $this->mysqlInstance->from('radio')//->join('users', 'user_log.mac', 'users.mac', 'LEFT')
                        ->where($param['where'])->like($param['like'], 'OR')->orderby($param['order']);
        if (!empty($param['limit']['limit'])) {
            $this->mysqlInstance->limit($param['limit']['limit'], $param['limit']['offset']);
        }

        return $this->mysqlInstance->get()->all();
    }
    
    public function toggleRadioStatus($id, $status) {
        return $this->mysqlInstance->update('radio', array('status' => $status), array('id' => $id))->total_rows();
    }
    
    public function deleteRadioById($id) {
        return $this->mysqlInstance->delete('radio', array('id' => $id))->total_rows();
    }
    
    public function searchOneRadioParam($param = array()){
        reset($param);
        list($key, $row) = each($param);
        return $this->mysqlInstance->from('radio')->where($param)->get()->first($key);
    }
    
    public function updateRadio($param, $id){
        return $this->mysqlInstance->update('radio', $param, array('id'=>$id))->total_rows();
    }
    
    public function insertRadio($param){
        return $this->mysqlInstance->insert('radio', $param)->insert_id();
    }
}
