<?php
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
        $this->db = Database::getInstance(DB_NAME);
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
        $sql = "select * from $this->cache_table";
        $rs = $this->db->executeQuery($sql);
        $content = unserialize(base64_decode($rs->getValueByName(0, 'content')));
        if (is_array($content)){
            return $content;
        }else{
            return 0;
        }
    }
    
    private function setDataDBCache($arr){
        $content = base64_encode(serialize($arr));
        $sql = "select * from $this->cache_table";
        $rs = $this->db->executeQuery($sql);
        if (md5($content) != @$rs->getValueByName(0, 'crc')){
            if ($rs->getRowCount() == 1){
                $sql = "update $this->cache_table set content='$content', updated=NOW(), url='$this->gapi_url', crc=MD5('$content')";
                $rs = $this->db->executeQuery($sql);
            }else{
                $sql = "insert into $this->cache_table (content, updated, url, crc) value ('$content', NOW(), '".$this->gapi_url."', MD5('$content'))";
                $rs = $this->db->executeQuery($sql);
            }
        }else{
            if ($rs->getRowCount() == 1){
                $sql = "update $this->cache_table set updated=NOW()";
                $rs = $this->db->executeQuery($sql);
            }
        }
    }
}
?>