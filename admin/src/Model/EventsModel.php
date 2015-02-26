<?php

namespace Model;

class EventsModel extends \Model\BaseStalkerModel {

    public function __construct() {
        parent::__construct();
    }

    public function getTotalRowsEventsList($where = array(), $like = array()) {
        $obj = $this->mysqlInstance->count()->from('events');
        if (!empty($where) || !empty($like)) {
            $obj = $obj->join('users', 'events.uid', 'users.id', 'LEFT');    
        }
        $obj = $obj->where($where);
        if (!empty($like)) {
            $obj = $obj->like($like, 'OR');
        }
        return $obj->get()->counter();
    }
   
    public function getEventsList($param) {
        $obj = $this->mysqlInstance->select($param['select'])
                        ->from('events')->join('users', 'events.uid', 'users.id', 'LEFT')
                        ->where($param['where'])->like($param['like'], 'OR')->orderby($param['order']);
        if (!empty($param['limit']['limit'])) {
            $obj = $obj->limit($param['limit']['limit'], (array_key_exists('offset', $param['limit'])? $param['limit']['offset']: NULL));
        }

        return $obj->get()->all();
    }
    
    public function getUser($param) {
        return $this->mysqlInstance->from('users')->where($param, 'OR')->get()->first();
    }
    
    public function getConsoleGroup($param = array()){
        return $this->mysqlInstance->from('stb_groups')->where($param)->get()->all();
    }
    
    public function getConsoleInGroup($param = array()){
        return $this->mysqlInstance->from('stb_in_group')->where($param)->get()->all();
    }
    
    public function updateUser($params, $where) {
        return $this->mysqlInstance->where($where)->update('users', $params)->total_rows();
    }
    
    public function deleteEventsByUID($uid) {
        return $this->mysqlInstance->delete('events', array('uid' => $uid))->total_rows();
    }
//    
//    public function searchOneEventsParam($param = array()){
//        reset($param);
//        list($key, $row) = each($param);
//        return $this->mysqlInstance->from('events')->where($param)->get()->first($key);
//    }
//    
//    public function updateEvents($param, $id){
//        return $this->mysqlInstance->update('events', $param, array('id'=>$id))->total_rows();
//    }
//    public function insertEvents($param){
//        return $this->mysqlInstance->insert('events', $param)->insert_id();
//    }
}
