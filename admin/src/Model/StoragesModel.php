<?php

namespace Model;

class StoragesModel extends \Model\BaseStalkerModel {

    public function __construct() {
        parent::__construct();
    }
    
    public function getLogsTotalRows($where = array(), $like = array()) {
        $params = array(
            /*'select' => array("*"),*/
            'where' => $where,
            'like' => array(),
            'order' => array()
        );
        if (!empty($like)) {
            $params['like'] = $like;
        }
        return $this->getLogsList($params, TRUE);
    }
    
    public function getLogsList($param, $counter = FALSE) {
        if (!empty($param['select'])) {
            $this->mysqlInstance->select($param['select']);
        }
        $this->mysqlInstance->from("master_log as M_L")->where($param['where']);
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
    
    public function getListTotalRows($where = array(), $like = array()) {
        $params = array(
            /*'select' => array("*"),*/
            'where' => $where,
            'like' => array(),
            'order' => array()
        );
        if (!empty($like)) {
            $params['like'] = $like;
        }
        return $this->getListList($params, TRUE);
    }
    
    public function getListList($param, $counter = FALSE) {
        if (!empty($param['select'])) {
            $this->mysqlInstance->select($param['select']);
        }
        $this->mysqlInstance->from("storages as S")->where($param['where']);
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
    
    public function updateStorageCache($param, $id = array()){
        return $this->mysqlInstance->update('storage_cache', $param, $id)->total_rows() || 1;
    }
    
    public function getNoCustomVideo($id) {
        return $this->mysqlInstance->from("video")->where(array('protocol!=' => 'custom'))->get()->all('id');
    }
    
    public function getNoCustomKaraoke($id) {
        return $this->mysqlInstance->from("karaoke")->where(array('protocol!=' => 'custom'))->get()->all('id');
    }
    
    public function updateStrages($param, $id){
        return $this->mysqlInstance->update('storages', $param, array('id'=>$id))->total_rows() || 1;
    }
    
    public function insertStrages($param){
        return $this->mysqlInstance->insert('storages', $param)->insert_id();
    }
    
    public function deleteStrages($id){
        return $this->mysqlInstance->delete('storages', array('id' => $id))->total_rows();
    }
    
    public function getTotalRowsVideoList($select = array(), $where = array(), $like = array(), $having = array()) {
        $params = array(
            /*'select' => $select,*/
            'where' => $where,
            'like' => array(),
            'order' => array()
        );
        if (!empty($like)) {
            $params['like'] = $like;
        }
        if (!empty($having)) {
            $params['having'] = $having;
        }
        return $this->getVideoList($params, TRUE);
    }
   
    public function getVideoList($param, $counter = FALSE) {
        if (!empty($param['select'])) {
            $this->mysqlInstance->select($param['select']);
        }
        $this->mysqlInstance->from('video, storage_cache')
                    ->where($param['where'])
                    ->where(array('media_type'=>'vclub','video.id=storage_cache.media_id  and '=>'1=1','storage_cache.status'=>'1'))
                ->like($param['like'], 'OR');
        if (!empty($param['order'])) {
            $this->mysqlInstance->orderby($param['order']);
        }
        $this->mysqlInstance->groupby('media_id');
        
        if (!empty($param['having'])) {
            $this->mysqlInstance->having($param['having']);
        }
        
        if (!empty($param['limit']['limit'])) {
            $this->mysqlInstance->limit($param['limit']['limit'], $param['limit']['offset']);
        }
        if ($counter) {
            $result = $this->mysqlInstance->get()->all();
            return count($result);
        } 
//        print_r($this->mysqlInstance->get());
//        exit;
        return $this->mysqlInstance->get()->all();
    }
    
    //------------------------------------------------
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
        return $this->mysqlInstance->update('streaming_servers', $param, array('id'=>$id))->total_rows() || 1;
    }
    
    public function insertServers($param){
        return $this->mysqlInstance->insert('streaming_servers', $param)->insert_id();
    }
    
    public function deleteServers($id){
        return $this->mysqlInstance->delete('streaming_servers', array('id' => $id))->total_rows();
    }
    
}