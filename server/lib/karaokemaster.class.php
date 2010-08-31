<?php
/**
 * Master for karaoke storages.
 * 
 * @package stalker_portal
 * @author zhurbitsky@gmail.com
 */

class KaraokeMaster extends Master
{
    public function __construct(){
        
        $this->media_type = 'karaoke';
        $this->db_table = 'video';
        
        parent::__construct();
    }
    
    protected function getMediaNameById(){
        return $this->media_id.'.mpg';
    }
}

?>