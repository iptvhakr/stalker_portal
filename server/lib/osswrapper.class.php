<?php

use Stalker\Lib\Core\Config;

class OssWrapper
{
    private static $instance = null;

    private function __construct(){}

    /**
     * @return OssWrapperInterface
     */
    public static function getWrapper(){

        if (self::$instance !== null){
            return self::$instance;
        }

        $wrapper_class = Config::get('oss_wrapper');
        self::$instance = new $wrapper_class;

        return self::$instance;
    }
}