<?php

use Stalker\Lib\Core\Mysql;

/**
 * Basic rss widget class
 * 
 * @package stalker_portal
 * @author zhurbitsky@gmail.com
 */

class Widget
{
    public $db;
    public $widget_name;
    public $cache_table;
    public $cache_expire = 3600;
    public $rss_url;
    public $rss_fields = array();
    public $rss_atributes = array();
    
    public function __construct(){
        $this->db = Mysql::getInstance();
        $this->cache_table = "rss_cache_".$this->widget_name;
    }
    
    public function getData(){
        return $this->getDataFromDBCache();
    }
    
    public function getDataFromRSS(){
        $rss_new = array();
        $rss = simplexml_load_file($this->rss_url);
        if ($rss){
            foreach ($rss->channel->item as $item){
                $new_item = array();
                foreach ($this->rss_fields as $field){
                    $new_item[$field] = strval($item->$field);
                }
                foreach ($this->rss_atributes as $atribute){
                    $new_item['attributes_'.$atribute]   = strval($item->enclosure->attributes()->$atribute);
                }
                $rss_new[] = $new_item;
            }
            $this->setDataDBCache($rss_new);
            return $rss_new;
        }
    }
    
    private function getDataFromDBCache(){
        
        /*$sql = "select * from $this->cache_table";
        $rs = $this->db->executeQuery($sql);*/
        
        $content = $this->db->get($this->cache_table)->first('content');
        
        $content = unserialize(base64_decode($content));
        
        if (is_array($content)){
            return $content;
        }else{
            return 0;
        }
    }
    
    private function setDataDBCache($arr){
        
        $content = base64_encode(serialize($arr));
        
        /*$sql = "select * from $this->cache_table";
        $rs = $this->db->executeQuery($sql);*/
        
        $result = $this->db->get($this->cache_table);
        
        if (md5($content) != $result->first('crc')){
            
            $data = array(
                          'content' => $content,
                          'updated' => 'NOW()',
                          'url'     => $this->rss_url,
                          'crc'     => md5($content)
                      );
            
            if ($result->count() == 1){
                
                $this->db->update($this->cache_table,
                                  $data);
            }else{

                $this->db->insert($this->cache_table,
                                  $data);
            }
        }
    }
}

?>