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

    public function getTotalRowsMsgTemplates($where = array(), $like = array()) {
        $params = array(
            'where' => $where
        );
        if (!empty($like)) {
            $params['like'] = $like;
        }
        return $this->getMsgTemplates($params, TRUE);
    }

    public function getMsgTemplates($param, $counter = FALSE) {

        if (!empty($param['select'])) {
            $this->mysqlInstance->select($param['select']);
        }
        $this->mysqlInstance->from("messages_templates as M_T")
            ->join("administrators as A", "M_T.author", "A.id", "LEFT");
        if (!empty($param['where'])) {
            $this->mysqlInstance->where($param['where']);
        }
        if (!empty($where)) {
            $this->mysqlInstance->where($where, ' OR ');
        }
        if (!empty($param['like'])) {
            $this->mysqlInstance->like($param['like'], ' OR ');
        }
        if (!empty($param['order'])) {
            $this->mysqlInstance->orderby($param['order']);
        }
        if (!empty($param['limit']['limit'])) {
            $this->mysqlInstance->limit($param['limit']['limit'], $param['limit']['offset']);
        }

        return ($counter) ? $this->mysqlInstance->count()->get()->counter() : $this->mysqlInstance->get()->all();
    }

    public function insertMsgTemplate($params){
        return $this->mysqlInstance->insert('messages_templates', $params)->insert_id();
    }

    public function updateMsgTemplate($params, $id){
        $where = array('id'=>$id);
        if (!empty($this->admin_login) && $this->admin_login != 'admin') {
            $where['author'] = $this->admin_id;
        }
        return $this->mysqlInstance->update('messages_templates', $params, $where)->total_rows();
    }

    public function deleteMsgTemplate($id) {
        $where = array('id'=>$id);
        if (!empty($this->admin_login) && $this->admin_login != 'admin') {
            $where['author'] = $this->admin_id;
        }
        return $this->mysqlInstance->delete('messages_templates', $where)->total_rows();
    }

    public function getFilterSet($params){
        return $this->mysqlInstance->from('filter_set')->where($params)->get()->all();
    }

    public function getTotalRowsScheduleEvents($where = array(), $like = array()) {
        $params = array(
            'where' => $where
        );
        if (!empty($like)) {
            $params['like'] = $like;
        }
        return $this->getScheduleEvents($params, TRUE);
    }

    public function getScheduleEvents($param, $counter = FALSE) {

        if (!empty($param['select'])) {
            $this->mysqlInstance->select($param['select']);
        }
        $this->mysqlInstance->from("schedule_events as S_E");
        if (!empty($param['where'])) {
            $this->mysqlInstance->where($param['where']);
        }
        if (!empty($where)) {
            $this->mysqlInstance->where($where, ' OR ');
        }
        if (!empty($param['like'])) {
            $this->mysqlInstance->like($param['like'], ' OR ');
        }
        if (!empty($param['order'])) {
            $this->mysqlInstance->orderby($param['order']);
        }
        if (!empty($param['limit']['limit'])) {
            $this->mysqlInstance->limit($param['limit']['limit'], $param['limit']['offset']);
        }

        return ($counter) ? $this->mysqlInstance->count()->get()->counter() : $this->mysqlInstance->get()->all();
    }

    public function insertScheduleEvents($params){
        return $this->mysqlInstance->insert('schedule_events', $params)->insert_id();
    }

    public function updateScheduleEvents($params, $id){
        $where = array('id'=>$id);
        return $this->mysqlInstance->update('schedule_events', $params, $where)->total_rows();
    }

    public function deleteScheduleEvents($id) {
        $where = array('id'=>$id);
        return $this->mysqlInstance->delete('schedule_events', $where)->total_rows();
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
