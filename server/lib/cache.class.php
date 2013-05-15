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
        
        $status = $this->backend->connect(Config::get('memcache_host'), 11211);

        //var_dump($status);

        if (!$status){
            throw new Exception("Could not connect to memcached. Host: ".Config::get('memcache_host'));
        }
    }

    public function useCustomCaching($val){
        $this->use_custom_caching = !!$val;
    }

    private function customGet($key){

        $val = $this->backend->get($key);

        var_dump('-----------------------customGet', $key);

        if (!is_array($val)){
            return $val;
        }

        if(floatval($val['expire']) < time() && $val['expire'] != 0){
            $this->miss();
            return false;
        }

        if ($this->isInvalidTags($val['tags'])){
            $this->miss();
            return false;
        }

        $this->hit();

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

        var_dump('-----------------------customSet', $tags);

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
    
    public function del($key){

        return $this->backend->delete($key, 0);
    }

    public function setInvalidTags($tags){

        var_dump('-----------------------setInvalidTags', $tags);

        $new_tags = array();
        
        $tag_version = strval(microtime(true));
        
        foreach ($tags as $tag){
            
            $tag_name = 'tag_'.$tag;
            
            $this->backend->set($tag_name, $tag_version);
            $new_tags[$tag_name] = $tag_version;
        }
        
        //var_dump($new_tags);
        
        return $new_tags;
    }
    
    private function isInvalidTags($tags){

        var_dump('-----------------------isInvalidTags', $tags);
        
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

    private function hit(){

        var_dump('!!!!!!!!!!!!!++++++++++++HIT');

        $stats = $this->backend->get('stats');

        if ($stats === false || empty($stats)){
            $stats = array();
        }

        !key_exists('hits', $stats) ? $stats['hits'] = 0 : $stats['hits'] += 1;

        $this->backend->set('stats', $stats);
    }

    private function miss(){

        var_dump('!!!!!!!!!!!!!----------MISS');

        $stats = $this->backend->get('stats');

        if ($stats === false || empty($stats)){
            $stats = array();
        }

        !key_exists('misses', $stats) ? $stats['misses'] = 0 : $stats['misses'] += 1;

        $this->backend->set('stats', $stats);
    }
}
?>