<?php

class Registry{

    protected static $data = array();

    public static function get($key, $default = null){
        return isset(self::$data[$key]) ? self::$data[$key] : $default;
    }

    public static function set($key, $value){
        self::$data[$key] = $value;
    }
}

?>