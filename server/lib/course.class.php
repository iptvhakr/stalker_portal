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
        $this->db = Mysql::getInstance();
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
            $result['title'] = System::word('course_title').$arr[1];
            $result['on_date'] = $arr[1];
            $result['data'] = array();
            $idx = 0;
            
            $old_data = $this->getDataFromDBCache();
            
            if (!key_exists('on_date', $old_data) || $result['on_date'] != $old_data['on_date']){
            //if (1){
            
                foreach ($this->codes as $code){
                    preg_match("/<td align=\"Center\">$code<\/td><td align=\"Center\">([\S]+)<\/td><td align=\"Center\">([\d]+)<\/td><td align=\"Left\">(.*)<\/td><td align=\"Right\">([\d,\.]+)<\/td>/",$content,$arr2);
                    $result['data'][$idx] = array();
                    $result['data'][$idx]['code'] = $code;
                    $result['data'][$idx]['currency'] = $arr2[2].' '.$arr2[1];
                    $result['data'][$idx]['value'] = $arr2[4];
                    
                    $result['data'][$idx]['diff'] = 0;
                    $result['data'][$idx]['trend'] = 0;
                    
                    if (is_array($old_data) && key_exists('data', $old_data) && key_exists($idx, $old_data['data'])){
                    
                        $result['data'][$idx]['diff'] = round(($result['data'][$idx]['value'] - $old_data['data'][$idx]['value']), 4);
                        
                        if ($result['data'][$idx]['diff'] > 0){
                            $result['data'][$idx]['trend'] = 1;
                        }else if ($result['data'][$idx]['diff'] < 0){
                            $result['data'][$idx]['trend'] = -1;
                        }
                    }
                    
                    $idx++;
                }
                
                $this->setDataDBCache($result);
            }else{
                $result = $old_data;
            }
        }
        return $result;
    }
    
    private function getDataFromDBCache(){
        
        $content = $this->db->from($this->cache_table)->get()->first('content');
                
        $content = unserialize(System::base64_decode($content));
        
        if (is_array($content)){
            return $content;
        }else{
            return 0;
        }
    }
    
    private function setDataDBCache($arr){
        
        $content = System::base64_encode(serialize($arr));
        
        $result = $this->db->from($this->cache_table)->get();
        $crc = $result->get('crc');
        
        
        if (md5($content) != $crc){
            
            $data = array(
                          'content' => $content,
                          'updated' => 'NOW()',
                          'url'     => $this->content_url,
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