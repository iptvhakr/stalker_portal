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
    
    public function __construct(){
        
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

        $user = $this->db->getFirstData('users', array('mac' => $this->mac));
        
        if (!empty($user)){
            $this->params = $user;
            $this->id  = $user['id'];
            $this->hd  = $user['hd'];
            $this->additional_services_on = $user['additional_services_on'];
        }
    }
    
    public function getIdByMAC($mac){
        
        $user = $this->db->getFirstData('users', array('mac' => $mac));
        
        if(!empty($user) && key_exists('id', $user)){
            return $user['id'];
        }
        
        return false;
    }
    
    private function getAllMACs(){
        $users = $this->db->getData('users');
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
        
        $this->db->updateData('users', array(
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
        $profile['last_itv_id'] = $this->getLastItvId();
        
        //$updated_places_arr = $this->db->getData('updated_places', array('uid' => $this->id));
        
        $profile['updated'] = $this->getUpdatedPlaces();
/*            'anec'  => intval($updated_places_arr[0]['anec']),
            'vclub' => intval($updated_places_arr[0]['vclub'])
        );*/
        
        return $profile;
    }
    
    private function createProfile(){
        
        $uid = $this->db->insertData('users', array(
                    'mac'  => $this->mac,
                    'name' => substr($this->mac, 12, 16)
                ));
                
        $this->setId($uid);
            
        $this->insertData('updated_places', array('uid' => $this->id));
    }
    
    public function getLastItvId(){
        
        $last_id_arr = $this->db->getFirstData('last_id', array('ident' => $this->mac));
        
        if(!empty($last_id_arr) && key_exists('last_id', $last_id_arr)){
            return $last_id_arr['last_id'];
        }
        
        return 0;
    }
    
    public function setLastItvId(){
        
        $last_id_arr = $this->db->getFirstData('last_id', array('ident' => $this->mac));
        
        if (!empty($last_id_arr) && key_exists('last_id', $last_id_arr)){
            $this->db->updateData('last_id', array('last_id' => $_REQUEST['id']), array('ident' => $this->mac));
        }else{
            $this->db->insertData('last_id', array('last_id' => $_REQUEST['id']));
        }
        
        return true;
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
            $this->db->updateData('users', array('parent_password' => $_REQUEST['pass']), array('mac' => $this->mac));
            $this->params['parent_password'] = $_REQUEST['pass'];
        }
        
        return true;
    }
    
    public function setVolume(){
        
        $volume = intval($_REQUEST['vol']);
        
        if($volume < 0 || $volume > 100){
            $volume = 100;
        }
        
        $this->db->updateData('users', array('volume' => $volume), array('mac' => $this->mac));
        $this->params['volume'] = $volume;
        
        return true;
    }
    
    public function setFavItvStatus(){
        
        $fav_itv_on = intval($_REQUEST['fav_itv_on']);
        
        $this->db->updateData('users', array('fav_itv_on' => $fav_itv_on), array('mac' => $this->mac));
        $this->params['fav_itv_on'] = $fav_itv_on;
        
        return true;
    }
    
    public function getUpdatedPlaces(){
        return $this->db->getFirstData('updated_places', array('uid' => $this->id));
    }
    
    public function setUpdatedPlaceConfirm(){
        
        $place = $_REQUEST['place'];
        
        $this->db->updateData('updated_places', array($place => 0), array('uid' => $stb->id));
        
        return true;
    }
    
}
?>