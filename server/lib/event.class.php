<?php
/**
 * Events sender from server to client
 *
 * @package stalker_portal
 * @author zhurbitsky@gmail.com
 */

class Event extends HTTPPush
{
    private $param = array(
        'user_list' => array(),
        'event'     => '',
        'priority'  => 0,
        'msg'       => '',
        'need_confirm'    => 0,
        'reboot_after_ok' => 0,
        'eventtime' => 0,
        'auto_hide_timeout' => 0
    );
    
    private $pattern;
    private $db;
    
    public function __construct(){
        $this->db = Mysql::getInstance();
        $this->pattern = $this->param;
    }
    
    /**
     * Set user list by mac
     *
     * @param mixed $list
     */
    public function setUserListByMac($list){
        if (is_string($list) || is_int($list)){
            if ($list == 'all'){
                $this->param['user_list'] = Middleware::getAllUsersId();
            }else{
                $this->param['user_list'] = array(Middleware::getUidByMac($list));
            }
        }else{
            $this->param['user_list'] = array();
            foreach ($list as $mac){
                $this->param['user_list'][] = Middleware::getUidByMac($mac);
            }
        }
    }
    
    /**
     * Set user list by id
     *
     * @param mixed $list
     */
    public function setUserListById($list){
        if (is_string($list) || is_int($list)){
            if ($list == 'all'){
                $this->param['user_list'] = Middleware::getAllUsersId();
            }else{
                $this->param['user_list'] = array($list);
            }
        }else{
            $this->param['user_list'] = $list;
        }
    }
    
    /**
     * Set sended status for event
     *
     * @param int $id event id
     */
    public static function setSended($id){
        $db = Mysql::getInstance();
        //$db->executeQuery('update events set sended=1 where id='.$id);
        $db->update('events', array('sended' => 1), array('id' => $id));
    }
    
    /**
     * Set confirmed event
     *
     * @param int $id event id
     */
    public static function setConfirmed($id){
        $db = Mysql::getInstance();
        //$db->executeQuery('update events set confirmed=1,ended=1 where id='.$id);
        $db->update('events', array('confirmed' => 1, 'ended' => 1), array('id' => $id));
    }
    
    /**
     * Set ended event
     *
     * @param int $id event id
     */
    public static function setEnded($id){
        $db = Mysql::getInstance();
        //$db->executeQuery('update events set ended=1 where id='.$id);
        $db->update('events', array('ended' => 1), array('id' => $id));
    }
    
    /**
     * Return events for stb by priority
     *
     * @param int $uid
     * @return array|false events for stb by priority or error
     */
    public static function getAllNotEndedEvents($uid){
        if ($uid){
            $db = Mysql::getInstance();
            //return $db->executeQuery('select * from events where uid='.$uid.' and ended=0 and eventtime>NOW() order by priority, addtime')->getAllValues();
            return $db->from('events')
                      ->where(array('uid' => $uid, 'ended' => 0, 'eventtime>' => 'NOW()'))
                      ->orderby('priority')
                      ->orderby('addtime')
                      ->get()
                      ->all();
        }
        return false;
    }
    
    /**
     * Set event name
     *
     * @param string $event
     */
    protected function setEvent($event){
        $this->param['event'] = $event;
    }
    
    /**
     * Set event priority
     *
     * @param int $priority
     */
    protected function setPriority($priority){
        $this->param['priority'] = $priority;
    }
    
    /**
     * Set event message
     *
     * @param string $msg
     */
    protected function setMsg($msg){
        $this->param['msg'] = $msg;
    }
    
    /**
     * Set event life time
     *
     * @param string $eventtime must be valid mysql datetime "Y-m-d H:i:s"
     */
    protected function setEventTime($eventtime){
        $this->param['eventtime'] = $eventtime;
    }
    
    /**
     * Set need confirm option
     *
     * @param int $need_confirm
     */
    protected function setNeedConfirm($need_confirm){
        $this->param['need_confirm'] = $need_confirm;
    }
    
    /**
     * Set reboot after ok option
     *
     * @param int $reboot_after_ok
     */
    protected function setRebootAfterOk($reboot_after_ok){
        $this->param['reboot_after_ok'] = $reboot_after_ok;
    }

    /**
     * Set auto hide timeout option. In seconds.
     *
     * @param int $reboot_after_ok
     */
    public function setAutoHideTimeout($timeout){
        $this->param['auto_hide_timeout'] = $timeout;
    }
    
    /**
     * Send event
     *
     */
    protected function send(){
        if (!$this->param['eventtime']){
            if ($this->param['event'] == 'send_msg'){
                $correction = 7*24*3600;
            }else{
                $correction = Config::get('watchdog_timeout')*2;
            }
            $this->setEventTime(date("Y-m-d H:i:s", time() + $correction));
        }
        
        if (!$this->param['priority']){
            if ($this->param['event'] == 'send_msg'){
                $this->setPriority(2);
            }else{
                $this->setPriority(1);
            }
        }
        
        $this->saveInDb();
        $this->push();
        $this->resetEventOptions();
    }
    
    /**
     * Reset all event options
     *
     */
    protected function resetEventOptions(){
        $this->param = $this->pattern;
    }
    
    /**
     * Save event in database
     *
     */
    protected function saveInDb(){
        
        if (is_array($this->param['user_list']) && count($this->param['user_list']) > 0){
        /*$sql = 'insert into events (
                                      uid,
                                      event,
                                      addtime,
                                      eventtime,
                                      need_confirm,
                                      reboot_after_ok,
                                      msg,
                                      priority
                                     )
                              VALUES ';
        
            foreach ($this->param['user_list'] as $uid){
                $sql .= '('.$uid.', "'.$this->param['event'].'", NOW(), "'.$this->param['eventtime'].'", '.$this->param['need_confirm'].', '.$this->param['reboot_after_ok'].', "'.mysql_real_escape_string($this->param['msg']).'", '.$this->param['priority'].'),';
            }
            $sql = substr($sql, 0, strlen($sql)-1);
            $this->db->executeQuery($sql);*/
            
            $data = array();
            
            foreach ($this->param['user_list'] as $uid){
                
                $data[] = array(
                    'uid'               => $uid,
                    'event'             => $this->param['event'],
                    'addtime'           => 'NOW()',
                    'eventtime'         => $this->param['eventtime'],
                    'need_confirm'      => $this->param['need_confirm'],
                    'reboot_after_ok'   => $this->param['reboot_after_ok'],
                    'msg'               => $this->param['msg'],
                    'priority'          => $this->param['priority'],
                    'auto_hide_timeout' => $this->param['auto_hide_timeout']
                );
                
            }
            
            $this->db->insert('events', $data);
            
            
            /*if ($this->db->getLastError()){
                echo $this->db->getLastError();
            }*/
        }
    }
}

?>