<?php

class Module
{
    public static function getServices(){
        $modules = Config::get('disabled_modules');
        sort($modules);

        return array_map(function($module){
            return array('id' => $module, 'name' => $module);
        }, $modules);
    }
}