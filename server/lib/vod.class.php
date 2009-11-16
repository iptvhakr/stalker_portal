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
        
    }
}

?>