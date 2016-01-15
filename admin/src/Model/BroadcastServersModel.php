<?php

namespace Model;

class BroadcastServersModel extends \Model\BaseStalkerModel {

    public function __construct() {
        parent::__construct();
    }
    
    public function getZoneTotalRows($where = array(), $like = array()) {
        $params = array(
            /*'select' => array("*"),*/
            'where' => $where,
            'like' => array(),
            'order' => array()
        );
        if (!empty($like)) {
            $params['like'] = $like;
        }
        return $this->getZoneList($params, TRUE);
    }
    
    public function getZoneList($param, $counter = FALSE) {
        if (!empty($param['select'])) {
            $this->mysqlInstance->select($param['select']);
        }
        $this->mysqlInstance->from("stream_zones as S_Z")
                        ->join("countries_in_zone as C_I_Z", "S_Z.id", "C_I_Z.zone_id", "LEFT")
                        ->where($param['where']);
        if (!empty($param['like'])) {
            $this->mysqlInstance->like($param['like'], 'OR');
        }
        $this->mysqlInstance->groupby('S_Z.id');
        if (!empty($param['order'])) {
            $this->mysqlInstance->orderby($param['order']);
        }
        if (!empty($param['limit']['limit'])) {
            $this->mysqlInstance->limit($param['limit']['limit'], $param['limit']['offset']);
        }
        
        if ($counter) {
            $result = $this->mysqlInstance->get()->all();
            return count($result);
        }
        
        return $this->mysqlInstance->get()->all();
    }
    
    public function getContryByZoneId($id) {
        return $this->mysqlInstance->from("countries_in_zone")->where(array('zone_id'=>$id))->get()->all('country_id');
    }
    
    public function updateZone($param, $id){
        return $this->mysqlInstance->update('stream_zones', $param, array('id'=>$id))->total_rows() || 1;
    }
    
    public function insertZone($param){
        return $this->mysqlInstance->insert('stream_zones', $param)->insert_id();
    }
    
    public function deleteZone($id){
        return $this->mysqlInstance->delete('stream_zones', array('id' => $id))->total_rows();
    }
    
    public function deleteCountriesInZone($zone_id){
        return $this->mysqlInstance->delete('countries_in_zone', array('zone_id' => $zone_id))->total_rows();
    }
    
    public function insertCountriesInZone($param){
        return $this->mysqlInstance->insert('countries_in_zone', $param)->insert_id();
    }
    
    public function getServersTotalRows($where = array(), $like = array()) {
        $params = array(
            /*'select' => array("*"),*/
            'where' => $where,
            'like' => array(),
            'order' => array()
        );
        if (!empty($like)) {
            $params['like'] = $like;
        }
        return $this->getServersList($params, TRUE);
    }
    
    public function getServersList($param, $counter = FALSE) {
        if (!empty($param['select'])) {
            $this->mysqlInstance->select($param['select']);
        }
        $this->mysqlInstance->from("`streaming_servers` as S_S")
                        ->join("stream_zones as S_Z", "S_S.stream_zone", "S_Z.id", "LEFT")
                        ->where($param['where']);
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
    
    public function updateServers($param, $id){
        return $this->mysqlInstance->update('streaming_servers', $param, array('id'=>$id))->total_rows();
    }
    
    public function insertServers($param){
        return $this->mysqlInstance->insert('streaming_servers', $param)->insert_id();
    }
    
    public function deleteServers($id){
        return $this->mysqlInstance->delete('streaming_servers', array('id' => $id))->total_rows();
    }
    
}