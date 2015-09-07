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

    public function getApplicationByURL($url){
        return $this->mysqlInstance->from('apps')->where(array('url' => $url))->get()->all();
    }

    public function insertApplication($data){
        return $this->mysqlInstance->insert('apps', $data)->total_rows();
    }

    public function getTotalRowsEventsList($where = array(), $like = array()) {
        if (!empty($this->reseller_id)) {
            $where['reseller_id'] = $this->reseller_id;
        }
        $this->mysqlInstance->count()->from('events');
        if (!empty($where) || !empty($like)) {
            $this->mysqlInstance->join('users', 'events.uid', 'users.id', 'LEFT');
        }
        $this->mysqlInstance->where($where);
        if (!empty($like)) {
            $this->mysqlInstance->like($like, 'OR');
        }

        return $this->mysqlInstance->get()->counter();
    }

    public function getEventsList($param) {
        if (!empty($this->reseller_id)) {
            $param['where']['reseller_id'] = $this->reseller_id;
        }
        if (!empty($param['select'])) {
            $this->mysqlInstance->select($param['select']);
        }
        $this->mysqlInstance->from('events')->join('users', 'events.uid', 'users.id', 'LEFT')
            ->where($param['where'])->like($param['like'], 'OR')->orderby($param['order']);
        if (!empty($param['limit']['limit'])) {
            $this->mysqlInstance->limit($param['limit']['limit'], (array_key_exists('offset', $param['limit'])? $param['limit']['offset']: NULL));
        }

        return $this->mysqlInstance->get()->all();
    }

}