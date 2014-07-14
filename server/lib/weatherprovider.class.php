<?php

abstract class WeatherProvider{

    protected $pictures = array(
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

    protected $pictures_night = array(
        0 => '_0_moon.png',
        1 => '_1_moon_cl.png',
        5 => '_1_moon_cl.png',
    );

    protected $context_params = array(
        'http' => array(
            'timeout' => 300
        )
    );

    protected $cache_table;

    public function __construct(){
        if (Config::exist('http_proxy')){
            $this->context_params['http']['proxy'] = Config::get('http_proxy');
            $this->context_params['http']['request_fulluri'] = true;

            if (Config::exist('http_proxy_login') && Config::exist('http_proxy_password')){
                $this->context_params['http']['header'] = "Proxy-Authorization: Basic ".base64_encode(Config::get('http_proxy_login').":".Config::get('http_proxy_password'))."\r\n";
            }
        }

        $this->cache_table = strtolower(get_called_class()).'_cache';
    }

    public abstract function getCurrent();

    public abstract function getForecast();

    public abstract function updateFullCurrent();

    public abstract function updateFullForecast();

    public abstract function getCities($country_id, $search = '');

    public abstract function getCityFieldName();

    protected function setCurrentCache($current){

        return $this->setCache($current);
    }

    protected function setForecastCache($forecast){

        return $this->setCache($forecast, false);
    }

    private function setCache($weather, $current = true){

        if (empty($weather['city_id'])){
            return false;
        }

        $city_id = intval($weather['city_id']);

        $field = empty($current) ? 'forecast' : 'current';

        $cache = Mysql::getInstance()->from($this->cache_table)->where(array('city_id' => $city_id))->get()->first();

        if (empty($cache)){
            return Mysql::getInstance()->insert($this->cache_table,
                array(
                     'city_id' => $city_id,
                     'updated_'.$field => 'NOW()',
                     $field => System::base64_encode(serialize($weather))
                )
            );
        }else{
            return Mysql::getInstance()->update($this->cache_table,
                array(
                     $field => System::base64_encode(serialize($weather)),
                     'updated_'.$field => 'NOW()'
                ),
                array('city_id' => $city_id)
            );
        }
    }

    protected function postParse($weather){

        if (!empty($weather['date'])){

            if (strlen($weather['date']) == 10 && !empty($weather['hour'])){
                $weather['date'] = $weather['date'].' '.$weather['hour'].':00:00';
            }

            $target_timezone = Mysql::getInstance()->from('cities')->where(array('id' => Stb::getInstance()->city_id))->get()->first('timezone');

            if (!$target_timezone){
                $target_timezone = Stb::getInstance()->getTimezone();
            }

            $date = new DateTime($weather['date'], new DateTimeZone('Europe/Kiev'));
            $date->setTimeZone(new DateTimeZone($target_timezone));

            $weather['date_orig'] = $weather['date'];
            $weather['date'] = $date->format('Y-m-d H:i:s');
            $weather['hour'] = $date->format('G');

            $weather['pict'] = $this->getPicture($weather);
        }

        return $weather;
    }

    protected function preParse($arr){
        if (array_key_exists('cloud', $arr)){

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

            if (array_key_exists($cloud, $cloud_arr)){
                $arr['cloud_str'] = $cloud_arr[$cloud];
            }else{
                $arr['cloud_str'] = 'n/a';
            }
        }

        if (array_key_exists('w_rumb', $arr) || !empty($arr['wind']) && array_key_exists('rumb', $arr['wind'])){
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
                $arr['w_rumb_str'] = 'na';
            }
        }

        $arr['pict'] = $this->getPicture($arr);

        return $arr;
    }

    protected function getPicture($weather){

        if (array_key_exists('cloud', $weather)){
            $cloud = intval(floor($weather['cloud']/10));
        }else{
            $cloud = 25;
        }

        if (isset($weather['hour']) && ($weather['hour'] >= 21 || $weather['hour'] <= 6)){
            $pictures = $this->pictures_night + $this->pictures;
        }else{
            $pictures = $this->pictures;
        }

        return $pictures[$cloud];
    }
}