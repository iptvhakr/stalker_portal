<?php
/**
 * Master for video storages.
 *
 * @package stalket_portal
 * @author zhurbitsky@gmail.com
 */

class VideoMaster extends Master
{
    public function __construct(){
        
        $this->media_type = 'vclub';
        $this->db_table = 'video';
        
        parent::__construct();
    }
    
    protected function getMediaName(){
        
        if (!empty($this->media_params) && !empty($this->media_params['path'])){
            
            return $this->media_params['path'];
        }
        
        return '';
    }

    protected function getMediaPath($file_name){
        
        return $this->media_name.'/'.$file_name;
    }
    
    protected function saveSeries($series_arr){
        sort($series_arr);
        
        $this->db->update('video',
                          array('series' => serialize($series_arr)),
                          array('id' => $this->media_id));
    }
}

?>