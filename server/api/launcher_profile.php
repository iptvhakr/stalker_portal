<?php

include "./common.php";

if (empty($_GET['uid'])){

    exit;
}

use Stalker\Lib\Core\Config;
use Stalker\Lib\Core\Stb;
use Stalker\Lib\Core\Mysql;

$config = array(
    'options' => array(),
    'themes'  => array(),
    'apps'    => array()
);

$language = isset($_GET['language']) ? $_GET['language'] : 'en';

$allowed_languages = Config::get('allowed_locales');
$allowed_languages_map = array();

foreach ($allowed_languages as $loc){
    $allowed_languages_map[substr($loc, 0, 2)] = $loc;
}

if (isset($allowed_languages_map[$language])){
    $locale = $allowed_languages_map[$language];
}elseif (count($allowed_languages_map) > 0){
    reset($allowed_languages_map);
    $locale = $allowed_languages_map[key($allowed_languages_map)];
}else{
    $locale = 'en_GB.utf8';
}

setlocale(LC_MESSAGES, $locale);
putenv('LC_MESSAGES='.$locale);

$app_manager = new SmartLauncherAppsManager($language);
$installed_apps = $app_manager->getInstalledApps();

$installed_apps_names = array_map(function($app){
    return 'launcher_'.$app['alias'];
}, $installed_apps);

$user = Stb::getById((int) $_GET['uid']);

// if user is off - return empty menu
if (!empty($user) && $user['status'] == 1){

    echo json_encode($config);
    exit;
}

if (!empty($user)){
    User::getInstance($user['id']);
}

$all_modules = array_merge(Config::get('all_modules'), $installed_apps_names);
$disabled_modules = Stb::getDisabledModulesByUid((int) $_GET['uid']);

$config['options']['pluginsPath'] = '../../../plugins/';

$config['options']['stalkerHost'] = 'http'.(((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) ? 's' : '')
    .'://'.(strpos($_SERVER['HTTP_HOST'], ':') > 0 ? $_SERVER['HTTP_HOST'] : $_SERVER['HTTP_HOST'].':'.$_SERVER['SERVER_PORT']);

$config['options']['appsPackagesPath'] = '/'.Config::getSafe('launcher_apps_path', 'stalker_launcher_apps/');

$config['options']['stalkerApiPath']    = Config::getSafe('portal_url', '/stalker_portal/') . 'api/v3/';
$config['options']['stalkerAuthPath']   = Config::getSafe('portal_url', '/stalker_portal/') . 'auth/token.php';
$config['options']['stalkerLoaderPath'] = Config::getSafe('portal_url', '/stalker_portal/') . 'c/';

$config['options']['stalkerApiHost'] = $config['options']['stalkerHost'].$config['options']['stalkerApiPath'];

$config['options']['stalkerAuthHost'] = $config['options']['stalkerHost'] . $config['options']['stalkerAuthPath'];

$config['options']['sap'] = 'http'.(((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) ? 's' : '')
    .'://'.(strpos($_SERVER['HTTP_HOST'], ':') > 0 ? $_SERVER['HTTP_HOST'] : $_SERVER['HTTP_HOST'].':'.$_SERVER['SERVER_PORT'])
    .Config::getSafe('portal_url', '/stalker_portal/')
    .'server/api/sap.php';

$config['options']['pingTimeout'] = Config::getSafe('watchdog_timeout', 120) * 1000;
$available_modules = array_values(array_diff($all_modules, $disabled_modules));

$themes = $app_manager->getInstalledApps('theme');

if (!empty($themes)){

    $user_theme = isset($user['theme']) ? $user['theme'] : '';

    if (!$user_theme){
        $default_theme = Mysql::getInstance()->from('settings')->get()->first('default_template');

        if ($default_theme == 'smart_launcher'){
            $default_theme = $themes[0]['alias'];
        }

        $user_theme = $default_theme;
    }

    $theme_alias = str_replace('smart_launcher:', '', $user_theme);

    if ($user_theme != 'smart_launcher' && $theme_alias){
        foreach ($themes as $theme){
            if ($theme['alias'] == $theme_alias){
                $config['themes'][$theme['alias']] = $theme['current_version'];
            }
        }
    }

    if (empty($config['themes'])){
        $theme = reset($themes);
        $config['themes'][$theme['alias']] = $theme['current_version'];
    }
}

foreach ($themes as $theme){
    $config['themes'][$theme['alias']] = $theme['current_version'];
}

$user_apps = array();

$system_apps = $app_manager->getSystemApps();

$installed_apps = array_merge($system_apps, $installed_apps);

foreach ($installed_apps as $app) {

    if ((!in_array('launcher_'.$app['alias'], $available_modules) || empty($user)) && $app['type'] == 'app'){
        continue;
    }

    if ($app['type'] == 'core'){
        continue;
    }

    if ($app['config']){
        $app_config = json_decode($app['config'], true);
        if ($app_config){
            $app['config'] = $app_config;
        }
    }


    if ($app['config']){
        $app['config']['packageName'] = $app['url'];
        $app['config']['version'] = $app['current_version'];

        if (!isset($app['config']['uris'])){

            $app['config']['entry'] = isset($app['config']['entry']) ? $app['config']['entry'] : 'app/';

            $app['config']['url'] = 'http'.(((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) ? 's' : '')
                .'://'.(strpos($_SERVER['HTTP_HOST'], ':') > 0 ? $_SERVER['HTTP_HOST'] : $_SERVER['HTTP_HOST'].':'.$_SERVER['SERVER_PORT'])
                .'/'.Config::getSafe('launcher_apps_path', 'stalker_launcher_apps/')
                .$app['alias']
                .'/'.$app['config']['version']
                .'/'.$app['config']['entry'];
        }

        if ($app['options'] && ($options = json_decode($app['options'], 1))){
            $app['config']['options'] = $options;
        }

        try{
            $app['config']['dependencies'] = $app_manager->getFullAppDependencies($app['id']);
        }catch (SmartLauncherAppsManagerException $e){
            error_log($e->getMessage());
            continue;
        }

        if ($app['localization']){

            $app['localization'] = json_decode($app['localization'], true);
            $app['config']['name'] = isset($app['localization'][$language]['name']) ? $app['localization'][$language]['name'] : $app['config']['name'];
            $app['config']['description'] = isset($app['localization'][$language]['description']) ? $app['localization'][$language]['description'] : $app['config']['description'];
        }

        $user_apps[] = $app['config'];
    }
}

$config['apps'] = $user_apps;
header('Content-Type: application/json');
echo json_encode($config, 192);