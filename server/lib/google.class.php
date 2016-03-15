<?php

use Stalker\Lib\Core\Mysql;

/**
 * Google API
 * 
 * @package stalker_portal
 * @author zhurbitsky@gmail.com
 */

class Google
{
    private $db;
    public  $gapi_name;
    public  $gapi_url;
    private $gapi_arr;
    private $cache_table;
    public  $cache_expire = 3600;
    public  $gapi_module;
    public  $gapi_field;
    
    public function __construct(){
        $this->db = Mysql::getInstance();
        $this->cache_table = "gapi_cache_".$this->gapi_name;
    }
    
    public function getData(){
        return $this->getDataFromDBCache();
    }
    
    public function getDataFromGAPI(){
        $gapi_arr = array();
        $gapi_resp = simplexml_load_file($this->gapi_url);
        if ($gapi_resp){
            foreach ($gapi_resp->{$this->gapi_module}->{$this->gapi_field} as $item){
                $new_item = array();
                foreach ($item as $field => $data){
                    $new_item[$field] = strval($data->attributes()->data);
                }
                $gapi_arr[] = $new_item;
            }
            $this->setDataDBCache($gapi_arr);
            return $gapi_arr;
        }
    }
    
    private function getDataFromDBCache(){
        
        /*$sql = "select * from $this->cache_table";
        $rs = $this->db->executeQuery($sql);*/
        
        $content = $this->db->from($this->cache_table)->get()->first('content');
        
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
        
        $result = $this->db->from($this->cache_table)->get();
        $crc = $result->get('crc');
        
        if (md5($content) != $crc){
            
            $data = array(
                          'content' => $content,
                          'updated' => 'NOW()',
                          'url'     => $this->gapi_url,
                          'crc'     => md5($content)
                      );
            
            if ($result->count() == 1){
                
                $this->db->update($this->cache_table, $data);
            }else{                
                
                $this->db->insert($this->cache_table, $data);
            }
            
        }else{
            if ($result->count() == 1){
                $this->db->update($this->cache_table, array('updated' => 'NOW()'));
                
            }
        }
    }
}
?>