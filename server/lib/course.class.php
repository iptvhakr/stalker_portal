<?php
/**
 * Exchange rate
 * 
 * @package stalker_portal
 * @author zhurbitsky@gmail.com
 */

class Course
{
    public $db;
    public $cache_table;
    public $content_url = 'http://www.bank.gov.ua/Fin_ryn/OF_KURS/Currency/FindByDate.aspx';
    public $codes = array(840, 978, 643);
    
    public function __construct(){
        $this->db = Database::getInstance(DB_NAME);
        $this->cache_table = "course_cache";
    }
    
    public function getData(){
        return $this->getDataFromDBCache();
    }
    
    public function getDataFromURI(){
        $result = array();
        $content = file_get_contents($this->content_url);
        if ($content){
            preg_match("/<SPAN class='h5'>([\d,\.]+)<\/SPAN>/",$content,$arr);
            $result['title'] = 'Курс валют Национального банка Украины на '.$arr[1];
            $result['data'] = array();
            $idx = 0;
            
            foreach ($this->codes as $code){            
                preg_match("/<td align=\"Center\">$code<\/td><td align=\"Center\">([\S]+)<\/td><td align=\"Center\">([\d]+)<\/td><td align=\"Left\">(.*)<\/td><td align=\"Right\">([\d,\.]+)<\/td>/",$content,$arr2);                
                $result['data'][$idx] = array();
                $result['data'][$idx]['currency'] = $arr2[2].' '.$arr2[1];
                $result['data'][$idx]['value'] = $arr2[4];
                $idx++;
            }
        }
        $this->setDataDBCache($result);
        return $result;
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
                $sql = "update $this->cache_table set content='$content', updated=NOW(), url='$this->content_url', crc=MD5('$content')";
                $rs = $this->db->executeQuery($sql);
            }else{
                $sql = "insert into $this->cache_table (content, updated, url, crc) value ('$content', NOW(), '".$this->content_url."', MD5('$content'))";
                $rs = $this->db->executeQuery($sql);
            }
        }
    }
}
?>