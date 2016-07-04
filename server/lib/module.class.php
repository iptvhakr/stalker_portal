<?php

use Stalker\Lib\Core\Config;

class Module
{
    public static function getServices(){

        $apps = new AppsManager();
        $external_apps = $apps->getList(true);

        $installed_apps = array_values(array_filter($external_apps, function($app){
            return $app['installed'] == 1 && $app['status'] == 1 && !empty($app['alias']);
        }));

        $external_apps_list = array_map(function($app){
            return array(
                'id'   => 'external_'.$app['alias'],
                'name' => $app['alias'],
                'external' => 1,
            );
        }, $installed_apps);

        $launcher_apps_manager = new SmartLauncherAppsManager();
        $launcher_apps = $launcher_apps_manager->getInstalledApps();

        $launcher_apps_list = array_map(function($app){
            return array(
                'id'   => 'launcher_'.$app['alias'],
                'name' => $app['alias'],
                'launcher' => 1,
            );
        }, $launcher_apps);

        $modules = Config::getSafe('disabled_modules', array());

        sort($modules);

        $idx = array_search('ivi', $modules);

        if ($idx !== false){
            array_splice($modules, $idx, 1);
        }

        $modules = array_map(function($module){
            return array('id' => $module, 'name' => $module);
        }, $modules);

        $modules = array_merge($modules, $external_apps_list, $launcher_apps_list);

        return $modules;
    }
}