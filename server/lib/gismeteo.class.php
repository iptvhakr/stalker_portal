<?php
/**
 * Gismeteo weather
 * 
 * @package stalker_portal
 * @author zhurbitsky@gmail.com
 */

class Gismeteo
{
    private $db;
    private $cache_table = 'gismeteo_day_weather';
    //private $cache_expire = 3600;
    public  $xml_url = GISMETEO_XML;
    private $weekday_arr = array('','Вс','Пн','Вт','Ср','Чт','Пт','Сб');
    private $tod_arr = array('Ночь','Утро','День','Вечер');
    private $month_arr = array('','янв','фев','мар','апр','май','июн','июл','авг','сен','окт','ноя','дек');
    private $cloudiness_arr = array('ясно','малооблачно','облачно','пасмурно');
    private $precipitation_arr = array('','','','','дождь','ливень',' снег',' снег','гроза','нет данных','без осадков');
    private $direction_arr = array('С','СВ','В','ЮВ','Ю','ЮЗ','З','СЗ');
    private $precipitation_img_arr = array('','','','','w_rain.png','w_rain_strong.png','w_snow.png','w_snow.png','w_thunderstorm.png','','w_empty.png');
    private $cloudiness_img_arr = array('w_empty.png','w_cloud_small.png','w_cloud_big.png','w_cloud_black.png');
    
    public function __construct(){
        $this->db = Mysql::getInstance();
        $this->xml_url = $this->xml_url.'?'.time();
    }
    
    public function getData(){
        return $this->getDataFromDBCache();
    }
    
    public function getDataFromXML(){
        $gis_arr = array();
        $xml_resp = simplexml_load_file($this->xml_url);
        if ($xml_resp){
            $i=0;
            foreach ($xml_resp->REPORT->TOWN->FORECAST as $item){
                $tod_id = strval($item->attributes()->tod);
                $tod = $this->tod_arr[$tod_id];
                $day = strval($item->attributes()->day);
                $months = $this->month_arr[intval($item->attributes()->month)];
                $weekday = $this->weekday_arr[strval($item->attributes()->weekday)];
                $title = $tod.' '.$day.' '.$months.', '.$weekday;
                
                $t_min = strval($item->TEMPERATURE->attributes()->min);
                $t_max = strval($item->TEMPERATURE->attributes()->max);
                if ($t_min>0){
                    $t_min = '+'.$t_min;
                }
                if ($t_max>0){
                    $t_max = '+'.$t_max;
                }
                $temperature = $t_min.'..'.$t_max.'&ordm;';
                
                $pattern = array("/(\d)/","/&ordm;/","/\+/","/\-/", "/\.\./");
                $replace = array("<img src='i/\\1.png'>","<img src='i/deg.png'>", "<img src='i/plus.png'>", "<img src='i/minus.png'>", "<img src='i/dots.png'>");
    
                $temperature = preg_replace($pattern,$replace,$temperature);
                
                $cloudiness_id = strval($item->PHENOMENA->attributes()->cloudiness);
                $cloudiness = $this->cloudiness_arr[$cloudiness_id];
                $precipitation_id = strval($item->PHENOMENA->attributes()->precipitation);
                $precipitation = $this->precipitation_arr[$precipitation_id];
                $phenomena = $cloudiness.', '.$precipitation;
                
                $pressure = strval($item->PRESSURE->attributes()->min).'..'.strval($item->PRESSURE->attributes()->max);
                $pressure_str = $pressure.' мм рт.ст.';
                
                $wind = $this->direction_arr[strval($item->WIND->attributes()->direction)].', '.strval($item->WIND->attributes()->min).'-'.strval($item->WIND->attributes()->max);
                $wind_str = $wind.' м/с';
                
                $gis_arr[$i]['title'] = $title;
                $gis_arr[$i]['temperature'] = $temperature;
                $gis_arr[$i]['phenomena']   = $phenomena;
                $gis_arr[$i]['pressure']    = $pressure;
                $gis_arr[$i]['wind']        = $wind;
                $gis_arr[$i]['description'] = $phenomena.',<br>давление '.$pressure_str.', ветер '.$wind_str;
                if ($tod_id == 0 || $tod_id == 3){
                    $img_1 = 'w_moon.png';
                }else{
                    $img_1 = 'w_sun.png';
                }
                
                $gis_arr[$i]['img_1'] = $img_1;
                $gis_arr[$i]['img_2'] = $this->precipitation_img_arr[$precipitation_id];
                $gis_arr[$i]['img_3'] = $this->cloudiness_img_arr[$cloudiness_id];
                $i++;
            }
            $this->setDataDBCache($gis_arr);
            return $gis_arr;
        }
    }
    
    private function getDataFromDBCache(){
        
        /*$sql = "select * from $this->cache_table";
        $rs = $this->db->executeQuery($sql);*/
        
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
        
        /*$sql = "select * from $this->cache_table";
        $rs = $this->db->executeQuery($sql);*/
        
        $result = $this->db->from($this->cache_table)->get();
        
        $crc = $result->get('crc');
        
        if (md5($content) != $crc){
            
            $data = array(
                          'content' => $content,
                          'updated' => 'NOW()',
                          'url'     => $this->xml_url,
                          'crc'     => md5($content)
                      );
            
            if ($result->count() == 1){
                
                /*$sql = "update $this->cache_table set content='$content', updated=NOW(), url='$this->xml_url', crc=MD5('$content')";
                $rs = $this->db->executeQuery($sql);*/
                
                $this->db->update($this->cache_table,
                                  $data);
                
            }else{
                
                /*$sql = "insert into $this->cache_table (content, updated, url, crc) value ('$content', NOW(), '".$this->xml_url."', MD5('$content'))";
                $rs = $this->db->executeQuery($sql);*/
                
                $this->db->insert($this->cache_table,
                                  $data);
            }
        }else{
            if ($result->count() == 1){
                
                /*$sql = "update $this->cache_table set updated=NOW()";
                $rs = $this->db->executeQuery($sql);*/
                
                $this->db->update($this->cache_table, array('updated' => 'NOW()'));
                
            }
        }
    }
}
?>