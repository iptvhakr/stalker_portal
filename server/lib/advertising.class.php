<?php
/**
 * Advertising for main menu & hot offer;
 * 
 * @package stalker_portal
 * @author zhurbitsky@gmail.com
 */

class Advertising
{
    
    private $db;
    
    public function __construct(){
        $this->db = Database::getInstance();
    }
    
    public function getMain(){
        $ad = $this->db->executeQuery('select * from main_page_ad')->getAllValues();
        
        if (count($ad) > 0){
            return $ad[0];
        }
        
        return null;
    }
    
    public function getMainMini(){
        
        $ad = $this->getMain();
        
        if (key_exists('text', $ad)){
            unset($ad['text']);
        }
        
        return $ad;
    }
    
    public function setMain($title = '', $text = '', $video_id = 0){
        
        $rows = $this->db->executeQuery("select * from main_page_ad")->getRowCount();
        
        $title = mysql_real_escape_string($title);
        $text  = mysql_real_escape_string($text);
        
        if ($rows > 0){
        
            $sql = 'update main_page_ad set title="'.$title.'", text="'.$text.'", video_id='.intval($video_id);
        }else{
            
            $sql = 'insert into main_page_ad (title, text, video_id) values ("'.$title.'", "'.$text.'", '.intval($video_id).')';
        }
        
        $this->db->executeQuery($sql);
    }
    
    public function delMain(){
        $this->db->executeQuery("delete from main_page_ad");
    }
    
}

?>