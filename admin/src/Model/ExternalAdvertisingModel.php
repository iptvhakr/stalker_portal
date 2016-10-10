<?php

namespace Model;

class ExternalAdvertisingModel extends \Model\BaseStalkerModel {

    public function __construct() {
        parent::__construct();
    }

    public function getRegisterRowsList($incoming = array(), $all = FALSE) {
        if ($all) {
            $incoming['like'] = array();
        }
        return $this->getRegisterList($incoming, TRUE);
    }

    public function getRegisterList($param, $counter = FALSE) {
        if (!empty($param['select'])) {
            $this->mysqlInstance->select($param['select']);
        }

        $this->mysqlInstance->from('ext_adv_register as E_A_R');

        if (array_key_exists('joined', $param)) {
            foreach ($param['joined'] as $table => $keys) {
                $this->mysqlInstance->join($table, $keys['left_key'], $keys['right_key'], $keys['type']);
            }
        }

        if (!empty($this->reseller_id)) {
            $this->mysqlInstance->where(array('reseller_id' => $this->reseller_id));
        }

        if (empty($param['joined']) || !array_key_exists('administrators as A', $param['joined'])) {
            $this->mysqlInstance->join('administrators as A', 'E_A_R.admin_id', 'A.id', 'LEFT');
        }
        if (!empty($param['where'])) {
            $this->mysqlInstance->where($param['where']);
        }
        if (!empty($param['like'])) {
            $this->mysqlInstance->like($param['like'], 'OR');
        }
        if (!empty($param['order'])) {
            $this->mysqlInstance->orderby($param['order']);
        }

        if (!empty($param['groupby'])) {
            $this->mysqlInstance->groupby($param['groupby']);
        }

        if ($counter) {
            $result = $this->mysqlInstance->count()->get()->first();
            return array_sum($result);
        }
        if (!empty($param['limit']['limit']) && !$counter) {
            $this->mysqlInstance->limit($param['limit']['limit'], $param['limit']['offset']);
        }

        return $this->mysqlInstance->get()->all();
    }

    public function insertRegisterData($params){
        return $this->mysqlInstance->insert('ext_adv_register', $params)->insert_id();
    }

    public function updateRegisterData($params, $id){
        $where = array('id'=>$id);
        return $this->mysqlInstance->update('ext_adv_register', $params, $where)->total_rows();
    }

    public function getSourceRowsList($incoming = array(), $all = FALSE) {
        if ($all) {
            $incoming['like'] = array();
        }
        return $this->getSourceList($incoming, TRUE);
    }

    public function getSourceList($param, $counter = FALSE) {
        if (!empty($param['select'])) {
            $this->mysqlInstance->select($param['select']);
        }

        $this->mysqlInstance->from('ext_adv_sources as E_A_S');

        if (array_key_exists('joined', $param)) {
            foreach ($param['joined'] as $table => $keys) {
                $this->mysqlInstance->join($table, $keys['left_key'], $keys['right_key'], $keys['type']);
            }
        }
        if (!empty($this->reseller_id)) {
            $this->mysqlInstance->where(array('reseller_id' => $this->reseller_id));
        }

        if (!empty($param['where'])) {
            $this->mysqlInstance->where($param['where']);
        }
        if (!empty($param['like'])) {
            $this->mysqlInstance->like($param['like'], 'OR');
        }
        if (!empty($param['order'])) {
            $this->mysqlInstance->orderby($param['order']);
        }

        if (!empty($param['groupby'])) {
            $this->mysqlInstance->groupby($param['groupby']);
        }

        if ($counter) {
            $result = $this->mysqlInstance->count()->get()->first();
            return array_sum($result);
        }
        if (!empty($param['limit']['limit']) && !$counter) {
            $this->mysqlInstance->limit($param['limit']['limit'], $param['limit']['offset']);
        }

        return $this->mysqlInstance->get()->all();
    }

    public function insertSourceData($params){
        return $this->mysqlInstance->insert('ext_adv_sources', $params)->insert_id();
    }

    public function updateSourceData($params, $id){
        $where = array('id'=>$id);
        return $this->mysqlInstance->update('ext_adv_sources', $params, $where)->total_rows();
    }
}