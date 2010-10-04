<?php

class Weatherco
{

    private $db;
    private $cache_table = 'weatherco_cache';
    private $xml_url = '';
    private $current = array();
    private $forecast = array();
    private $cache = array();
    
    public function __construct(){
        
        $this->db = Mysql::getInstance();
        
        $this->xml_url = 'http://xml.weather.co.ua/1.2/forecast/'.WEATHERCO_CITY_ID.'?dayf=1&lang=ru&userid=infomir.com.ua';
    }
    
    private function getFromNet(){
        
        $xml_resp = simplexml_load_file($this->xml_url);
        
        if ($xml_resp){
            
            $current = array();
            
            foreach ($xml_resp->current[0] as $field => $val){
                $current[$field] = strval($val);
            }
            
            $this->current = $this->parse($current);
            
            foreach ($xml_resp->forecast->day as $day){
                $item = array();
                
                $item['date'] = strval($day->attributes()->date);
                $item['hour'] = strval($day->attributes()->hour);
                
                foreach ($day as $field => $val){

                    if ($val->count() > 0){
                        $child = array();
                        foreach ($val as $child_name => $child_val){
                            $child[$child_name] = strval($child_val);
                        }
                        $item[$field] = $child;
                    }else{
                        $item[$field] = strval($val);
                    }
                }
                
                $this->forecast[] = $this->parse($item);
            }
            
            $this->setCache();
        
            return true;
        }

        return false;
    }
    
    private function getFromCache(){
        
        $this->cache = $this->db->from($this->cache_table)->get()->first();
        
        if (!empty($this->cache['current'])){
            $this->current = unserialize(System::base64_decode($this->cache['current']));
        }
        
        if (!empty($this->cache['forecast'])){
            $this->forecast = unserialize(System::base64_decode($this->cache['forecast']));
        }
    }
    
    private function setCache(){
        
        $data = array();
        
        if (!empty($this->current)){
            $data['current'] = System::base64_encode(serialize($this->current));
        }
        
        if (!empty($this->forecast)){
            $data['forecast'] = System::base64_encode(serialize($this->forecast));
        }

        if (!empty($data)){
            
            $data['url']     = $this->xml_url;
            $data['updated'] = 'NOW()';
            
            if ($this->db->get($this->cache_table)->count() == 1){
                
                $this->db->update($this->cache_table, $data);
            }else{
                
                $this->db->insert($this->cache_table, $data);
            }
        }
    }
    
    public function update(){
        
        return $this->getFromNet();
    }
    
    public function getCurrent(){
        
        if (empty($this->cache)){
            $this->getFromCache();
        }
        
        return $this->current;
    }
    
    public function getForecast(){
        
        if (empty($this->cache)){
            $this->getFromCache();
        }
        
        return $this->forecast;
    }
    
    private function parse($arr){
        if (key_exists('cloud', $arr)){
            
            $cloud = intval(floor($arr['cloud']/10));
            
            $cloud_arr = array(
                 0 => 'Ясно',
                 1 => 'Переменная облачность',
                 2 => 'Облачно',
                 3 => 'Пасмурно',
                 4 => 'Кратковременный дождь',
                 5 => 'Дождь',
                 6 => 'Гроза',
                 7 => 'Град',
                 8 => 'Мокрый снег',
                 9 => 'Снег',
                 10 => 'Снегопад'
             );
             
             if (key_exists($cloud, $cloud_arr)){
                 $arr['cloud_str'] = $cloud_arr[$cloud];
             }else{
                 $arr['cloud_str'] = 'n/a';
             }
        }
        
        if (key_exists('w_rumb', $arr)){
            $arr['w_rumb_str'] = '';
            
            $w_rumb = $arr['w_rumb'];
            
            if (($w_rumb > 325 && $w_rumb <= 360) || ($w_rumb >= 0 && $w_rumb < 35)){
                $arr['w_rumb_str'] = 'С';
            }elseif ($w_rumb >= 35 && $w_rumb <= 55){
                $arr['w_rumb_str'] = 'СВ';
            }elseif ($w_rumb > 55 && $w_rumb < 125){
                $arr['w_rumb_str'] = 'В';
            }elseif ($w_rumb >= 125 && $w_rumb <= 145){
                $arr['w_rumb_str'] = 'ЮВ';
            }elseif ($w_rumb > 145 && $w_rumb < 215){
                $arr['w_rumb_str'] = 'Ю';
            }elseif ($w_rumb >= 215 && $w_rumb <= 235){
                $arr['w_rumb_str'] = 'ЮЗ';
            }elseif ($w_rumb > 235 && $w_rumb < 305){
                $arr['w_rumb_str'] = 'З';
            }elseif ($w_rumb >= 305 && $w_rumb <= 325){
                $arr['w_rumb_str'] = 'СЗ';
            }
        }
        
        if (key_exists('rumb', $arr)){
            
        }
        
        if (key_exists('pict', $arr)){
            
            preg_match("/(.*)\.gif/", $arr['pict'], $match);
            
            $arr['pict'] = $match[1].'.png';
        }
        
        return $arr;
    }
}

?>