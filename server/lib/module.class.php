<?php

class Module
{
    public static function getServices(){

        $apps = new AppsManager();
        $external_apps = $apps->getList();
        $installed_apps = array_values(array_filter($external_apps, function($app){
            return $app['installed'];
        }));

        $external_apps_list = array_map(function($app){
            return $app['alias'];
        }, $installed_apps);

        $modules = Config::getSafe('disabled_modules', array());
        $modules = array_merge($modules, $external_apps_list);
        sort($modules);

        $idx = array_search('ivi', $modules);

        if ($idx !== false){
            array_splice($modules, $idx, 1);
        }

        return array_map(function($module){
            return array('id' => $module, 'name' => $module);
        }, $modules);
    }
}