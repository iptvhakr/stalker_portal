<?php

namespace Model;

class SettingsModel extends \Model\BaseStalkerModel {
    
    public function __construct() {
        parent::__construct();
    }

    public function getCurrentTheme() {
        return $this->mysqlInstance->from('settings')->get()->first('default_template');
    }
    
    public function setCurrentTheme($theme) {
        return $this->mysqlInstance->update('settings', array('default_template' => $theme)) || 1;
    }
    
    public function getTotalRowsCommonList($where = array(), $like = array()) {
        $params = array(
            'select' => array("*"),
            'where' => $where,
            'like' => array(),
            'order' => array()
        );
        if (!empty($like)) {
            $params['like'] = $like;
        }
        return $this->getCommonList($params, TRUE);
    }
    
    public function getCommonList($param, $counter = FALSE) {
        $obj = $this->mysqlInstance->select($param['select'])
                ->from("image_update_settings")
                ->where($param['where'])->like($param['like'], 'OR')
                ->orderby($param['order']);

        if (!empty($param['limit']['limit'])) {
            $obj = $obj->limit($param['limit']['limit'], $param['limit']['offset']);
        }

        return ($counter) ? $obj->count()->get()->counter() : $obj->get()->all();
    }

    public function updateCommon($param, $where) {
        $where = (is_array($where) ? $where : array('id' => $where));
        return $this->mysqlInstance->update("image_update_settings", $param, $where)->total_rows() || 1;
    }

    public function insertCommon($param) {
        return $this->mysqlInstance->insert("image_update_settings", $param)->insert_id();
    }

    public function deleteCommon($param) {
        return $this->mysqlInstance->delete("image_update_settings", $param)->total_rows();
    }
}