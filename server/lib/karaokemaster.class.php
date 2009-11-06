<?php
/**
 * Master for karaoke storages
 * 
 * @package stalker_portal
 * @author zhurbitsky@gmail.com
 */

class KaraokeMaster extends Master
{
    public function __construct(){
        parent::__construct();
        $this->media_type = 'karaoke';
    }
    
    protected function getMediaNameById($media_id){
        return $media_id.'.mpg';
    }
}

?>