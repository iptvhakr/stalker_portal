<?php

use Stalker\Lib\Core\Mysql;
use Stalker\Lib\Core\Middleware;
use Stalker\Lib\Core\Config;

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
        'header'     => '',
        'priority'  => 0,
        'msg'       => '',
        'need_confirm'    => 0,
        'reboot_after_ok' => 0,
        'eventtime' => 0,
        'auto_hide_timeout' => 0,
        'param1'    => '',
        'post_function' => ''
    );
    
    private $pattern;
    private $db;
    private $ttl;
    
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
            }else if ($list == 'online'){
                $this->param['user_list'] = Middleware::getOnlineUsersId();
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
                      ->where(array('uid' => $uid, 'ended' => 0, 'eventtime>' => date(Mysql::DATETIME_FORMAT)))
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
     * Set event header
     *
     * @param string $header
     */
    protected function setHeader($header = ''){
        $this->param['header'] = $header;
    }

    /**
     * Set event param1
     *
     * @param string $param1
     */
    protected function setParam1($param1){
        $this->param['param1'] = $param1;
    }

    /**
     * Set event post_function
     *
     * @param string $post_function
     */
    protected function setPostFunction($post_function){
        $this->param['post_function'] = $post_function;
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
     * @param int $timeout
     */
    public function setAutoHideTimeout($timeout){
        $this->param['auto_hide_timeout'] = $timeout;
    }

    /**
     * Set event time to live
     *
     * @param int $ttl
     */
    public function setTtl($ttl){
        $this->ttl = (int) $ttl;
    }
    
    /**
     * Send event
     *
     */
    protected function send(){
        if (!$this->param['eventtime']){

            if (empty($this->ttl)){
                if ($this->param['event'] == 'send_msg' || $this->param['event'] == 'send_msg_with_video'){
                    $this->ttl = 7*24*3600;
                }else{
                    $this->ttl = Config::get('watchdog_timeout')*2;
                }
            }

            $this->setEventTime(date("Y-m-d H:i:s", time() + $this->ttl));
        }
        
        if (!$this->param['priority']){
            if ($this->param['event'] == 'send_msg' || $this->param['event'] == 'send_msg_with_video'){
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
            
            $data = array();

            foreach ($this->param['user_list'] as $uid){
                
                $data[] = array(
                    'uid'               => $uid,
                    'event'             => $this->param['event'],
                    'header'             => $this->param['header'],
                    'addtime'           => 'NOW()',
                    'eventtime'         => $this->param['eventtime'],
                    'need_confirm'      => $this->param['need_confirm'],
                    'reboot_after_ok'   => $this->param['reboot_after_ok'],
                    'msg'               => $this->param['msg'],
                    'priority'          => $this->param['priority'],
                    'auto_hide_timeout' => $this->param['auto_hide_timeout'],
                    'param1'            => $this->param['param1'],
                    'post_function'     => $this->param['post_function']
                );

                if ($this->param['event'] == 'cut_off'){
                    \Stalker\Lib\OAuth\AuthAccessHandler::setInvalidAccessTokenByUid($uid);
                }
            }

            if ($this->param['event'] == 'send_msg' && $this->param['reboot_after_ok'] == 1){
                Mysql::getInstance()->query('delete from events where uid in('.implode(',', $this->param['user_list']).') and event="send_msg" and sended=0 and reboot_after_ok=1');
            }

            $this->db->insert('events', $data);
        }
    }
}

?>