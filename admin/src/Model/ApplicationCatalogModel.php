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
}