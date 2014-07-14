<?php

class Weather implements \Stalker\Lib\StbApi\Weather
{
    protected $provider;

    public function __construct(){
        $this->provider = $this->getProvider();
    }

    /**
     * @return WeatherProvider
     * @throws Exception
     */
    private function getProvider(){

        $class = ucfirst(Config::getSafe('weather_provider', 'weatherco'));

        if (!class_exists($class)){
            throw new Exception('Resource "'.$class.'" does not exist');
        }

        return new $class;
    }

    public function getCurrent(){
        return $this->provider->getCurrent();
    }

    public function getForecast(){
        return $this->provider->getForecast();
    }

    public function updateFullCurrent(){
        return $this->provider->updateFullCurrent();
    }

    public function updateFullForecast(){
        return $this->provider->updateFullForecast();
    }

    public function getCities($country, $search = ''){
        return $this->provider->getCities($country, $search);
    }

    public function getCityFieldName(){
        return $this->provider->getCityFieldName();
    }
}