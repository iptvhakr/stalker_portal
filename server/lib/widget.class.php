<?php
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
        $this->db = Database::getInstance(DB_NAME);
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
                $sql = "update $this->cache_table set content='$content', updated=NOW(), url='$this->rss_url', crc=MD5('$content')";
                $rs = $this->db->executeQuery($sql);
            }else{
                $sql = "insert into $this->cache_table (content, updated, url, crc) value ('$content', NOW(), '".$this->rss_url."', MD5('$content'))";
                $rs = $this->db->executeQuery($sql);
            }
        }
    }
}

?>