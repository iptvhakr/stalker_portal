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
        parent::__construct();
        $this->media_type = 'vclub';
    }
    
    protected function getMediaNameById($media_id){
        
        //$media_name = $this->db->executeQuery('select * from video where id='.$media_id)->getValueByName(0, 'path');
        $media_name = $this->db->from('video')
                               ->where(array('id' => $media_id))
                               ->get()
                               ->first('path');
        
        if ($media_name){
            return $media_name;
        }else{
            return '';
        }
    }
}

?>