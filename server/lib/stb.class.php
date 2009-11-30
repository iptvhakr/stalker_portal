<?php
/**
 * Main STB class.
 * 
 * @package stalker_portal
 * @author zhurbitsky@gmail.com
 */

class Stb
{
    public $id  = 0;
    public $mac = '';
    public $ip;
    public $hd  = 0;
    private $params = array();
    private $db;
    
    private static $instance = NULL;
    
    public static function getInstance(){
        if (self::$instance == NULL)
        {
            self::$instance = new Stb();
        }
        return self::$instance;
    }
    
    private function __construct(){
        
        $this->mac = @trim(urldecode($_COOKIE['mac']));
        
        if (@$_SERVER['HTTP_X_REAL_IP']){
            $this->ip = $_SERVER['HTTP_X_REAL_IP'];
        }else{
            $this->ip = $_SERVER['REMOTE_ADDR'];
        }
        
        $this->db = Mysql::getInstance();
        $this->getStbParams();
    }
    
    public function setId($id){
        $this->id = $id;
        $this->params['id'] = $id;
    }
    
    public function getStbParams(){

        //$user = $this->db->getFirstData('users', array('mac' => $this->mac));
        $user = $this->db->from('users')
                         ->where(array('mac' => $this->mac))
                         ->get()
                         ->first();
        
        if (!empty($user)){
            $this->params = $user;
            $this->id  = $user['id'];
            $this->hd  = $user['hd'];
            $this->additional_services_on = $user['additional_services_on'];
        }
    }
    
    public function getIdByMAC($mac){
        
        //$user = $this->db->getFirstData('users', array('mac' => $mac));
        $user = $this->db->from('users')
                         ->where(array('mac' => $mac))
                         ->get()
                         ->first();
        
        if(!empty($user) && key_exists('id', $user)){
            return $user['id'];
        }
        
        return false;
    }
    
    private function getAllMACs(){
        //$users = $this->db->getData('users');
        
        $users = $this->db->get('users')->all();
        
        $arr = array();
        foreach ($users as $user){
            $arr[$user['mac']] = intval($user['id']);
        }
        return $arr;
    }
    
    public function getProfile(){
        
        if (!$this->id){
            $this->createProfile();
        }
        
        $this->db->update('users', array(
                'last_start' => 'NOW()',
                'keep_alive' => 'NOW()',
                'version'    => @$_REQUEST['ver'],
                'hd'         => @$_REQUEST['hd'],
            ),
            array('id' => $this->id));
        
        $master = new VideoMaster();
        $master->checkAllHomeDirs();
        
        $profile = $this->params;
        $profile['storages'] = $master->getStoragesForStb();
        
        $itv = Itv::getInstance();
        $profile['last_itv_id'] = $itv->getLastId();
        
        $profile['updated'] = $this->getUpdatedPlaces();
        
        return $profile;
    }
    
    private function createProfile(){
        
        $uid = $this->db->insert('users', array(
                    'mac'  => $this->mac,
                    'name' => substr($this->mac, 12, 16)
                ));
                
        $this->setId($uid);
            
        $this->insertData('updated_places', array('uid' => $this->id));
    }
    
    public function getPreloadImages(){
        $dir = PORTAL_PATH.'/client/i/';
        $files = array();
        
        if (is_dir($dir)) {
            if ($dh = opendir($dir)) {
                while (($file = readdir($dh)) !== false) {
                    if (is_file($dir.$file)){
                        $files[] = 'i/'.$file;
                    }
                }
                closedir($dh);
            }
        }
        
        return $files;
    }
    
    public function setParentPassword(){
        
        if (isset($_REQUEST['pass'])){
            $this->db->update('users', array('parent_password' => $_REQUEST['pass']), array('mac' => $this->mac));
            $this->params['parent_password'] = $_REQUEST['pass'];
        }
        
        return true;
    }
    
    public function setVolume(){
        
        $volume = intval($_REQUEST['vol']);
        
        if($volume < 0 || $volume > 100){
            $volume = 100;
        }
        
        $this->db->update('users', array('volume' => $volume), array('mac' => $this->mac));
        $this->params['volume'] = $volume;
        
        return true;
    }
    
    public function setFavItvStatus(){
        
        $fav_itv_on = intval($_REQUEST['fav_itv_on']);
        
        $this->db->update('users', array('fav_itv_on' => $fav_itv_on), array('mac' => $this->mac));
        $this->params['fav_itv_on'] = $fav_itv_on;
        
        return true;
    }
    
    public function getUpdatedPlaces(){
        //return $this->db->getFirstData('updated_places', array('uid' => $this->id));
        return $this->db->from('updated_places')->where(array('uid' => $this->id))->get()->first();
    }
    
    public function setUpdatedPlaceConfirm(){
        
        $place = $_REQUEST['place'];
        
        $this->db->update('updated_places', array($place => 0), array('uid' => $this->id));
        
        return true;
    }
    
    public function setEventConfirm(){
        
        $event_id = intval($_REQUEST['event_id']);

        Event::setConfirmed($event_id);
        
        return true;
    }
    
    public function getWatchdog(){
        
        $this->db->update('users', 
                          array('keep_alive'       => 'NOW()',
                                'ip'               => $this->ip,
                                'now_playing_type' => intval($_REQUEST['cur_play_type'])
                               ), 
                          array('mac' => $this->mac));
        
        
        $events = Event::getAllNotEndedEvents($this->id);
        
        $messages = count($events);
                
        $res = array();
        $res['msgs'] = $messages;
        
        if ($messages>0){
            if ($events[0]['sended'] == 0){
                
                Event::setSended($events[0]['id']);
                
                if($events[0]['need_confirm'] == 0){
                    Event::setEnded($events[0]['id']);
                }
            }
            
            if ($events[0]['id'] != @$_REQUEST['event_active_id']){
                $res['id']    = $events[0]['id'];
                $res['event'] = $events[0]['event'];
                $res['need_confirm'] = $events[0]['need_confirm'];
                $res['msg']   = $events[0]['msg'];
                $res['reboot_after_ok'] = $events[0]['reboot_after_ok'];
            }
        }
        
        $res['additional_services_on'] = $this->additional_services_on;
        
        $cur_weather = new Curweather();
        $res['cur_weather'] = $cur_weather->getData();
        
        $res['updated'] = $this->getUpdatedPlaces();
        
        return $res;
    }
    
    public function setStreamError(){
        $this->db->insert('stream_error',
                           array(
                                'ch_id'      => intval($_REQUEST['ch_id']),
                                'mac'        => $this->stb->mac,
                                'error_time' => 'NOW()'
                           ));
        return true;
    }
}
?>