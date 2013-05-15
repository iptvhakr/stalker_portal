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
        $this->db_table = 'karaoke';
        
        parent::__construct();
    }
    
    protected function getMediaName(){
        return $this->media_id.'.mpg';
    }
}

?>