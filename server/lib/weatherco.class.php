<?php

class Weatherco
{
    public function updateFullCurrent(){
        
        $xml_resp = simplexml_load_file('http://xml.weather.co.ua/1.2/fullcurrent/');

        if (!$xml_resp){
            throw new Exception("Error loading fullcurrent weather");
        }

        foreach ($xml_resp->current as $current){

            $item = array();

            $item['city_id'] = intval($current->attributes()->city);

            foreach ($current as $field => $val){
                $item[$field] = strval($val);

                if ($field == 'date'){
                    $item['hour'] = date("G", strtotime(strval($val)));
                }
            }

            $item = $this->parse($item);

            $this->setCurrentCache($item);
        }
    }

    public function updateFullForecast(){

        $xml_resp = simplexml_load_file('http://xml.weather.co.ua/1.2/fullforecast/');

        if (!$xml_resp){
            throw new Exception("Error loading fullforecast weather");
        }

        foreach ($xml_resp->forecast as $forecast){

            $weather = array();

            $weather['city_id'] = intval($forecast->attributes()->city);

            $weather['forecast'] = array();

            foreach ($xml_resp->forecast->day as $day){

                $item = array();

                $item['date'] = strval($day->attributes()->date);
                $item['hour'] = intval($day->attributes()->hour);
                $item['timestamp'] = strtotime($item['date'].' '.$item['hour'].':00:00');

                foreach ($day as $field => $val){

                    if ($val->count() > 0){ // work if php>=5.3.0
                        $child = array();
                        foreach ($val as $child_name => $child_val){
                            $child[$child_name] = strval($child_val);
                        }
                        $item[$field] = $child;
                    }else{
                        $item[$field] = strval($val);
                    }
                }

                $weather['forecast'][] = $this->parse($item);

                $this->setForecastCache($weather);
            }
        }
    }

    private function setCurrentCache($current){

        return $this->setCache($current);
    }

    private function setForecastCache($forecast){

        return $this->setCache($forecast, false);
    }

    private function setCache($weather, $current = true){

        if (empty($weather['city_id'])){
            return false;
        }

        $city_id = intval($weather['city_id']);

        $weather['updated'] = 'NOW()';

        $cache = Mysql::getInstance()->from('weatherco_cache')->where(array('city_id' => $city_id))->get()->first();

        $field = empty($current) ? 'forecast' : 'current';

        if (empty($cache)){
            return Mysql::getInstance()->insert('weatherco_cache', array('city_id' => $city_id, $field => System::base64_encode(serialize($weather))));
        }else{
            return Mysql::getInstance()->update('weatherco_cache', array($field => System::base64_encode(serialize($weather))), array('city_id' => $city_id));
        }
    }

    public function getCurrent(){

        $city_id = Stb::getInstance()->city_id;

        $cache = Mysql::getInstance()->from('weatherco_cache')->where(array('city_id' => $city_id))->get()->first();

        $current = unserialize(System::base64_decode($cache['current']));

        if (!empty($current) && is_array($current)){

            $current['city'] = Mysql::getInstance()->from('cities')->where(array('id' => $current['city_id']))->get()->first(_('city_name_field'));
            $current['cloud_str'] = _($current['cloud_str']);
            $current['w_rumb_str'] = _($current['w_rumb_str']);

            return $current;
        }

        return false;
    }

    public function getForecast(){

        $city_id = Stb::getInstance()->city_id;

        $cache = Mysql::getInstance()->from('weatherco_cache')->where(array('city_id' => $city_id))->get()->first();

        $tod_arr   = array(3 => 'Night', 9 => 'Morning', 15 => 'Day', 21=> 'Evening');

        $weather = unserialize(System::base64_decode($cache['forecast']));

        $weather['city']  = Mysql::getInstance()->from('cities')->where(array('id' => $weather['city_id']))->get()->first(_('city_name_field'));

        if (!empty($weather) && is_array($weather)){

            $weather['forecast'] = array_map(function($day) use ($tod_arr){

                $day['title'] = _($tod_arr[$day['hour']]).' '.date("j", $day['timestamp']).' '._(date("M", $day['timestamp'])).', '._(date("D", $day['timestamp']));

                $day['cloud_str']  = _($day['cloud_str']);
                $day['w_rumb_str'] = _($day['w_rumb_str']);

                $day['temperature'] = (($day['t']['min']) > 0 ? '+' : '').$day['t']['min'].'..'.(($day['t']['max']) > 0 ? '+' : '').$day['t']['max'].'&deg;';

                return $day;
            },
            $weather['forecast']);

            return $weather;
        }

        return false;
    }
    
    private function parse($arr){
        if (key_exists('cloud', $arr)){
            
            $cloud = intval(floor($arr['cloud']/10));

            $cloud_arr = array(
                0 => 'Clear',
                1 => 'Partly Cloudy',
                2 => 'Cloudy',
                3 => 'Overcast',
                4 => 'Little rain',
                5 => 'Rain',
                6 => 'Thunderstorm',
                7 => 'Hail',
                8 => 'Sleet',
                9 => 'Snow',
                10 => 'Snowfall'
            );
             
            if (key_exists($cloud, $cloud_arr)){
                $arr['cloud_str'] = $cloud_arr[$cloud];
            }else{
                $arr['cloud_str'] = 'n/a';
            }
        }
        
        if (key_exists('w_rumb', $arr) || !empty($arr['wind']) && key_exists('rumb', $arr['wind'])){
            $arr['w_rumb_str'] = '';

            if (!empty($arr['w_rumb'])){
                $w_rumb = $arr['w_rumb'];
            }else if (!empty($arr['wind']['rumb'])){
                $w_rumb = $arr['wind']['rumb'];
            }else{
                $w_rumb = -1;
            }
            
            if (($w_rumb > 325 && $w_rumb <= 360) || ($w_rumb >= 0 && $w_rumb < 35)){
                $arr['w_rumb_str'] = 'N';
            }elseif ($w_rumb >= 35 && $w_rumb <= 55){
                $arr['w_rumb_str'] = 'NE';
            }elseif ($w_rumb > 55 && $w_rumb < 125){
                $arr['w_rumb_str'] = 'E';
            }elseif ($w_rumb >= 125 && $w_rumb <= 145){
                $arr['w_rumb_str'] = 'SE';
            }elseif ($w_rumb > 145 && $w_rumb < 215){
                $arr['w_rumb_str'] = 'S';
            }elseif ($w_rumb >= 215 && $w_rumb <= 235){
                $arr['w_rumb_str'] = 'SW';
            }elseif ($w_rumb > 235 && $w_rumb < 305){
                $arr['w_rumb_str'] = 'W';
            }elseif ($w_rumb >= 305 && $w_rumb <= 325){
                $arr['w_rumb_str'] = 'NW';
            }else{
                $arr['w_rumb_str'] = 'n/a';
            }
        }

        $pictures = array(
            0 => '_0_sun.png',
            1 => '_1_sun_cl.png',
            2 => '_2_cloudy.png',
            3 => '_3_pasmurno.png',
            4 => '_4_short_rain.png',
            5 => '_5_rain.png',
            6 => '_6_lightning.png',
            7 => '_7_hail.png',
            8 => '_8_rain_swon.png',
            9 => '_9_snow.png ',
            10 => '_10_heavy_snow.png',
            25 => '_255_NA.png',
        );

        $pictures_night = array(
            0 => '_0_moon.png',
            1 => '_1_moon_cl.png',
            5 => '_1_moon_cl.png',
        );

        if ($arr['hour'] > 21 && $arr['hour'] < 6){
            $pictures = $pictures_night + $pictures;
        }

        $arr['pict'] = $pictures[$cloud];
        
        return $arr;
    }

}

?>