<?php

use Stalker\Lib\Core\Mysql;

/**
 * Gismeteo weather
 * 
 * @package stalker_portal
 * @author zhurbitsky@gmail.com
 * @deprecated since version 4.7.1
 */

class Gismeteo
{
    private $db;
    private $cache_table = 'gismeteo_day_weather';
    //private $cache_expire = 3600;
    public  $xml_url = 'http://informer.gismeteo.ru/xml/33837_1.xml';
    private $weekday_arr = array();
    private $tod_arr = array();
    private $month_arr = array();
    private $cloudiness_arr = array();
    private $precipitation_arr = array();
    private $direction_arr = array();
    private $precipitation_img_arr = array('','','','','w_rain.png','w_rain_strong.png','w_snow.png','w_snow.png','w_thunderstorm.png','','w_empty.png');
    private $cloudiness_img_arr = array('w_empty.png','w_cloud_small.png','w_cloud_big.png','w_cloud_black.png');
    
    public function __construct(){
        $this->db = Mysql::getInstance();
        $this->xml_url = $this->xml_url.'?'.time();

        $this->weekday_arr = array('',_('Sun'),_('Mon'),_('Tue'),_('Wed'),_('Thu'),_('Fri'),_('Sat'));
        $this->tod_arr = array(_('Night'),_('Morning'),_('Day'),_('Evening'));
        $this->month_arr = array('',_('Jan'),_('Feb'),_('Mar'),_('Apr'),_('May'),_('Jun'),_('Jul'),_('Aug'),_('Sep'),_('Oct'),_('Nov'),_('Dec'));
        $this->cloudiness_arr = array(_('clear'),_('partly cloudy'),_('cloudy'),_('overcast'));
        $this->precipitation_arr = array('','','','',_('rain'),_('rainfall'),_('snow'),_('snow'),_('thunderstorm'),_('no data'),_('w/o precipitation'));
        $this->direction_arr = array('N','NE','E','SE','S','SW','W','NW');
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
                $month_id = intval($item->attributes()->month);
                $months = $this->month_arr[$month_id];
                $weekday_id = strval($item->attributes()->weekday);
                $weekday = $this->weekday_arr[$weekday_id];
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

                $wind_from_id = strval($item->WIND->attributes()->direction);
                $wind_from = $this->direction_arr[$wind_from_id];
                $wind_min = strval($item->WIND->attributes()->min);
                $wind_max = strval($item->WIND->attributes()->max);
                $wind = $wind_from.', '.$wind_min.'-'.$wind_max;
                $wind_str = $wind.' м/с';
                
                $gis_arr[$i]['title']       = $title;
                $gis_arr[$i]['tod']         = $tod;
                $gis_arr[$i]['tod_id']      = $tod_id;
                $gis_arr[$i]['day']         = $day;
                $gis_arr[$i]['months']      = $months;
                $gis_arr[$i]['month_id']    = $month_id;
                $gis_arr[$i]['weekday']     = $weekday;
                $gis_arr[$i]['weekday_id']  = $weekday_id;
                $gis_arr[$i]['temperature'] = $temperature;
                $gis_arr[$i]['phenomena']   = $phenomena;
                $gis_arr[$i]['pressure']    = $pressure;
                $gis_arr[$i]['wind_from_id']= $wind_from_id;
                $gis_arr[$i]['wind_from']   = $wind_from;
                $gis_arr[$i]['wind_min']    = $wind_min;
                $gis_arr[$i]['wind_max']    = $wind_max;
                $gis_arr[$i]['wind']        = $wind;
                $gis_arr[$i]['cloudiness']    = $cloudiness;
                $gis_arr[$i]['cloudiness_id']    = $cloudiness_id;
                $gis_arr[$i]['precipitation']    = $precipitation;
                $gis_arr[$i]['precipitation_id'] = $precipitation_id;
                $gis_arr[$i]['description'] = $phenomena.',<br>'._('pressure').' '.$pressure_str.', '._('wind').' '.$wind_str;
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

            $content = array_map(function($day){

                $day['title'] = _($day['tod']).' '.$day['day'].' '._($day['months']).', '._($day['weekday']);
                $day['phenomena']   = _($day['cloudiness']).', '._($day['precipitation']);
                //var_dump($day['wind_from']);
                $day['wind'] = _($day['wind_from']).', '.$day['wind_min'].'-'.$day['wind_max'];

                return $day;

            },
            $content);

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