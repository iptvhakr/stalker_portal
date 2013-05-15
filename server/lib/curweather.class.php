<?php
/**
 * Current weather
 * 
 * @package stalker_portal
 * @author zhurbitsky@gmail.com
 * @deprecated
 */

class Curweather extends Google
{
    public $gapi_name   = 'cur_weather';
    public $gapi_module = 'weather';
    public $gapi_url = 'http://www.google.com/ig/api?hl=ru&weather=Odessa,,,46430000,30770000&oe=utf8';
    public $cache_expire = 600;
    public $gapi_field = 'current_conditions';
    
    public function getData(){
        $tmp_arr = parent::getData();
        $new_tmp_arr = $tmp_arr[0];
        /*preg_match("/ (\d*) км\/ч/", $new_tmp_arr['wind_condition'], $arr);
        $new_wind = ceil($arr[1]*1000/3600);
        $first_wind_condition = substr($new_tmp_arr['wind_condition'], 0, strpos($new_tmp_arr['wind_condition'], ","));
        $new_tmp_arr['wind_condition'] = $first_wind_condition.', '.$new_wind.' м/с';*/
        
        return $new_tmp_arr;
    }
}
?>