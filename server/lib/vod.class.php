<?php
/**
 * Main VOD class.
 * 
 * @package stalker_portal
 * @author zhurbitsky@gmail.com
 */

class Vod
{
    
    private $db;
    private $stb;
    
    private static $instance = NULL;
    
    public static function getInstance(){
        if (self::$instance == NULL)
        {
            self::$instance = new Vod();
        }
        return self::$instance;
    }
    
    public function __construct(){
        $this->db  = Mysql::getInstance();
        $this->stb = Stb::getInstance();
    }
    
    public function createLink(){
        
        preg_match("/auto \/media\/(\d+).mpg$/", $_REQUEST['cmd'], $tmp_arr);
            
        $media_id = $tmp_arr[1];
        
        $master = new VideoMaster();
        
        try {
            $res = $master->play($media_id, intval($_REQUEST['series']));
        }catch (Exception $e){
            trigger_error($e->getMessage());
        }
        
        var_dump($res);
        
        return $res;
    }
    
    public function getMediaCats(){
        
        return $this->db->getData('media_category');
        
    }
    
    public function setVote(){
        
        if ($_REQUEST['vote'] == 'good'){
            $good = 1;
            $bad = 0;
        }else{
            $good = 0;
            $bad = 1;
        }
        
        $type = $_REQUEST['type'];
        
        $this->db->insertData('vclub_vote',
                               array(
                                    'media_id'  => intval($_REQUEST['media_id']),
                                    'uid'       => $this->stb->id,
                                    'vote_type' => $type,
                                    'good'      => $good,
                                    'bad'       => $bad,
                                    'added'     => 'NOW()'
                               ));
        
        $video = $this->db->getFirstData('video', array('id' => intval($_REQUEST['media_id'])));
        
        $this->db->updateData('video',
                               array(
                                    'vote_'.$type.'_good' => $video['vote_'.$type.'_good'] + $good,
                                    'vote_'.$type.'_bad'  => $video['vote_'.$type.'_bad'] + $bad,
                               ),
                               array('id' => intval($_REQUEST['media_id'])));
        
        return true;
    }
    
    public function setPlayed(){
        
        $video_id   = intval($_REQUEST['video_id']);
        $storage_id = intval($_REQUEST['storage_id']);
        
        if ($day <= date("j")){
            $field_name = 'count_first_0_5';
        }else{
            $field_name = 'count_second_0_5';
        }
        
        $video = $this->db->getFirstData('video', array('id' => $video_id));
        
        $this->db->updateData('video',
                               array(
                                    $field_name   => $video[$field_name] + 1,
                                    'count'       => $video['count'] + 1,
                                    'last_played' => 'NOW()'
                               ),
                               array('id' => $video_id));
        
        $this->db->insertData('played_video',
                               array(
                                    'video_id' => $video_id,
                                    'uid'      => $this->stb->id,
                                    'storage'  => $storage_id,
                                    'playtime' => 'NOW()'
                               ));
        
        $this->db->updateData('users',
                               array('time_last_play_video' => 'NOW()'),
                               array('id' => $this->stb->id));
        
        $today_record = $this->db->getFirstData('daily_played_video', array('date' => 'CURDATE()'));
        
        if (empty($today_record)){
            
            $this->db->insertData('daily_played_video',
                                   array(
                                        'count' => 1,
                                        'date'  => 'CURDATE()'
                                   ));
            
        }else{
            
            $this->db->updateData('daily_played_video',
                                   array(
                                        'count' => $today_record['count'] + 1,
                                        'date'  => 'NOW()'
                                   ));
            
        }
        
        $played_video = $this->db->getData('stb_played_video',
                            array(
                                'uid' => $this->stb->id,
                                'video_id' => $video_id
                            ));
        
        if (empty($played_video)){
            
            $this->db->insertData('stb_played_video',
                                   array(
                                        'uid'      => $this->stb->id,
                                        'video_id' => $video_id,
                                        'playtime' => 'NOW()'
                                   ));
            
        }else{
            
            $this->db->updateData('stb_played_video',
                                   array('playtime' => 'NOW()'),
                                   array(
                                        'uid'      => $this->stb->id,
                                        'video_id' => $video_id
                                   ));
            
        }
        
        return true;
    }
    
    public function setFav(){
        
        $new_id = intval($_REQUEST['video_id']);

        $fav_video = $this->getFav();
        
        if (!is_array($fav_video)){
            $this->db->insertData('fav_vclub',
                                   array(
                                        'uid'       => $this->stb->id,
                                        'fav_video' => serialize(array($new_id)),
                                        'addtime'   => 'NOW()'
                                   ));
             return true;                      
        }
        
        if (!in_array($new_id, $fav_video)){
            
            $fav_video[] = $new_id;
            $fav_video_s = serialize($fav_video);
            
            $this->db->updateData('fav_vclub',
                                   array(
                                        'fav_video' => $fav_video_s,
                                        'edittime'  => 'NOW()'),
                                   array('uid' => $this->stb->id));
            
        }
        
        return true;
    }
    
    public function getFav(){
        
        $fav_video_arr = $this->db->getFirstData('fav_vclub', array('uid' => $this->stb->id));
        
        if (empty($fav_video_arr)){
            return null;
        }
        
        $fav_video = unserialize($fav_video_arr['fav_video']);
        
        if (!is_array($fav_video)){
            $fav_video = array();
        }
        
        return $fav_video;
    }
    
    public function delFav(){
        
        $del_id = intval($_REQUEST['video_id']);
        
        $fav_video = $this->getFav();
        
        if (is_array($fav_video)){

            if (in_array($del_id, $fav_video)){
                
                unset($fav_video[array_search($del_id, $fav_video)]);
                
                $fav_video_s = serialize($fav_video);
                
                $this->db->updateData('fav_vclub',
                                       array(
                                            'fav_video' => $fav_video_s,
                                            'edittime'  => 'NOW()'
                                       ),
                                       array('uid' => $this->stb->id));
                
            }
        }
        
        return true;
    }
    
    public function setNotEnded(){
        
        $video_id   = intval($_REQUEST['video_id']);
        $series     = intval($_REQUEST['series']);
        $end_time   = intval($_REQUEST['end_time']);
        
        $not_ended = $this->db->getFirstData('vclub_not_ended',
                                              array(
                                                   'uid' => $this->stb->id,
                                                   'video_id' => $video_id
                                              ));
        
        
        
        if (empty($not_ended)){

            $this->db->insertData('vclub_not_ended',
                                   array(
                                        'uid'      => $this->stb->id,
                                        'video_id' => $video_id,
                                        'series'   => $series,
                                        'end_time' => $end_time,
                                        'added'    => 'NOW()'
                                   ));
            
        }else{
            
            $this->db->updateData('vclub_not_ended',
                                   array(
                                        'series'   => $series,
                                        'end_time' => $end_time,
                                        'added'    => 'NOW()'
                                   ),
                                   array(
                                        'uid'      => $this->stb->id,
                                        'video_id' => $video_id
                                   ));
            
        }
        
        return true;
    }
    
    
}

?>