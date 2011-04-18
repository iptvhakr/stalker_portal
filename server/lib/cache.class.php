<?php
/**
 * Memcache driver.
 * Implementation of http://www.smira.ru/2008/10/29/web-caching-memcached-5/
 * 
 * @package stalker_portal
 * @author zhurbitsky@gmail.com
 */

class Cache
{
    
    private $backend;
    private $use_custom_caching = false;
    
    private static $instance = NULL;

    /**
     * @static
     * @return Cache
     */
    public static function getInstance(){
        if (self::$instance == NULL)
        {
            self::$instance = new Cache();
        }
        return self::$instance;
    }
    
    private function __construct(){

        $this->backend = new Memcache;
        
        $this->backend->connect(Config::get('memcache_host'), 11211);
    }

    public function useCustomCaching($val){
        $this->use_custom_caching = !!$val;
    }

    private function customGet($key){
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

    private function realGet($key){
        return $this->backend->get($key);
    }
      
    public function get($key){
        
        if ($this->use_custom_caching){
            return $this->customGet($key);
        }else{
            return $this->realGet($key);
        }
    }

    private function customSet($key, $val, $tags, $expire = 0){

        $tags = $this->setInvalidTags($tags);

        $prepared_val = array(
            'expire' => ($expire == 0)? 0 : $expire + time(),
            'data'   => $val,
            'tags'   => $tags
        );

        return $this->backend->set($key, $prepared_val, 0, 0);
    }

    private function realSet($key, $val, $flag = 0, $expire = 0){
        return $this->backend->set($key, $val, $flag, $expire);
    }
    
    public function set($key, $val, $tags = array(), $expire = 0){
        
        if ($this->use_custom_caching){
            return $this->customSet($key, $val, $tags, $expire);
        }else{
            return $this->realSet($key, $val, 0, $expire);
        }
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