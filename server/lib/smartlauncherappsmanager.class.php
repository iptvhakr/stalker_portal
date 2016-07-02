<?php

//require_once '../common.php';

use Stalker\Lib\Core\Mysql;
use Stalker\Lib\Core\Config;
use Stalker\Lib\Core\Cache;

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

        $cache = Cache::getInstance();

        $cached_info = $cache->get($app_id.'_launcher_app_info');

        if (empty($cached_info)){
            $npm = new Npm();
            $info = $npm->info($app['url']);
        }else{
            $info = $cached_info;
        }

        if (empty($info)){
            throw new SmartLauncherAppsManagerException('Unable to get info for '.$app['url']);
        }

        if (empty($cached_info)){
            $cache->set($app_id.'_launcher_app_info', $info, 0, rand(1000, 3600));
        }

        $app['alias'] = $info['name'];
        $app['name']  = isset($info['config']['name']) ? $info['config']['name'] : $info['name'];
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

        if (!$original_app['name'] && $app['name']){
            $update_data['name'] = $app['name'];
        }

        if (!$original_app['description'] && $app['description']){
            $update_data['description'] = $app['description'];
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

        if (empty($app['name'])) {
            $update_data['name'] = isset($info['config']['name']) ? $info['config']['name'] : $info['name'];
        }

        if (empty($app['description'])) {
            $update_data['description'] = isset($info['config']['description']) ? $info['config']['description'] : '';
        }

        $update_data['author']    = isset($info['author']) ? $info['author'] : '';
        $update_data['type']      = isset($info['config']['type']) ? $info['config']['type'] : null;
        $update_data['category']  = isset($info['config']['category']) ? $info['config']['category'] : null;
        $update_data['is_unique'] = isset($info['config']['unique']) && $info['config']['unique'] ? 1 : 0;

        if (!empty($info['config'])){
            $update_data['config'] = json_encode($info['config']);
        }

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

        $app = Mysql::getInstance()->from('launcher_apps')->where(array('id' => $app_id))->get()->first();

        if (empty($app)){
            return false;
        }

        $info = file_get_contents(realpath(PROJECT_PATH.'/../../'
            .Config::getSafe('launcher_apps_path', 'stalker_launcher_apps/')
            .$app['alias']
            .'/'.$app['current_version'].'/package.json'));

        if (!$info){
            return false;
        }

        $info = json_decode($info, true);

        if (!$info){
            return false;
        }

        $full_dependencies = array();

        $dependencies = isset($info['dependencies']) ? $info['dependencies'] : array();

        foreach ($dependencies as $package => $version_expression){

            $dep_app = Mysql::getInstance()->from('launcher_apps')->where(array('alias' => $package))->get()->first();

            if (empty($dep_app) || empty($dep_app['current_version'])){
                continue;
            }

            $range = new SemVerExpression($version_expression);

            if ($range->satisfiedBy(new SemVer($dep_app['current_version']))){
                $full_dependencies[$package] = '../'.($dep_app['type'] == 'plugin' ? 'plugins/' : '').$package.'/'.$dep_app['current_version'].'/';
            }else{
                $dep_app_path = realpath(PROJECT_PATH.'/../../'
                    .Config::getSafe('launcher_apps_path', 'stalker_launcher_apps/')
                    .$dep_app['alias']);

                $files = array_diff(scandir($dep_app_path), array('.','..'));

                $max_version = null;

                foreach ($files as $file){
                    if (is_dir($dep_app_path.'/'.$file)){

                        $semver = new SemVer($file);

                        if ($range->satisfiedBy($semver)){
                            if (is_null($max_version)){
                                $max_version = $semver->getVersion();
                            } else if (SemVer::gt($semver->getVersion(), $max_version)){
                                $max_version = $semver->getVersion();
                            }
                        };
                    }
                }

                if (is_null($max_version)){
                    throw new SmartLauncherAppsManagerException('Unresolved dependency '.$dep_app['alias'].' for '.$app['alias']);
                }
            }

        }

        return $full_dependencies;
    }

    public function getConflicts($app_id, $version = null) {

        $app = $original_app = Mysql::getInstance()->from('launcher_apps')->where(array('id' => $app_id))->get()->first();

        if (empty($app)) {
            throw new SmartLauncherAppsManagerException('App not found, id=' . $app_id);
        }

        $npm = new Npm();
        $info = $npm->info($app['url'], $version);

        if (empty($info)){
            throw new SmartLauncherAppsManagerException('Unable to get info for '.$app['url']);
        }

        $dependencies = isset($info['dependencies']) ? $info['dependencies'] : array();

        $conflicts = array();

        foreach ($dependencies as $package => $version_expression) {

            $dep_app = Mysql::getInstance()->from('launcher_apps')->where(array('alias' => $package))->get()->first();

            if (empty($dep_app) || empty($dep_app['current_version']) || !$dep_app['unique']) {
                continue;
            }

            $range = new SemVerExpression($version_expression);

            if (!$range->satisfiedBy(new SemVer($dep_app['current_version']))){
                $conflicts[] = array(
                    'alias'           => $package,
                    'current_version' => $dep_app['current_version']
                );
            }
        }

        return $conflicts;
    }

    public function getInstalledApps($type = 'app'){

        $apps = Mysql::getInstance()->from('launcher_apps')->where(array('type' => $type))->get()->all();

        return array_values(array_filter($apps, function($app){
            return !empty($app['alias']) && $app['status'] == 1 && is_dir(realpath(PROJECT_PATH.'/../../'
                .Config::getSafe('launcher_apps_path', 'stalker_launcher_apps/')
                .$app['alias']
                .'/'.$app['current_version']));
        }));
    }

    public function getSystemApps(){

        $apps = Mysql::getInstance()->from('launcher_apps')->where(array('type!=' => 'app'))->get()->all();

        return array_values(array_filter($apps, function($app){
            return !empty($app['alias']) && $app['status'] == 1 && is_dir(realpath(PROJECT_PATH.'/../../'
                .Config::getSafe('launcher_apps_path', 'stalker_launcher_apps/')
                .$app['alias']
                .'/'.$app['current_version']));
        }));
    }

    private static function delTree($dir) {
        $files = array_diff(scandir($dir), array('.','..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? self::delTree("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }
}

class SmartLauncherApp
{
    public function __construct() {

    }

    public function getName(){

    }

    public function getDescription(){

    }
}

class SmartLauncherAppsManagerException extends Exception{}

/*$manager = new SmartLauncherAppsManager();
$manager->installApp(3);
$manager->installApp(7);
$manager->installApp(8);
$manager->installApp(9);
$manager->installApp(10);*/