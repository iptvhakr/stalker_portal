<?php

use Stalker\Lib\Core\Mysql;

class L10n {

    public static $api_key = 'ABQIAAAA8gol0t00IMl-GLDtPLoQnRT2RNzrSW75x_tEA63PvQHiSnPv7BQnFyZpHgybA9POm2hOwqHdf4JatA';
    public static $geonames_username = 'azhurb';

    public static function updateCitiesInfo(){

        return self::updateAllAvailableCountries();
    }

    private static function updateAllAvailableCountries($force = false){

        if ($force){
            Mysql::getInstance()->query('truncate table countries');
            Mysql::getInstance()->query('truncate table cities');
        }

        $xml_resp = simplexml_load_file('http://xml.weather.ua/1.2/country/');

        if (!$xml_resp){
            throw new ErrorException("Couldn't load country xml");
        }

        foreach ($xml_resp->country as $country){
            $item = array();

            $item['id'] = intval($country->attributes()->id);

            $db_country = Mysql::getInstance()->from('countries')->where(array('id' => $item['id']))->get()->first();

            foreach ($country as $field => $val){

                if ($field == 'region'){
                    $item['region'] = strval($val);
                    $item['region_id'] = intval($val->attributes()->id);
                }else{
                    $item[$field] = strval($val);
                }
            }

            if (empty($db_country['id'])){
                Mysql::getInstance()->insert('countries', $item);
            }else{
                Mysql::getInstance()->update('countries', $item, array('id' => $db_country['id']));
            }

            self::updateAllCitiesByCountryId($item['id']);
        }
    }

    private static function updateAllCitiesByCountryId($country_id){

        if (empty($country_id)){
            return;
        }

        $xml_resp = simplexml_load_file('http://xml.weather.ua/1.2/city/?country='.$country_id);

        if (!$xml_resp){
            throw new ErrorException("Couldn't load city xml for country ".$country_id);
        }

        $delay = 100000;

        foreach ($xml_resp->city as $city){

            $geocode_pending = true;

            while($geocode_pending){

                $item = array();

                $item['id'] = intval($city->attributes()->id);

                $db_city = Mysql::getInstance()->from('cities')->where(array('id' => $item['id']))->get()->first();

                foreach ($city as $field => $val){
                    $item[$field] = strval($val);
                }

                $item['country'] = Mysql::getInstance()->from('countries')->where(array('id' => $item['country_id']))->get()->first('name_en');

                $geocode_pending = false;

                if (empty($db_city['timezone'])){

                    try{
                        $item['timezone'] = self::getTimezoneForCity($item['country'], $item['name_en']);
                    }catch(GeoCodeException $ge){
                        echo "Bad status for country: ".$item['country'].", city: ".$item['name_en']."; Status: ".$ge->getMessage().";\n";

                        if ($ge->getMessage() == 'OVER_QUERY_LIMIT'){
                            $delay += 100000;

                            echo "Increasing the delay to ".$delay." microseconds\n";

                            $geocode_pending = true;
                        }

                    }catch(GeoNamesException $gn){
                        echo "Bad status for country: ".$item['country'].", city: ".$item['name_en']."; Status: ".$gn->getMessage().";\n";

                        if ($gn->getCode() >= 18 && $gn->getCode() <= 20){

                            if (self::$geonames_username != 'demo'){

                                self::$geonames_username = 'demo';
                                
                                $geocode_pending = true;
                            }else{
                                throw new ErrorException("GeoNames credits exceeded");
                            }
                        }
                        
                    }catch(Exception $e){
                        echo $e;
                    }
                }


                if (!$geocode_pending){
                    if (empty($db_city['id'])){
                        Mysql::getInstance()->insert('cities', $item);
                    }else{
                        Mysql::getInstance()->update('cities', $item, array('id' => $db_city['id']));
                    }
                }

                usleep($delay);
            }
        }
    }

    private static function getTimezoneForCity($country, $city){

        //$city = urlencode(str_replace('-', ' ', $city));
        //$country = urlencode($country);
        $search = urlencode($country.' '.$city);

        $url = 'http://maps.google.com/maps/api/geocode/json?address='.$search.'&sensor=false&key='.self::$api_key;

        $result = file_get_contents($url);

        if (!$result){
            throw new ErrorException("Couldn't load geocode");
        }

        $result = json_decode($result, true);

        var_dump($url);

        if ($result['status'] != 'OK'){
            throw new GeoCodeException($result['status']);
        }

        $lat = $result['results'][0]['geometry']['location']['lat'];
        $lng = $result['results'][0]['geometry']['location']['lng'];

        /*$url = 'http://nominatim.openstreetmap.org/search/?format=json&q='.$city.' '.$country;
        
        $result = file_get_contents($url);

        if (!$result){
            throw new ErrorException("Couldn't load geocode");
        }

        $result = json_decode($result, true);

        var_dump($url);

        if (empty($result)){
            throw new ErrorException("Empty result for ".$country.", ".$city);
        }

        $lat = $result[0]['lat'];
        $lng = $result[0]['lon'];*/

        if (empty($lat) || empty($lng)){
            throw new ErrorException("Couldn't get location for ".$country.", ".$city);
        }

        $timezone_api_url = 'http://api.geonames.org/timezoneJSON?formatted=true&lat='.$lat.'&lng='.$lng.'&username='.self::$geonames_username.'&style=full';

        $timezone_api = file_get_contents($timezone_api_url);

        if (!$timezone_api){
            throw new ErrorException("Couldn't load timezone api");
        }

        $timezone_api = json_decode($timezone_api, true);

        if (!empty($timezone_api['status'])){
            throw new GeoNamesException($timezone_api['status']['message'], $timezone_api['status']['value']);
        }

        if (empty($timezone_api['timezoneId'])){
            throw new ErrorException("timezoneId empty, url: ".$timezone_api_url);
        }

        $timezone_id = $timezone_api['timezoneId'];

        echo "Country: ".$timezone_id."; City: ".$city."; Timezone: ".$timezone_id.";\n";

        return $timezone_id;
    }
}

class GeoCodeException extends Exception{};

class GeoNamesException extends Exception{};
