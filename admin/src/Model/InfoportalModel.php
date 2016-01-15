<?php

namespace Model;

class InfoportalModel extends \Model\BaseStalkerModel {

    public function __construct() {
        parent::__construct();
    }

    public function getTotalRowsPhoneBoockList($table_prefix, $where = array(), $like = array()) {
        $params = array(
            /*'select' => array("*"),*/
            'where' => $where,
            'like' => array(),
            'order' => array()
        );
        if (!empty($like)) {
            $params['like'] = $like;
        }
        return $this->getPhoneBoockList($table_prefix, $params, TRUE);
    }

    public function getPhoneBoockList($table_prefix, $param, $counter = FALSE) {
        if (!empty($param['select'])) {
            $this->mysqlInstance->select($param['select']);
        }
        $this->mysqlInstance->from("{$table_prefix}_city_info");

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

    public function updatePhoneBoock($table_prefix, $param) {
        $where = array('id' => $param['id']);
        return $this->mysqlInstance->update("{$table_prefix}_city_info", $param[0], $where)->total_rows();
    }

    public function insertPhoneBoock($table_prefix, $param) {
        return $this->mysqlInstance->insert("{$table_prefix}_city_info", $param)->insert_id();
    }

    public function deletePhoneBoock($table_prefix, $param) {
        return $this->mysqlInstance->delete("{$table_prefix}_city_info", $param)->total_rows();
    }

    public function getTotalRowsHumorList($where = array(), $like = array()) {
        $params = array(
            /*'select' => array("*"),*/
            'where' => $where,
            'like' => array(),
            'order' => array()
        );
        if (!empty($like)) {
            $params['like'] = $like;
        }
        return $this->getHumorList($params, TRUE);
    }

    public function getHumorList($param, $counter = FALSE) {
        if (!empty($param['select'])) {
            $this->mysqlInstance->select($param['select']);
        }
        $this->mysqlInstance->from("anec");

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

    public function updateHumor($param, $where) {
        $where = (is_array($where) ? $where : array('id' => $where));
        return $this->mysqlInstance->update("anec", $param, $where)->total_rows();
    }

    public function insertHumor($param) {
        return $this->mysqlInstance->insert("anec", $param)->insert_id();
    }

    public function deleteHumor($param) {
        return $this->mysqlInstance->delete("anec", $param)->total_rows();
    }

    public function getFirstFreeNumber($table_prefix) {
        $min = (int) $this->mysqlInstance->query("SELECT min(`{$table_prefix}_city_info`.`num`) as `empty_number` FROM `{$table_prefix}_city_info`")->first('empty_number');
        if ($min > 1) {
            return 1;
        } else {
            return $this->mysqlInstance
                ->query("SELECT (`{$table_prefix}_city_info`.`num`+1) as `empty_number`
                    FROM `{$table_prefix}_city_info`
                    WHERE (
                        SELECT 1 FROM `{$table_prefix}_city_info` as `st` WHERE `st`.`num` = (`{$table_prefix}_city_info`.`num` + 1)
                    ) IS NULL
                    ORDER BY `{$table_prefix}_city_info`.`num`
                    LIMIT 1")
                ->first('empty_number');
        }
    }
}
