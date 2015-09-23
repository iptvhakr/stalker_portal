<?php

class Vclubinfo implements \Stalker\Lib\StbApi\vclubinfo {

    private static function getProvider(){

        $class = ucfirst(Config::getSafe('vclub_info_provider', 'kinopoisk'));

        if (!class_exists($class)){
            throw new Exception('Resource "'.$class.'" does not exist');
        }

        return $class;
    }

    public static function getInfoById($id){
        $class_name = self::getProvider();
        return $class_name::getInfoById($id);
    }

    public static function getInfoByName($orig_name){
        $class_name =self::getProvider();
        return $class_name::getInfoByName($orig_name);
    }

    public static function getRatingByName($orig_name){
        $class_name =self::getProvider();
        return $class_name::getRatingByName($orig_name);
    }

    public static function getRatingById($kinopoisk_id){
        $class_name =self::getProvider();
        return $class_name::getRatingById($kinopoisk_id);
    }
}