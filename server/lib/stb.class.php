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
    private $is_moderator = 0;
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
    
    /*public function getIdByMAC($mac){
        
        //$user = $this->db->getFirstData('users', array('mac' => $mac));
        $user = $this->db->from('users')
                         ->where(array('mac' => $mac))
                         ->get()
                         ->first();
        
        if(!empty($user) && key_exists('id', $user)){
            return $user['id'];
        }
        
        return false;
    }*/
    
    /*private function getAllMACs(){
        //$users = $this->db->getData('users');
        
        $users = $this->db->get('users')->all();
        
        $arr = array();
        foreach ($users as $user){
            $arr[$user['mac']] = intval($user['id']);
        }
        return $arr;
    }*/
    
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
    
    public function isModerator(){
        return $this->is_moderator;
    }
    
    public function getPreloadImages(){
        $dir = PORTAL_PATH.'/c/i/';
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
    
    public function setAspect(){
        $aspect = intval($_REQUEST['aspect']);
        
        $this->db->update('users', array('aspect' => $aspect), array('mac' => $this->mac));
        $this->params['aspect'] = $aspect;
        
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
    
    public function log(){
        
        $action = strval($_REQUEST['real_action']);
        $param  = strval($_REQUEST['param']);
        $type   = $_REQUEST['tmp_type'];
        
        $this->db->insert('user_log',
                            array(
                                'mac'    => $this->mac,
                                'action' => $action,
                                'param'  => $param,
                                'time'   => 'NOW()',
                                'type'   => $type
                            ));
        
        $update_data = array();
                            
        if ($action == 'play'){
            $update_data['now_playing_start'] = 'NOW()';

            switch ($type){
                case 1: // TV
                    
                    $ch_name = $this->db->from('itv')->where(array('cmd' => $param, 'status' => 1))->get()->first('name');
                    
                    if (empty($ch_name)){
                        $ch_name = $param;
                    }
                    
                    $update_data['now_playing_content'] = $ch_name;
                    
                    break;
                case 2: // Video Club
                    
                    preg_match("/auto \/media\/([\S\s]+)\/(\d+)\.[a-z]*$/", $param, $tmp_arr);
                    
                    $storage  = $tmp_arr[1];
                    $media_id = intval($tmp_arr[2]);
                    
                    $video = $this->db->from('video')->where(array('id' => $media_id))->get()->first();
                    
                    $update_data['storage_name'] = $storage;
                    
                    if (!empty($video)){
                        
                        $update_data['now_playing_content'] = $video['name'];
                        $update_data['hd_content']          = $video['hd'];
                    }else{
                        $update_data['now_playing_content'] = $param;
                    }
                    
                    break;
                case 3: // Karaoke
                    
                    preg_match("/(\d+).mpg$/", $param, $tmp_arr);
                    $karaoke_id = intval($tmp_arr[1]);
                    
                    $karaoke = $this->db->from('karaoke')->where(array('id' => $karaoke_id))->get->first();
                    
                    if (!empty($karaoke)){
                        $update_data['now_playing_content'] = $karaoke['name'];
                    }else{
                        $update_data['now_playing_content'] = $param;
                    }
                    
                    break;
                case 4: // Audio Club
                    
                    preg_match("/(\d+).mp3$/", $param, $tmp_arr);
                    $audio_id = intval($tmp_arr[1]);
                    
                    $audio = $this->db->from('audio')->where(array('id' => $audio_id))->get()->first();
                    
                    if (!empty($audio)){
                        $update_data['now_playing_content'] = $audio['name'];
                    }else{
                        $update_data['now_playing_content'] = $param;
                    }
                    
                    break;
                case 5: // Radio
                
                    $radio = $this->db->from('radio')->where(array('cmd' => $param, 'status' => 1))->get()->first();
                    
                    if (!empty($radio)){
                        $update_data['now_playing_content'] = $radio['name'];
                    }else{
                        $update_data['now_playing_content'] = $param;
                    }
                    
                    break;
                case 6: // My Records
                
                    /*$my_record_name = '';
                    
                    preg_match("/\/(\d+).mpg/", $param, $tmp_arr);
                    $my_record_id = $tmp_arr[1];
                    
                    $sql = "select t_start,itv.name from users_rec, itv where users_rec.ch_id=itv.id and users_rec.id=$my_record_id";
                    $rs = $db->executeQuery($sql);
                    
                    if ($rs->getRowCount() == 1){
                        $my_record_name = $rs->getValueByName(0, 't_start').' '.$rs->getValueByName(0, 'name');
                    }else{
                        $my_record_name = $param;
                    }
                    
                    $_sql .= ", now_playing_content='$my_record_name'";
                    break;*/
                case 7: // Shared Records
                    /*$shared_record_name = '';
                    
                    preg_match("/(\d+).mpg$/", $param, $tmp_arr);
                    $shared_record_id = $tmp_arr[1];
                    
                    $sql = "select * from video_records where id=$shared_record_id";
                    $rs = $db->executeQuery($sql);
                    
                    if ($rs->getRowCount() == 1){
                        $shared_record_name = $rs->getValueByName(0, 'descr');
                    }else{
                        $shared_record_name = $param;
                    }
                    
                    $_sql .= ", now_playing_content='$shared_record_name'";*/
                    break;
                case 8: // Video clips
                    /*$video_name = '';
                    
                    preg_match("/(\d+).mpg$/", $param, $tmp_arr);
                    $media_id = $tmp_arr[1];
                    
                    $sql = "select * from video_clips where id=$media_id";
                    $rs = $db->executeQuery($sql);
                    
                    if ($rs->getRowCount() == 1){
                        $video_name = $rs->getValueByName(0, 'name');
                    }else{
                        $video_name = $param;
                    }
                    
                    $_sql .= ", now_playing_content='$video_name'";
                    break;*/
                default:
                    $update_data['now_playing_content'] = 'unknown media '.$param;
            }
        }
        
        if ($action == 'infoportal'){
            $update_data['now_playing_start'] = 'NOW()';

            $info_arr = array(
                20 => 'city_info',
                21 => 'anec_page',
                22 => 'weather_page',
                23 => 'game_page',
                24 => 'horoscope_page',
                25 => 'course_page'
            );
            
            if (@$info_arr[$type]){
                $info_name = $info_arr[$type];
            }else{
                $info_name = 'unknown';
            }
            
            $update_data['now_playing_content'] = $info_name;
        }
        
        if ($action == 'stop' || $action == 'close_infoportal'){
            
            $update_data['now_playing_content'] = '';
            $update_data['storage_name'] = '';
            $update_data['hd_content'] = '';
            
            $type = 0;
        }
        
        if ($action == 'pause'){
            
            $this->db->insert('vclub_paused',
                              array(
                                  'uid' => $this->id,
                                  'mac' => $this->mac,
                                  'pause_time' => 'NOW()'
                              ));
        }
        
        if ($action == 'continue' || $action == 'stop' || $action == 'set_pos()' || $action == 'play'){
            
            $this->db->delete('vclub_paused',
                              array(
                                  'mac' => $this->mac
                              ));
        }
        
        if ($action == 'readed_anec'){
            
            return $this->db->insert('readed_anec',
                              array(
                                  'mac'    => $this->mac,
                                  'readed' => 'NOW()'
                              ));
        }
        
        if ($action == 'loading_fail'){
            
            return $this->db->insert('loading_fail',
                                     array(
                                         'mac'   => $this->mac,
                                         'added' => 'NOW()'
                                     ));
        }
        
        $update_data['last_active'] = 'NOW()';
        $update_data['keep_alive']  = 'NOW()';
        $update_data['now_playing_type']  = $type;
        
        $this->db->update('users', $update_data, array('mac' => $this->mac));
        
        return 1;
    }
}
?>