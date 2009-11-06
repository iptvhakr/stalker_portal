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
        $this->db = Database::getInstance(DB_NAME);
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
                
                $pressure = strval($item->PRESSURE->attributes()->min).'..'.strval($item->PRESSURE->attributes()->max).' мм рт.ст.';
                
                $wind = $this->direction_arr[strval($item->WIND->attributes()->direction)].',<br> '.strval($item->WIND->attributes()->min).'-'.strval($item->WIND->attributes()->max).' м/с';
                
                $gis_arr[$i]['title'] = $title;
                $gis_arr[$i]['temperature'] = $temperature;
                $gis_arr[$i]['description'] = $phenomena.',<br>давление '.$pressure.', ветер '.$wind;
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
                $sql = "update $this->cache_table set content='$content', updated=NOW(), url='$this->xml_url', crc=MD5('$content')";
                $rs = $this->db->executeQuery($sql);
            }else{
                $sql = "insert into $this->cache_table (content, updated, url, crc) value ('$content', NOW(), '".$this->xml_url."', MD5('$content'))";
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