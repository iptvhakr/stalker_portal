<?php
/**
 * Main ITV class.
 * 
 * @package stalker_portal
 * @author zhurbitsky@gmail.com
 */

class Itv
{
    
    private $db;
    private $stb;
    
    private static $instance = NULL;
    
    public static function getInstance(){
        if (self::$instance == NULL)
        {
            self::$instance = new Itv();
        }
        return self::$instance;
    }
    
    private function __construct(){
        $this->db  = Mysql::getInstance();
        $this->stb = Stb::getInstance();
    }
    
    public function setPlayed(){
        $itv_id = intval($_REQUEST['itv_id']);
        
        $this->db->insert('played_itv', array(
                                            'itv_id'   => $itv_id,
                                            'uid'      => $this->stb->id,
                                            'playtime' => 'NOW()'
                                        ));
        
        $this->db->update('users',
                          array('time_last_play_tv' => 'NOW()'),
                          array('id' => $this->stb->id));
        
        $this->setLastId($itv_id);
        
        return true;
    }
    
    public function getLastId(){
        
        //$last_id_arr = $this->db->getFirstData('last_id', array('ident' => $this->stb->mac));
        $last_id_arr = $this->db->from('last_id')
                                ->where(array('ident' => $this->stb->mac))
                                ->get()
                                ->first();
        
        if(!empty($last_id_arr) && key_exists('last_id', $last_id_arr)){
            return $last_id_arr['last_id'];
        }
        
        return 0;
    }
    
    public function setLastId($id){
        
        if (!$id){
            $id = intval($_REQUEST['id']);
        }
        
        //$last_id_arr = $this->db->getFirstData('last_id', array('ident' => $this->stb->mac));
        $last_id_arr = $this->db->from('last_id')
                                ->where(array('ident' => $this->stb->mac))
                                ->get()
                                ->first();
        
        if (!empty($last_id_arr) && key_exists('last_id', $last_id_arr)){
            $this->db->update('last_id', array('last_id' => $id), array('ident' => $this->stb->mac));
        }else{
            $this->db->insert('last_id', array('last_id' => $id));
        }
        
        return true;
    }
    
    public function setFav($uid = null){
        
        if (!$uid){
            $uid = $this->stb->id;
        }
        
        $fav_ch = @$_REQUEST['fav_ch'];
        
        if (empty($fav_ch)){
            $fav_ch = array();
        }
        
        if (is_array($fav_ch)){
            $fav_ch_str = base64_encode(serialize($fav_ch));
            
            //$fav_itv_arr = $this->db->getFirstData('fav_itv', array('uid' => $uid));
            $fav_itv_arr = $this->db->from('fav_itv')->where(array('uid' => $uid))->get()->first();
            
            if (empty($fav_itv_arr)){
                $this->db->insert('fav_itv',
                                   array(
                                        'uid'     => $uid,
                                        'fav_ch'  => $fav_ch_str,
                                        'addtime' => 'NOW()'
                                   ));
            }else{
                $this->db->update('fav_itv',
                                   array(
                                        'fav_ch'  => $fav_ch_str,
                                        'addtime' => 'NOW()'
                                   ),
                                   array('uid' => $uid));
            }
        }
        
        return true;
    }
    
    public function getFav($uid = null){
        
        if (!$uid){
            $uid = $this->stb->id;
        }
        
        //$fav_itv_ids_arr = $this->db->getFirstData('fav_itv', array('uid' => $uid));
        $fav_itv_ids_arr = $this->db->from('fav_itv')->where(array('uid' => $uid))->get()->first();
        
        if (!empty($fav_itv_ids_arr)){
            $fav_ch = unserialize(base64_decode($fav_itv_ids_arr['fav_ch']));
            
            if (is_array($fav_ch)){
                return $fav_ch;
            }
        }
        
        return array();
    }
    
    public function getListByNumber(){
        
        $page = intval($_REQUEST['p']);
        
        $this->db->from('itv')
                 ->where(array('status' => 1));
    }
    
    public function getChannels(){
        
        $query = $this->db->from('itv')
                        ->where(array(
                            'censored' => 0
                        ));
        
        if (!$this->stb->isModerator()){
            $query->where(array('status' => 1));
        }
        
        return $query;
    }
    
    public function getAllChannels(){
        
        return $this->getChannels()
                    ->orderby('number')
                    ->get()
                    ->all();
        
    }
    
    public function getAllFavChannels(){
        
        $fav_ids = $this->getFav();
        
        return $this->getChannels()
                    ->in('id' , $fav_ids)
                    ->orderby('number')
                    ->get()
                    ->all();
        
    }
    
    public function getFavItvIds(){
        
        return $this->getFav();
        
    }
    
}
?>