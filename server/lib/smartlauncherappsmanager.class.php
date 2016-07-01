<?php

use Stalker\Lib\Core\Mysql;
use Stalker\Lib\Core\Config;

class SmartLauncherAppsManager
{
    private $lang;

    public function __construct($lang = null){
        $this->lang = $lang ? $lang : 'en';
    }

    /**
     * @param int $app_id
     * @return mixed
     * @throws SmartLauncherAppsManagerException
     */
    public function getAppInfo($app_id){

        $app = $original_app = Mysql::getInstance()->from('launcher_apps')->where(array('id' => $app_id))->get()->first();

        if (empty($app)){
            throw new SmartLauncherAppsManagerException('App not found, id='.$app_id);
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

        if ($app['localization'] && ($localization = json_decode($app['localization'], true))){
            if (!empty($localization[$this->lang]['name'])){
                $app['name'] = $localization[$this->lang]['name'];
            }

            if (!empty($localization[$this->lang]['description'])){
                $app['description'] = $localization[$this->lang]['description'];
            }
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

    /**
     * @param int $app_id
     * @param string $version
     * @return bool
     * @throws SmartLauncherAppsManagerException
     */
    public function installApp($app_id, $version = null){

        $app = $original_app = Mysql::getInstance()->from('launcher_apps')->where(array('id' => $app_id))->get()->first();

        if (empty($app)){
            throw new SmartLauncherAppsManagerException('App not found, id='.$app_id);
        }

        $npm = new Npm();
        $info = $npm->info($app['url'], $version);

        if (empty($info)){
            throw new SmartLauncherAppsManagerException('Unable to get info for '.$app['url']);
        }

        if ($app['current_version'] == $info['version']){
            throw new SmartLauncherAppsManagerException('Nothing to install');
        }

        $result = $npm->install($app['url'], $version);

        if (empty($result)){
            throw new SmartLauncherAppsManagerException('Unable to install application');
        }

        $update_data = array('current_version' => $info['version']);

        if (empty($app['alias'])) {
            $update_data['alias'] = $info['name'];
        }

        $update_data['author']    = isset($info['author']) ? $info['author'] : '';
        $update_data['type']      = isset($info['config']['type']) ? $info['config']['type'] : null;
        $update_data['category']  = isset($info['config']['category']) ? $info['config']['category'] : null;
        $update_data['is_unique'] = isset($info['config']['unique']) && $info['config']['unique'] ? 1 : 0;

        if ($version){
            $update_data['updated'] = 'NOW()';
        }

        $localization = $this->getAppLocalization($app_id, $info['version']);

        if (!empty($localization)){
            $update_data['localization'] = json_encode($localization);
        }

        Mysql::getInstance()->update('launcher_apps',
            $update_data,
            array('id' => $app_id)
        );

        return $result;
    }

    /**
     * @param int $app_id
     * @param null $version
     * @return bool
     * @throws SmartLauncherAppsManagerException
     */
    public function updateApp($app_id, $version = null){
        return $this->installApp($app_id, $version);
    }

    /**
     * @param int $app_id
     * @param string $version
     * @return bool
     * @throws SmartLauncherAppsManagerException
     */
    public function deleteApp($app_id, $version = null){

        $app = Mysql::getInstance()->from('launcher_apps')->where(array('id' => $app_id))->get()->first();

        if (empty($app['alias']) || empty($app['current_version'])){
            throw new SmartLauncherAppsManagerException('Nothing to delete');
        }

        if ($version === null){
            $version = $app['current_version'];
        }

        $path = realpath(PROJECT_PATH.'/../../'.Config::getSafe('launcher_apps_path', 'stalker_launcher_apps/').$app['alias'].'/'.$version);

        if (is_dir($path)){
            self::delTree($path);
        }

        if ($version == $app['current_version']){
            Mysql::getInstance()->update('launcher_apps', array('current_version' => ''), array('id' => $app_id));
        }

        return true;
    }

    /**
     * @param int $app_id
     * @param string $version
     * @return array|bool
     */
    private function getAppLocalization($app_id, $version = null){

        $app = Mysql::getInstance()->from('launcher_apps')->where(array('id' => $app_id))->get()->first();

        if (!$version){
            $version = $app['current_version'];
        }

        if (empty($app) || empty($app['alias'])){
            return false;
        }

        $path = PROJECT_PATH.'/../../'.Config::getSafe('launcher_apps_path', 'stalker_launcher_apps/').$app['alias'].'/'.$version.'/';

        $app_localizations = array();

        if (!is_dir($path.'/app/lang/')){
            return false;
        }

        $scanned_directory = array_diff(scandir($path.'/app/lang/'), array('..', '.'));

        $languages = array_map(function($file){
            return str_replace('.json', '', $file);
        }, $scanned_directory);

        $languages = array_merge(array($this->lang, 'en'), array_diff($languages, array($this->lang, 'en')));

        foreach ($languages as $lang){
            if (is_readable($path.'/app/lang/'.$lang.'.json')){
                $localization = json_decode(file_get_contents($path.'/app/lang/'.$lang.'.json'), true);
                if (!empty($localization['data'][''])){
                    $localization = $localization['data'][''];

                    if (!empty($localization[$app['name']])){
                        $app_localizations[$lang]['name'] = $localization[$app['name']];
                    }

                    if (!empty($localization[$app['description']])){
                        $app_localizations[$lang]['description'] = $localization[$app['description']];
                    }
                }
            }
        }

        return $app_localizations;
    }

    public function addApplication($url){

        $app = Mysql::getInstance()->from('launcher_apps')->where(array('url' => $url))->get()->first();

        if (!empty($app)){
            return false;
        }

        $app_id = Mysql::getInstance()->insert('launcher_apps', array(
            'url' => $url
        ))->insert_id();

        $this->getAppInfo($app_id);

        return $app_id;
    }

    public function startAutoUpdate(){

        $need_to_update = Mysql::getInstance()->from('launcher_apps')->where(array('status' => 1, 'autoupdate' => 1))->get()->all();

        foreach ($need_to_update as $app){
            $this->updateApp($app['id']);
        }
    }

    public function getFullAppDependencies($app_id){

    }

    private static function delTree($dir) {
        $files = array_diff(scandir($dir), array('.','..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? self::delTree("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }
}

class SmartLauncherAppsManagerException extends Exception{}