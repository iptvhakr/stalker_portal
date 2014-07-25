<?php

class Openweathermap extends WeatherProvider
{

    protected $conditions_map = array(
        '01' => array( // clear sky
            'cloud' => 0,
        ),
        '02' => array( // few clouds
            'cloud' => 10,
        ),
        '03' => array( // scattered clouds
            'cloud' => 20,
        ),
        '04' => array( // broken clouds
            'cloud' => 30,
        ),
        '09' => array( // shower rain
            'cloud' => 40,
        ),
        '10' => array( // rain
            'cloud' => 50,
        ),
        '11' => array( // thunderstorm
            'cloud' => 60,
        ),
        '13' => array( // snow
            'cloud' => 90,
        ),
        '50' => array( // mist
            'cloud' => 30,
        ),
        '906' => array( // hail
            'cloud' => 70,
        ),
        '611' => array( // sleet
            'cloud' => 80,
        ),
        '612' => array( // shower sleet
            'cloud' => 80,
        ),
        '621' => array( // shower snow
            'cloud' => 100,
        ),
        '622' => array( // heavy shower snow
            'cloud' => 100,
        )
    );

    private $appid = '';

    public function __construct(){

        parent::__construct();

        $this->appid = Config::getSafe('openweathermap_appid', '');

        if ($this->appid){
            $this->context_params['http']['header'] = 'x-api-key: '.$this->appid;
        }
    }

    public function getCurrent(){

        $city_id = Stb::getInstance()->openweathermap_city_id;

        if ($city_id == 0){
            return array('error' => 'not_configured');
        }

        $cache = Mysql::getInstance()->from($this->cache_table)->where(array('city_id' => $city_id))->get()->first();

        if (empty($cache) || empty($cache['current'])){
            $current = $this->getCurrentFromSource($city_id, $cache);

            // refresh cache data
            $cache = Mysql::getInstance()->from($this->cache_table)->where(array('city_id' => $city_id))->get()->first();

        }elseif(empty($cache['current'])){
            return array('repeat_time' => 10);
        }else{
            $current = unserialize(System::base64_decode($cache['current']));
        }

        if (time() - strtotime($cache['last_request']) > 600){
            Mysql::getInstance()->update($this->cache_table,
                array(
                     'last_request' => 'NOW()'
                ),
                array('id' => $cache['id'])
            );
        }

        if (!empty($current) && is_array($current)){

            $current['city'] = Mysql::getInstance()
                ->from('all_cities')
                ->where(array('id' => $current['city_id']))
                ->get()
                ->first(Stb::getInstance()->getStbLanguage() == 'ru' ? 'name_ru' : 'name');
            $current['cloud_str'] = _($current['cloud_str']);
            $current['w_rumb_str'] = str_replace('/', '', $current['w_rumb_str']);

            return $this->postParse($current);
        }

        return false;
    }

    private function getCurrentFromSource($city_id, $cache_data){

        if (empty($cache_data)){
            //lock cache
            $cache_id = Mysql::getInstance()->insert($this->cache_table,
                array(
                    'city_id' => $city_id
                )
            )->insert_id();

            if (!$cache_id){
                return false;
            }
        }

        return $this->updateCurrentById($city_id);
    }

    private function updateCurrentById($id){

        $url = 'http://api.openweathermap.org/data/2.5/weather?id='.$id.'&units=metric';

        $content = file_get_contents(
            $url,
            false,
            stream_context_create($this->context_params)
        );

        $content = json_decode($content, true);

        $weather = $this->normalizeWeatherData($content);

        if (!empty($weather)){
            $this->setCurrentCache($weather);
        }

        return $weather;
    }

    private function updateCurrentByGroupIds($ids){

        $chunks = array();
        $idx = 0;

        foreach ($ids as $id){
            if (!isset($chunks[$idx])){
                $chunks[$idx] = $id;
            }else{
                if (strlen($chunks[$idx]) + strlen(','.$id) > 1000){
                    $idx++;
                    $chunks[$idx] = $id;
                }else{
                    $chunks[$idx] .= ','.$id;
                }
            }
        }

        $url = 'http://api.openweathermap.org/data/2.5/group?id='.implode(',', $ids).'&units=metric';

        $content = file_get_contents(
            $url,
            false,
            stream_context_create($this->context_params)
        );

        $content = json_decode($content, true);

        if ($content && !empty($content['list'])){
            foreach ($content['list'] as $weather){
                $weather = $this->normalizeWeatherData($weather);
                if (!empty($weather)){
                    $this->setCurrentCache($weather);
                }
            }
        }
    }

    private function normalizeWeatherData($content){

        if (!$content){
            return false;
        }

        $weather = array();

        if (isset($content['id'])){
            $weather['city_id'] = $content['id'];
        }

        if (isset($content['dt'])){
            $weather['dt'] = $content['dt'];
        }

        if (isset($content['weather'][0]['id']) && isset($this->conditions_map[$content['weather'][0]['id']])){
            $weather['cloud'] = $this->conditions_map[$content['weather'][0]['id']]['cloud'];
        }

        if (!isset($content['cloud']) && isset($content['weather'][0]['icon']) && isset($this->conditions_map[substr($content['weather'][0]['icon'], 0, 2)])){
            $weather['cloud'] = $this->conditions_map[substr($content['weather'][0]['icon'], 0, 2)]['cloud'];
        }

        if (!isset($content['coord']) && isset($content['main']['temp_min']) && isset($content['main']['temp_max'])){
            $weather['t'] = array(
                'min' => round($content['main']['temp_min']),
                'max' => round($content['main']['temp_max'])
            );
        }elseif (isset($content['main']['temp'])){
            $weather['t'] = round($content['main']['temp']);
        }

        if (isset($content['main']['pressure'])){
            $weather['p'] = ceil($content['main']['pressure']/1.3332239); // hPa to mmhg
        }

        if (isset($content['main']['humidity'])){
            $weather['h'] = $content['main']['humidity'];
        }

        if (isset($content['wind']['speed'])){
            $weather['w'] = $weather['wind'] = round($content['wind']['speed']);
        }

        if (isset($content['wind']['deg'])){
            $weather['w_rumb'] = $content['wind']['deg'];
        }

        if (isset($content['sys']['sunrise'])){
            $weather['sunrise'] = $content['sys']['sunrise'];
        }

        if (isset($content['sys']['sunset'])){
            $weather['sunset'] = $content['sys']['sunset'];
        }

        //$weather['t_flik'] = 0; // todo

        return $this->preParse($weather);
    }

    public function updateFullCurrent(){

        $city_ids = Mysql::getInstance()
            ->from($this->cache_table)
            ->where(
                array(
                    'last_request>'    => date("Y-m-d H:i:s", time() - 24*3600),
                    //'updated_current<' => date("Y-m-d H:i:s", time() - 15*60)
                )
            )
            ->get()
            ->all('city_id');

        $this->updateCurrentByGroupIds($city_ids);
    }

    public function getForecast(){

        $city_id = Stb::getInstance()->openweathermap_city_id;

        if ($city_id == 0){
            return array('error' => 'not_configured');
        }

        $cache = Mysql::getInstance()->from($this->cache_table)->where(array('city_id' => $city_id))->get()->first();

        if (empty($cache) || empty($cache['forecast'])){
            $weather = $this->getForecastFromSource($city_id, $cache);

            // refresh cache data
            $cache = Mysql::getInstance()->from($this->cache_table)->where(array('city_id' => $city_id))->get()->first();

        }elseif(empty($cache['forecast'])){
            return array('repeat_time' => 10);
        }else{
            $weather = unserialize(System::base64_decode($cache['forecast']));
        }

        if (time() - strtotime($cache['last_request']) > 600){
            Mysql::getInstance()->update($this->cache_table,
                array(
                     'last_request' => 'NOW()'
                ),
                array('id' => $cache['id'])
            );
        }

        $city  = Mysql::getInstance()->from('all_cities')->where(array('id' => $city_id))->get()->first();

        $weather['city'] = $city[Stb::getInstance()->getStbLanguage() == 'ru' ? 'name_ru' : 'name'];
        $target_timezone = $city['timezone'];

        if (!empty($weather) && is_array($weather)){

            $that = $this;

            $weather['forecast'] = array_map(function($day) use ($that, $target_timezone){

                    $date = new DateTime('@'.$day['dt'], new DateTimeZone('UTC'));
                    $date->setTimeZone(new DateTimeZone($target_timezone));

                    $day['title'] = _($that->getDayPart($date->format('G'))).' '.$date->format('j').' '._($date->format('M')).', '._($date->format('D'));

                    $day['cloud_str']  = _($day['cloud_str']);

                    $day['w_rumb_str'] = str_replace('/', '', $day['w_rumb_str']);

                    $day['temperature'] = (($day['t']['min']) > 0 ? '+' : '').$day['t']['min'].'..'.(($day['t']['max']) > 0 ? '+' : '').$day['t']['max'].'&deg;';

                    return $that->postParse($day);
                },
                $weather['forecast']);

            return $weather;
        }

        return false;
    }

    private function getForecastFromSource($city_id, $cache_data){

        if (empty($cache_data)){
            //lock cache
            $cache_id = Mysql::getInstance()->insert($this->cache_table,
                array(
                     'city_id' => $city_id
                )
            )->insert_id();

            if (!$cache_id){
                return false;
            }
        }

        return $this->updateForecastById($city_id);
    }

    private function updateForecastById($id){

        $url = 'http://api.openweathermap.org/data/2.5/forecast?id='.$id.'&units=metric';

        $content = file_get_contents(
            $url,
            false,
            stream_context_create($this->context_params)
        );

        $content = json_decode($content, true);

        $weather = array();
        $weather['city_id'] = $id;
        $weather['forecast'] = array();

        if ($content && !empty($content['list'])){

            $indexes = array();

            for ($i=0; $i<4; $i++){
                $indexes[] = $i * 2 + 1;
            }

            foreach ($indexes as $idx){
                if (isset($content['list'][$idx])){
                    $weather['forecast'][] = $this->normalizeWeatherData($content['list'][$idx]);
                }
            }
        }

        if (!empty($weather)){
            $this->setForecastCache($weather);
        }

        return $weather;
    }

    public function updateFullForecast(){

        $city_ids = Mysql::getInstance()
            ->from($this->cache_table)
            ->where(
                array(
                  'last_request>'    => date("Y-m-d H:i:s", time() - 24*3600),
                  //'updated_forecast' => date("Y-m-d H:i:s", time() - 15*60)
                )
            )
            ->get()
            ->all('city_id');

        foreach ($city_ids as $city_id){
            $this->updateForecastById($city_id);
        }
    }

    public function postParse($weather){

        if (!empty($weather['dt'])){

            $target_timezone = Mysql::getInstance()->from('all_cities')->where(array('id' => Stb::getInstance()->openweathermap_city_id))->get()->first('timezone');

            $date = new DateTime('@'.$weather['dt'], new DateTimeZone('UTC'));
            $date->setTimeZone(new DateTimeZone($target_timezone));

            $weather['date'] = $date->format('Y-m-d H:i:s');
            $weather['hour'] = $date->format('G');

            $weather['pict'] = $this->getPicture($weather);
        }

        return $weather;
    }

    public function getCities($country_id, $search = ''){

        $result = array();

        if (empty($search)){
            $cities = Mysql::getInstance()->from('all_cities')->where(array('country_id' => $country_id))->orderby('name')->get()->all();

            foreach ($cities as $city){
                $selected = (Stb::getInstance()->openweathermap_city_id == $city['id'])? 1 : 0;
                $result[] = array('label' => $city['name'] , 'value' => $city['id'], 'timezone' => $city['timezone'], 'selected' => $selected);
            }
        }else{
            $cities = Mysql::getInstance()
                ->select('id, name')
                ->from('all_cities')
                ->where(array('country_id' => $country_id))
                ->like(array(
                   'name_ru'    => iconv('windows-1251', 'utf-8', $search).'%',
                   'name' => $search.'%'
                ), 'OR ')
                ->limit(3)
                ->get()
                ->all();

            $result = array();

            foreach ($cities as $city){
                $result[] = array('label' => $city['name'] , 'value' => $city['id']);
            }
        }

        return $result;
    }

    public function getCityFieldName(){
        return 'openweathermap_city_id';
    }

    public function getDayPart($hour){
        if ($hour >= 6 && $hour < 12){
            return 'Morning';
        }elseif ($hour >= 12 && $hour < 18){
            return 'Day';
        }elseif ($hour >= 18 && $hour < 24){
            return 'Evening';
        }elseif ($hour >= 0 && $hour < 6){
            return 'Night';
        }else{
            return '';
        }
    }
}