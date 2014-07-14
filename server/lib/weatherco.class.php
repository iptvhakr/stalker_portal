<?php

class Weatherco extends WeatherProvider
{

    public function __construct(){
        parent::__construct();
    }

    public function updateFullCurrent(){

        $start = microtime(1);

        $content = file_get_contents(
            'http://xml.weather.co.ua/1.2/fullcurrent/',
            false,
            stream_context_create($this->context_params)
        );

        $xml_resp = simplexml_load_string($content);

        if (!$xml_resp){

            echo "Error loading fullcurrent weather\n";
            echo "Time: ".(microtime(1) - $start)."\n";

            foreach(libxml_get_errors() as $error) {
                echo "\t", $error->message;
            }

            //file_put_contents('/var/log/stalkerd/1c_'.date('YmdHis').'.log', $content);
            exit;
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

            $item = $this->preParse($item);

            $this->setCurrentCache($item);
        }
    }

    public function updateFullForecast(){

        $start = microtime(1);

        $content = file_get_contents(
            'http://xml.weather.co.ua/1.2/fullforecast/',
            false,
            stream_context_create($this->context_params)
        );

        $xml_resp = simplexml_load_string($content);

        if (!$xml_resp){

            echo "Error loading fullforecast weather\n";
            echo "Downloaded in: ".(microtime(1) - $start)."\n";
            echo "Time: ".(microtime(1) - $start)."\n";

            foreach(libxml_get_errors() as $error) {
                echo "\t", $error->message;
            }

            //file_put_contents('/var/log/stalkerd/1f_'.date('YmdHis').'.log', $content);
            exit;
        }

        foreach ($xml_resp->forecast as $forecast){

            $weather = array();

            $weather['city_id'] = intval($forecast->attributes()->city);

            $weather['forecast'] = array();

            foreach ($forecast->day as $day){

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

                $weather['forecast'][] = $this->preParse($item);

                $this->setForecastCache($weather);
            }
        }
    }

    public function getCurrent(){

        $city_id = Stb::getInstance()->city_id;

        if ($city_id == 0){
            return array('error' => 'not_configured');
        }

        $cache = Mysql::getInstance()->from($this->cache_table)->where(array('city_id' => $city_id))->get()->first();

        $current = unserialize(System::base64_decode($cache['current']));

        if (!empty($current) && is_array($current)){

            $current['city'] = Mysql::getInstance()->from('cities')->where(array('id' => $current['city_id']))->get()->first(_('city_name_field'));
            $current['cloud_str'] = _($current['cloud_str']);
            //$current['w_rumb_str'] = _($current['w_rumb_str']);
            $current['w_rumb_str'] = str_replace('/', '', $current['w_rumb_str']);

            return $this->postParse($current);
        }

        return false;
    }

    public function getForecast(){

        $city_id = Stb::getInstance()->city_id;

        if ($city_id == 0){
            return array('error' => 'not_configured');
        }

        $cache = Mysql::getInstance()->from($this->cache_table)->where(array('city_id' => $city_id))->get()->first();

        $tod_arr   = array(3 => 'Night', 9 => 'Morning', 15 => 'Day', 21=> 'Evening');

        $weather = unserialize(System::base64_decode($cache['forecast']));

        $weather['city']  = Mysql::getInstance()->from('cities')->where(array('id' => $weather['city_id']))->get()->first(_('city_name_field'));

        if (!empty($weather) && is_array($weather)){

            $that = $this;

            $weather['forecast'] = array_map(function($day) use ($tod_arr, $that){

                $day['title'] = _($tod_arr[$day['hour']]).' '.date("j", $day['timestamp']).' '._(date("M", $day['timestamp'])).', '._(date("D", $day['timestamp']));

                $day['cloud_str']  = _($day['cloud_str']);
                //$day['w_rumb_str'] = _($day['w_rumb_str']);
                $day['w_rumb_str'] = str_replace('/', '', $day['w_rumb_str']);

                $day['temperature'] = (($day['t']['min']) > 0 ? '+' : '').$day['t']['min'].'..'.(($day['t']['max']) > 0 ? '+' : '').$day['t']['max'].'&deg;';

                return $that->postParse($day);
            },
            $weather['forecast']);

            return $weather;
        }

        return false;
    }

    public function postParse($weather){

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

    public function getCities($country_id, $search = ''){

        $result = array();

        if (empty($search)){
            $cities = Mysql::getInstance()->from('cities')->where(array('country_id' => $country_id))->orderby('name_en')->get()->all();

            foreach ($cities as $city){
                $selected = (Stb::getInstance()->city_id == $city['id'])? 1 : 0;
                $city_name = $city['name_en'];
                $result[] = array('label' => $city_name , 'value' => $city['id'], 'timezone' => $city['timezone'], 'selected' => $selected);
            }
        }else{
            $cities = Mysql::getInstance()
            ->select('id, name_en')
            ->from('cities')
            ->where(array('country_id' => $country_id))
            ->like(array(
                'name'    => iconv('windows-1251', 'utf-8', $search).'%',
                'name_en' => $search.'%'
            ), 'OR ')
            ->limit(3)
            ->get()
            ->all();

            $result = array();

            foreach ($cities as $city){
                $result[] = array('label' => $city['name_en'] , 'value' => $city['id']);
            }
        }

        return $result;
    }

    public function getCityFieldName(){
        return 'city_id';
    }

}