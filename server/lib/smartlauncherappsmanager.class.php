<?php

//require_once "../common.php"; // todo: remove

use Stalker\Lib\Core\Mysql;
use Stalker\Lib\Core\Config;

class SmartLauncherAppsManager
{
    private $lang;

    public function __construct($lang = null){
        $this->lang = $lang ? $lang : 'en';
    }

    public function getAppInfo($app_id){

        $app = $original_app = Mysql::getInstance()->from('launcher_apps')->where(array('id' => $app_id))->get()->first();

        if (empty($app)){
            return false;
        }

        $npm = new Npm();
        $info = $npm->info($app['url']);

        if (empty($info)){
            throw new SmartLauncherAppsManagerException('Unable to get info for '.$app['url']);
        }

        $app['alias'] = $info['name'];
        $app['name']  = isset($info['config']['name']) ? $info['config']['name'] : '';
        $app['description'] = isset($info['config']['description']) ? $info['config']['description'] : '';
        $app['available_version'] = isset($info['version']) ? $info['version'] : '';
        $app['author'] = isset($info['author']) ? $info['author'] : '';
        $app['type']   = isset($info['config']['type']) ? $info['config']['type'] : null;
        $app['category'] = isset($info['config']['category']) ? $info['config']['category'] : null;
        $app['is_unique'] = isset($info['config']['unique']) && $info['config']['unique'] ? 1 : 0;

        $update_data = array();

        if (!$original_app['alias'] && $app['alias']){
            $update_data['alias'] = $app['alias'];
        }

        if (!$original_app['author'] && $app['author'] || $original_app['author'] != $app['author']){
            $update_data['author'] = $app['author'];
        }

        $update_data['is_unique'] = $app['is_unique'];
        $update_data['type'] = $app['type'];
        $update_data['category'] = $app['category'];

        if (!empty($update_data)){
            Mysql::getInstance()->update('launcher_apps', $update_data, array('id' => $app_id));
        }

        if ($app['current_version']){
            $app['installed'] = is_dir(realpath(PROJECT_PATH.'/../../'
                .Config::getSafe('launcher_apps_path', 'stalker_launcher_apps/')
                .$app['url']
                .'/'.$app['current_version']));
        }else{
            $app['installed'] = false;
        }

        $app['versions'] = array();

        if (isset($info['versions']) && is_array($info['versions'])){

            foreach ($info['versions'] as $ver){
                $version = array(
                    'version'     => $ver,
                    'published'   => isset($info['time'][$ver]) ? strtotime($info['time'][$ver]) : 0,
                    'installed'   => is_dir(realpath(PROJECT_PATH.'/../../'
                        .Config::getSafe('launcher_apps_path', 'stalker_launcher_apps/')
                        .$app['alias']
                        .'/'.$ver)),
                    'current'     => $ver == $app['current_version'],
                );

                $app['versions'][] = $version;
            }

        }

        return $app;

    }

    public function installApp($app_id){

    }

    public function updateApp($app_id, $version = null){

    }

    public function deleteApp($app_id, $version = null){

    }

    private function getAppLocalization($app_id, $version = null){

    }

    public function startAutoUpdate(){

    }
}

class SmartLauncherAppsManagerException extends Exception{}