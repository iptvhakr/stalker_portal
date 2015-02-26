<?php

namespace Model;

class IndexModel extends \Model\BaseStalkerModel {

    public function __construct() {
        parent::__construct();
    }
    
    public function deleteDropdownAttribute($param) {
        return $this->mysqlInstance->delete('admin_dropdown_attributes', $param)->total_rows();
    }
    
    public function insertDropdownAttribute($param){
        return $this->mysqlInstance->insert('admin_dropdown_attributes', $param)->insert_id();
    }
}
