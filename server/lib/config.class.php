<?php

class Config
{
    private static $settings = array();

    private static function init(){

        $config_file = PROJECT_PATH.'/config.ini';

        if (!is_readable($config_file)){
            throw new ConfigException('Cannot read config file');
        }

        self::$settings = parse_ini_file($config_file);

        $custom_config = PROJECT_PATH.'/custom.ini';

        if (is_readable($custom_config)){
            $custom = parse_ini_file(PROJECT_PATH.'/custom.ini');

            if ($custom !== false){
                self::$settings = array_merge(self::$settings, $custom);
            }
        }
    }

    public static function get($key){

        if (empty(self::$settings)){
            self::init();
        }

        if (!array_key_exists($key, self::$settings)){
            throw new ConfigException('Key "'.$key.'" not found in settings');
        }

        return self::$settings[$key];
    }

    public static function getSafe($key, $default){
        
        try{
            $value = self::get($key);
        }catch (ConfigException $e){
            $value = $default;
        }

        return $value;
    }

    public static function exist($key){

        if (empty(self::$settings)){
            self::init();
        }

        if (!array_key_exists($key, self::$settings)){
            return false;
        }

        return true;
    }
}

class ConfigException extends Exception {}

?>