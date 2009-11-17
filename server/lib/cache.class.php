<?php
/**
 * Memcache class.
 * @package stalker_portal
 * @author zhurbitsky@gmail.com
 */

class Cache
{
    
    private $memcache;
    
    private static $instance = NULL;
    
    public static function getInstance(){
        if (self::$instance == NULL)
        {
            self::$instance = new Cache();
        }
        return self::$instance;
    }
    
    private function __construct(){
        $memcache = new Memcache;
        $memcache->connect(MEMCACHE_HOST, 11211);
    }
    
    public function get($key){
        
    }
    
    public function set($key, $val, $tags){
        
    }
    
    public function getKey($data){
        
    }
    
    public function getTags($data){
        
    }
}
?>