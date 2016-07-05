<?php

namespace Model;

class ApplicationCatalogModel extends \Model\BaseStalkerModel {

    public function __construct() {
        parent::__construct();
    }

    public function getTOS(){
        return $this->mysqlInstance->from('apps_tos')->get()->all();
    }

    public function setAcceptedTOS(){
        return $this->mysqlInstance->update('apps_tos', array('accepted'=>1));
    }

    public function getApplication($where){
        return $this->mysqlInstance->from('apps')->where($where)->get()->all();
    }

    public function insertApplication($data){
        $data['added'] = 'NOW()';
        return $this->mysqlInstance->insert('apps', $data)->total_rows();
    }

    public function updateApplication($data, $where){
        if (!is_array($where) && is_numeric($where)) {
            $where = array('id' => $where);
        }
        $data['updated'] = 'NOW()';
        return $this->mysqlInstance->update('apps', $data, $where)->total_rows();
    }

    public function deleteApplication($where){
        if (!is_array($where) && is_numeric($where)) {
            $where = array('id' => $where);
        }
        $data['updated'] = 'NOW()';
        return $this->mysqlInstance->delete('apps', $where)->total_rows();
    }

    public function getTotalRowsSmartApplicationList($where = array(), $like = array()) {
        $params = array(
            /*'select' => array("*"),*/
            'where' => $where,
            'like' => array(),
            'order' => array()
        );
        if (!empty($like)) {
            $params['like'] = $like;
        }

        return $this->getSmartApplicationList($params, TRUE);
    }

    public function getSmartApplicationList($param, $counter = FALSE, $get_object = FALSE) {
        if (!empty($param['select'])) {
            $this->mysqlInstance->select($param['select']);
        }
        $this->mysqlInstance->from('launcher_apps AS L_A ')->where($param['where']);
        if (!empty($param['like'])) {
            $this->mysqlInstance->like($param['like'], 'OR');
        }
        if (!empty($param['order'])) {
            $this->mysqlInstance->orderby($param['order']);
        }

        if (!empty($param['limit']['limit']) && !$get_object) {
            $this->mysqlInstance->limit($param['limit']['limit'], $param['limit']['offset']);
        }

        return ($counter) ? $this->mysqlInstance->count()->get()->counter() : ($get_object ? $this->mysqlInstance->get(): $this->mysqlInstance->get()->all());
    }

    public function getSmartApplication($where){
        return $this->mysqlInstance->from('launcher_apps')->where($where)->get()->all();
    }

    public function insertSmartApplication($data){
        $data['added'] = 'NOW()';
        return $this->mysqlInstance->insert('launcher_apps', $data)->total_rows();
    }

    public function updateSmartApplication($data, $where){
        if (!is_array($where) && is_numeric($where)) {
            $where = array('id' => $where);
        }
        $data['updated'] = 'NOW()';
        return $this->mysqlInstance->update('launcher_apps', $data, $where)->total_rows();
    }

    public function deleteSmartApplication($where){
        if (!is_array($where) && is_numeric($where)) {
            $where = array('id' => $where);
        }
        $data['updated'] = 'NOW()';
        return $this->mysqlInstance->delete('launcher_apps', $where)->total_rows();
    }
}