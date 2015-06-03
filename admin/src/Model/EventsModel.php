<?php

namespace Model;

class EventsModel extends \Model\BaseStalkerModel {

    public function __construct() {
        parent::__construct();
    }

    public function getTotalRowsEventsList($where = array(), $like = array()) {
        if (!empty($this->reseller_id)) {
            $where['reseller_id'] = $this->reseller_id;
        }
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
        if (!empty($this->reseller_id)) {
            $param['where']['reseller_id'] = $this->reseller_id;
        }
        $obj = $this->mysqlInstance->select($param['select'])
                        ->from('events')->join('users', 'events.uid', 'users.id', 'LEFT')
                        ->where($param['where'])->like($param['like'], 'OR')->orderby($param['order']);
        if (!empty($param['limit']['limit'])) {
            $obj = $obj->limit($param['limit']['limit'], (array_key_exists('offset', $param['limit'])? $param['limit']['offset']: NULL));
        }

        return $obj->get()->all();
    }
    
    public function getUser($param = array(), $all = FALSE) {
        $where = array();
        if (!empty($this->reseller_id)) {
            $where['reseller_id'] = $this->reseller_id;
        }

        if ($all !== FALSE) {
            return $this->mysqlInstance->from('users')->where($param, 'OR')->where($where)->get()->all();
        }

        return $this->mysqlInstance->from('users')->where($param, 'OR')->where($where)->get()->first();
    }
    
    public function getConsoleGroup($param = array()){
        if (!empty($this->reseller_id)) {
            $param['reseller_id'] = $this->reseller_id;
        }
        return $this->mysqlInstance->from('stb_groups')->where($param)->get()->all();
    }
    
    public function getConsoleInGroup($param = array()){
        return $this->mysqlInstance->from('stb_in_group')->where($param)->get()->all();
    }
    
    public function updateUser($params, $where) {
        if (!empty($this->reseller_id)) {
            $where['reseller_id'] = $this->reseller_id;
        }
        return $this->mysqlInstance->where($where)->update('users', $params)->total_rows();
    }
    
    public function deleteEventsByUID($uid) {
        if (empty($this->reseller_id)) {
            return $this->mysqlInstance->delete('events', array('uid' => $uid))->total_rows();
        } else {
            $users = array();
            foreach($this->getUser(array('id' => $uid), 'ALL') as $row) {
                $users[] = $row['id'];
            }

            return $this->mysqlInstance->in('uid', $users)->delete('events', array())->total_rows();
        }
    }

    public function deleteAllEvents() {
        if (empty($this->reseller_id)) {
            return $this->mysqlInstance->query('TRUNCATE TABLE `events`') ? 'all': 0;
        } else {
            $users = array();
            foreach($this->getUser(array(), 'ALL') as $row) {
                $users[] = $row['id'];
            }

            return $this->mysqlInstance->in('uid', $users)->delete('events', array())->total_rows();
        }
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
