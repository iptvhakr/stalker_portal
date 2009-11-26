<?php
/**
 * Memcache driver.
 * 
 * @package stalker_portal
 * @author zhurbitsky@gmail.com
 */

class Cache
{
    
    private $backend;
    
    private static $instance = NULL;
    
    public static function getInstance(){
        if (self::$instance == NULL)
        {
            self::$instance = new Cache();
        }
        return self::$instance;
    }
    
    private function __construct(){

        $this->backend = new Memcache;
        
        $this->backend->connect(MEMCACHE_HOST, 11211);
    }
      
    public function get($key){
        
        $val = $this->backend->get($key);
        
        if (!is_array($val)){
            return $val;
        }
        
        if(floatval($val['expire']) < time() && $val['expire'] != 0){
            return false;
        }
        
        if ($this->isInvalidTags($val['tags'])){
            return false;
        }
        
        return $val['data'];
        
    }
    
    public function set($key, $val, $tags, $expire = 0){
        
        $tags = $this->setInvalidTags($tags);
        
        $prepared_val = array(
            'expire' => ($expire == 0)? 0 : $expire + time(),
            'data'   => $val,
            'tags'   => $tags
        );
        
        return $this->backend->set($key, $prepared_val, 0, 0);
    }

    public function setInvalidTags($tags){
        
        $new_tags = array();
        
        $tag_version = strval(microtime(true));
        
        foreach ($tags as $tag){
            
            $tag_name = 'tag_'.$tag;
            
            $this->backend->set($tag_name, $tag_version);
            $new_tags[$tag_name] = $tag_version;
        }
        
        var_dump($new_tags);
        
        return $new_tags;
    }
    
    private function isInvalidTags($tags){
        
        foreach ($tags as $tag => $version){
            $cur_version = $this->backend->get($tag);
            
            if($cur_version === false){
                $this->backend->set($tag, $version);
            }
            
            if ($cur_version != $version){
                return true;
            }
        }
        return false;
    }
}
?>