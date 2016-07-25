<?php

use Stalker\Lib\Core\Mysql;
use Stalker\Lib\Core\Config;
use Stalker\Lib\Core\Cache;

class SmartLauncherAppsManager
{
    private $lang;
    private $callback;

    public function __construct($lang = null){
        $this->lang = $lang ? $lang : 'en';
    }

    public function setNotificationCallback($callback){
        if (!is_callable($callback)){
            throw new SmartLauncherAppsManagerException('Not valid callback');
        }
        $this->callback = $callback;
    }

    public function sendToCallback($msg){
        if (is_null($this->callback)){
            return;
        }

        call_user_func($this->callback, $msg);
    }

    public static function getLauncherUrl(){

        $core = Mysql::getInstance()->from('launcher_apps')->where(array('type' => 'core'))->get()->first();

        if (empty($core)){
            return false;
        }

        $url = 'http'.(((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) ? 's' : '')
        .'://'.$_SERVER['HTTP_HOST']
        .'/'.Config::getSafe('launcher_apps_path', 'stalker_launcher_apps/')
        .$core['alias']
        .'/'.$core['current_version'].'/app/';

        return $url;
    }

    public static function getLauncherProfileUrl(){

        return 'http'.(((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) ? 's' : '')
            .'://'.$_SERVER['HTTP_HOST']
            .'/'.Config::getSafe('portal_url', '/stalker_portal/')
            .'/server/api/launcher_profile.php';
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
            $npm = Npm::getInstance();
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

        $app['type']   = isset($info['config']['type']) ? $info['config']['type'] : null;
        $app['alias'] = $info['name'];
        $app['name']  = $app['type'] == 'app' && isset($info['config']['name']) ? $info['config']['name'] : $info['name'];
        $app['description'] = isset($info['config']['description']) ? $info['config']['description'] : (isset($info['description']) ? $info['description'] : '');
        $app['available_version'] = isset($info['version']) ? $info['version'] : '';
        $app['author'] = isset($info['author']) ? $info['author'] : '';
        $app['category'] = isset($info['config']['category']) ? $info['config']['category'] : null;
        $app['is_unique'] = isset($info['config']['unique']) && $info['config']['unique'] ? 1 : 0;

        $option_values = json_decode($app['options'], true);

        if (empty($option_values)){
            $option_values = array();
        }

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

        unset($app['options']);

        $app['icon'] = '';
        $app['icon_big'] = '';
        $app['backgroundColor'] = '';

        if ($app['current_version']){

            $app_path = realpath(PROJECT_PATH.'/../../'
                .Config::getSafe('launcher_apps_path', 'stalker_launcher_apps/')
                .($app['type'] == 'plugin' ? 'plugins/' : '')
                .$app['url']
                .'/'.$app['current_version']);

            $app['installed'] = $app_path && is_dir($app_path);

            if ($app['installed'] && isset($info['config']['icons']['paths']['720']) && isset($info['config']['icons']['states']['normal'])){
                $icon_path = realpath($app_path.'/app/'.$info['config']['icons']['paths']['720'].$info['config']['icons']['states']['normal']);
                $app['icon'] = $icon_path && is_readable($icon_path) ?
                        'http'.(((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) ? 's' : '')
                        .'://'.$_SERVER['HTTP_HOST']
                        .'/'.Config::getSafe('launcher_apps_path', 'stalker_launcher_apps/')
                        .$app['alias']
                        .'/'.$app['current_version'].'/app/'
                        .$info['config']['icons']['paths']['720'].$info['config']['icons']['states']['normal']
                    : '';

                $icon_big_path = realpath($app_path.'/app/'.$info['config']['icons']['paths']['1080'].$info['config']['icons']['states']['normal']);

                $app['icon_big'] = $icon_big_path && is_readable($icon_big_path) ?
                    'http'.(((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) ? 's' : '')
                    .'://'.$_SERVER['HTTP_HOST']
                    .'/'.Config::getSafe('launcher_apps_path', 'stalker_launcher_apps/')
                    .$app['alias']
                    .'/'.$app['current_version'].'/app/'
                    .$info['config']['icons']['paths']['1080'].$info['config']['icons']['states']['normal']
                    : '';

                if ($app['icon'] || $app['icon_big']){
                    $app['backgroundColor'] = isset($info['config']['backgroundColor']) ? $info['config']['backgroundColor'] : '';
                }
            }
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

        if (isset($info['versions']) && is_string($info['versions'])){
            $info['versions'] = array($info['versions']);
        }

        if (isset($info['versions']) && is_array($info['versions'])){

            $npm = Npm::getInstance();
            $cache = Cache::getInstance();

            unset($info['time']['modified']);
            unset($info['time']['created']);

            foreach ($info['time'] as $ver => $time){

                $version = array(
                    'version'     => $ver,
                    'published'   => strtotime($time),
                    'installed'   => is_dir(realpath(PROJECT_PATH.'/../../'
                        .Config::getSafe('launcher_apps_path', 'stalker_launcher_apps/')
                        .($app['type'] == 'plugin' ? 'plugins/' : '')
                        .$app['alias']
                        .'/'.$ver)),
                    'current'     => $ver == $app['current_version'],
                );

                $cached_info = $cache->get($app_id.'_'.$ver.'_launcher_app_info');

                if (empty($cached_info)){
                    $info = $npm->info($app['url'], $ver);
                }else{
                    $info = $cached_info;
                }

                if (empty($cached_info)){
                    $cache->set($app_id.'_'.$ver.'_launcher_app_info', $info, 0, rand(3600, 36000));
                }

                $option_list = isset($info['config']['options']) ? $info['config']['options'] : array();

                $option_list = array_map(function($option) use ($option_values){

                    if (isset($option_values[$option['name']])){
                        $option['value'] = $option_values[$option['name']];
                    }elseif (!isset($option['value'])){
                        $option['value'] = null;
                    }

                    if (isset($option['info'])){
                        $option['desc'] = $option['info'];
                    }

                    return $option;
                }, $option_list);

                $version['options'] = $option_list;

                $app['versions'][] = $version;
            }
        }

        return $app;
    }

    /**
     * @param int $app_id
     * @param string $version
     * @param bool $skip_info_check
     * @return bool
     * @throws SmartLauncherAppsManagerException
     */
    public function installApp($app_id, $version = null, $skip_info_check = false){

        $app = $original_app = Mysql::getInstance()->from('launcher_apps')->where(array('id' => $app_id))->get()->first();

        if (empty($app)){
            throw new SmartLauncherAppsManagerException('App not found, id='.$app_id);
        }

        $npm = Npm::getInstance();

        if (!$skip_info_check) {

            $info = $npm->info($app['url'], $version);

            if (empty($info)) {
                throw new SmartLauncherAppsManagerException('Unable to get info for ' . $app['url']);
            }

            if ($app['current_version'] == $info['version']) {
                throw new SmartLauncherAppsManagerException('Nothing to install');
            }

            $conflicts = $this->getConflicts($app_id, $info['version']);

            if (!empty($conflicts)) {
                throw new SmartLauncherAppsManagerConflictException('Conflicts detected', $conflicts);
            }
        }

        $this->sendToCallback("Installing ".$app['url']."...");

        $result = $npm->install($app['url'], $version);

        if (empty($result)){
            throw new SmartLauncherAppsManagerException('Unable to install application');
        }

        $update_data = array('current_version' => isset($info['version']) ? $info['version'] : '');
        $update_data['type'] = isset($info['config']['type']) ? $info['config']['type'] : null;

        if (empty($app['alias'])) {
            $update_data['alias'] = !empty($info['name']) ? $info['name'] : $app['url'];
        }

        if (empty($app['name'])) {
            $update_data['name'] = $update_data['type'] == 'app' && isset($info['config']['name']) ? $info['config']['name'] : (!empty($info['name']) ? $info['name'] : $app['url']);
        }

        if (empty($app['description'])) {
            $update_data['description'] = isset($info['config']['description']) ? $info['config']['description'] : (isset($info['description']) ? $info['description'] : '');
        }

        $update_data['author']    = isset($info['author']) ? $info['author'] : '';
        $update_data['category']  = isset($info['config']['category']) ? $info['config']['category'] : null;
        $update_data['is_unique'] = isset($info['config']['unique']) && $info['config']['unique'] ? 1 : 0;
        $update_data['status'] = 1;

        if (!empty($info['config'])){
            $update_data['config'] = json_encode($info['config']);
        }

        if ($version){
            $update_data['updated'] = 'NOW()';
        }

        Mysql::getInstance()->update('launcher_apps',
            $update_data,
            array('id' => $app_id)
        );

        $localization = $this->getAppLocalization($app_id, isset($info['version']) ? $info['version'] : null);

        if (!empty($localization)){

            Mysql::getInstance()->update('launcher_apps',
                array('localization' => json_encode($localization)),
                array('id' => $app_id)
            );
        }

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
            $version = '';
        }

        $path = realpath(PROJECT_PATH.'/../../'
            .Config::getSafe('launcher_apps_path', 'stalker_launcher_apps/')
            .($app['type'] == 'plugin' ? 'plugins/' : '')
            .$app['alias']
            .'/'.$version
        );

        if (is_dir($path)){
            self::delTree($path);
        }

        if ($version && $version == $app['current_version']){
            Mysql::getInstance()->update('launcher_apps', array('current_version' => ''), array('id' => $app_id));
        }elseif (!$version){
            Mysql::getInstance()->delete('launcher_apps', array('id' => $app_id));
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

    public function addApplication($url, $autoinstall = false, $skip_info_check = false){

        $app = Mysql::getInstance()->from('launcher_apps')->where(array('url' => $url))->get()->first();

        if (!empty($app)){
            return false;
        }

        $app_id = Mysql::getInstance()->insert('launcher_apps', array(
            'url'   => $url,
            'added' => 'NOW()'
        ))->insert_id();

        if ($autoinstall){
            $this->installApp($app_id, null, $skip_info_check);
        }else{
            $this->getAppInfo($app_id);
        }

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
            .($app['type'] == 'plugin' ? 'plugins/' : '')
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
                //$full_dependencies[$package] = '../../../'.($dep_app['type'] == 'plugin' ? 'plugins/' : '').$package.'/'.$dep_app['current_version'].'/';
                $full_dependencies[$package] = $dep_app['current_version'];
            }else{
                $dep_app_path = realpath(PROJECT_PATH.'/../../'
                    .Config::getSafe('launcher_apps_path', 'stalker_launcher_apps/')
                    .($app['type'] == 'plugin' ? 'plugins/' : '')
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

                $full_dependencies[$package] = $max_version;
            }

        }

        return $full_dependencies;
    }

    public function getConflicts($app_id, $version = null) {

        $app = $original_app = Mysql::getInstance()->from('launcher_apps')->where(array('id' => $app_id))->get()->first();

        if (empty($app)) {
            throw new SmartLauncherAppsManagerException('App not found, id=' . $app_id);
        }

        $npm = Npm::getInstance();
        $info = $npm->info($app['url'], $version);

        if (empty($info)){
            throw new SmartLauncherAppsManagerException('Unable to get info for '.$app['url']);
        }

        $dependencies = isset($info['dependencies']) ? $info['dependencies'] : array();

        $conflicts = array();

        foreach ($dependencies as $package => $version_expression) {

            $dep_app = Mysql::getInstance()->from('launcher_apps')->where(array('alias' => $package))->get()->first();

            $range = new SemVerExpression($version_expression);

            if ($package == 'mag-app-stalker-api'){

                $sap_path = realpath(PROJECT_PATH.'/../deploy/src/sap/');
                $sap_versions = array_diff(scandir($sap_path), array('.','..'));

                $dep_info = $npm->info($package);

                if (isset($dep_info['config']['apiVersion']) && array_search($dep_info['config']['apiVersion'], $sap_versions) !== false){
                    $version_expression = $dep_info['config']['apiVersion'];
                    $dep_range = new SemVerExpression($version_expression);
                }else{
                    $dep_range = $range;
                }

                $suitable_sap = null;

                foreach ($sap_versions as $sap_version) {
                    if ($dep_range->satisfiedBy(new SemVer($sap_version))){
                        $suitable_sap = $sap_version;
                        break;
                    }
                }

                if (empty($suitable_sap)){
                    $conflicts[] = array(
                        'alias'           => $package,
                        'current_version' => $version_expression,
                        'target'          => $app['url']
                    );
                }
            }

            if (empty($dep_app) || empty($dep_app['current_version']) || !$dep_app['is_unique']) {
                continue;
            }

            if (!$range->satisfiedBy(new SemVer($dep_app['current_version']))){
                $conflicts[] = array(
                    'alias'           => $package,
                    'current_version' => $dep_app['current_version'],
                    'target'          => $app['url']
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
                .($app['type'] == 'plugin' ? 'plugins/' : '')
                .$app['alias']
                .'/'.$app['current_version']));
        }));
    }

    public function getSystemApps(){

        $apps = Mysql::getInstance()->from('launcher_apps')->not_in('type', array('app', 'theme'))->get()->all();

        return array_values(array_filter($apps, function($app){
            return !empty($app['alias']) && $app['status'] == 1 && is_dir(realpath(PROJECT_PATH.'/../../'
                .Config::getSafe('launcher_apps_path', 'stalker_launcher_apps/')
                .($app['type'] == 'plugin' ? 'plugins/' : '')
                .$app['alias']
                .'/'.$app['current_version']));
        }));
    }

    private static function delTree($dir) {
        if (!is_dir($dir)){
            return false;
        }
        $files = array_diff(scandir($dir), array('.','..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? self::delTree("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }

    public function syncApps(){

        $repos = Config::getSafe('launcher_apps_repos', array());

        foreach ($repos as $repo){
            $info = file_get_contents($repo);

            if (!$info){
                continue;
            }

            $info = json_decode($info, true);

            if (!$info){
                continue;
            }

            $apps = isset($info['dependencies']) ? $info['dependencies'] : array();

            if (is_string($apps)){
                $apps = array($apps);
            }

            foreach ($apps as $app => $ver){
                $this->addApplication($app);
            }
        }
    }

    public function initApps(){
        $apps = Mysql::getInstance()->from('launcher_apps')->count()->get()->counter();

        if ($apps == 0){
            return $this->resetApps();
        }

        return false;
    }

    public function resetApps($metapackage = null){

        $orig_metapackage = $metapackage;

        if (is_null($metapackage)){
            $metapackage = Config::getSafe('launcher_apps_metapackage', 'mag-apps-base');
        }

        if (empty($metapackage)){
            return false;
        }

        $npm = Npm::getInstance();

        if (is_null($orig_metapackage)) {

            $info = $npm->info($metapackage);

            if (!$info) {
                return false;
            }
        }

        $cache = Cache::getInstance();

        $apps_id = Mysql::getInstance()->from('launcher_apps')->get()->all('id');
        foreach ($apps_id as $app_id){
            $cache->del($app_id.'_launcher_app_info');
        }

        $this->sendToCallback("Removing apps...");

        Mysql::getInstance()->truncate('launcher_apps');

        $apps_path = realpath(PROJECT_PATH.'/../../'.Config::getSafe('launcher_apps_path', 'stalker_launcher_apps/'));

        if ($apps_path){
            $ignore = array('.','..');
            if ($orig_metapackage){
                $ignore[] = $orig_metapackage;
            }
            $files = array_diff(scandir($apps_path), $ignore);
            foreach ($files as $file){
                self::delTree($apps_path.'/'.$file);
            }
        }

        $this->sendToCallback("Installing metapackage ".$metapackage."...");

        $result = $this->addApplication($metapackage, true, !is_null($orig_metapackage));

        Mysql::getInstance()->delete('launcher_apps', array('url' => $metapackage));

        $this->syncApps();

        return boolval($result);
    }

    /**
     * @return string package.json content
     */
    public function getSnapshot(){

        $snapshot = array(
            'name' => 'mag-apps-snapshot',
            'version' => '0.0.1',
            'dependencies' => array()
        );

        $system_apps = $this->getSystemApps();
        $apps = $this->getInstalledApps('app');
        $themes = $this->getInstalledApps('theme');

        $dependencies = array_merge($system_apps, $themes, $apps);

        foreach ($dependencies as $dependency){
            $snapshot['dependencies'][$dependency['url']] = $dependency['current_version'];
        }

        return json_encode($snapshot, 192);
    }

    /**
     * @param string $json
     * @return bool
     * @throws SmartLauncherAppsManagerException
     */
    public function restoreFromSnapshot($json){

        $package = json_decode($json, true);

        if (!$package){
            throw new SmartLauncherAppsManagerException('Unable to decode JSON file');
        }

        if (empty($package['name']) || empty($package['version']) || empty($package['dependencies'])){
            throw new SmartLauncherAppsManagerException('Required fields in JSON file are missing.');
        }

        $apps_path = realpath(PROJECT_PATH.'/../../'.Config::getSafe('launcher_apps_path', 'stalker_launcher_apps/'));

        if (!$apps_path){
            throw new SmartLauncherAppsManagerException('Unable to get launcher apps path');
        }

        if (!is_dir($apps_path.'/'.$package['name'])) {

            umask(0);
            $mkdir = mkdir($apps_path.'/'.$package['name'], 0777);

            if (!$mkdir) {
                throw new SmartLauncherAppsManagerException('Unable to create metapackage folder');
            }
        }

        $file_result = file_put_contents($apps_path.'/'.$package['name'].'/package.json', $json);

        if (!$file_result){
            throw new SmartLauncherAppsManagerException('Unable to create package.json in metapackage folder');
        }

        return $this->resetApps($package['name']);
    }
}

class SmartLauncherAppsManagerException extends Exception{}

class SmartLauncherAppsManagerConflictException extends SmartLauncherAppsManagerException{
    protected $conflicts;

    /**
     * SmartLauncherAppsManagerException constructor.
     * @param string $message
     * @param array $conflicts
     */
    public function __construct($message, $conflicts) {
        parent::__construct($message);
        $this->conflicts = $conflicts;
    }

    /**
     * @return array
     */
    public function getConflicts(){
        return $this->conflicts;
    }
}