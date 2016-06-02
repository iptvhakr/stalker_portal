<?php

include "./common.php";

if (empty($_GET['uid'])){

    exit;
}

use Stalker\Lib\Core\Config;
use Stalker\Lib\Core\Stb;

$file = file_get_contents('../../new/core/config.json');

$profile = json_decode($file, true);

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

$apps = new AppsManager($language);
$external_apps = $apps->getList(true);

$installed_apps = array_values(array_filter($external_apps, function($app){
    return $app['installed'] == 1 && $app['status'] == 1 && !empty($app['alias']);
}));

$installed_apps_names = array_map(function($app){
    return 'external_'.$app['alias'];
}, $installed_apps);

$all_modules = array_merge(Config::get('all_modules'), $installed_apps_names);
$disabled_modules = Stb::getDisabledModulesByUid((int) $_GET['uid']);

$user = Stb::getById((int) $_GET['uid']);

// if user is off - return empty menu
if ($user['status'] == 1){

    $profile['menu'] = array();

    echo json_encode($profile);
    exit;
}

$profile['options']['stalkerApiHost'] = 'http'.(((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) ? 's' : '')
    .'://'.$_SERVER['HTTP_HOST']
    .Config::getSafe('portal_url', '/stalker_portal/')
    .'api/v3/';

$profile['options']['stalkerAuthHost'] = 'http'.(((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) ? 's' : '')
    .'://'.$_SERVER['HTTP_HOST']
    .Config::getSafe('portal_url', '/stalker_portal/')
    .'auth/token.php';

$profile['options']['sap'] = 'http'.(((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) ? 's' : '')
    .'://'.$_SERVER['HTTP_HOST']
    .Config::getSafe('portal_url', '/stalker_portal/')
    .'server/api/sap.php';

$profile['options']['pingTimeout'] = Config::getSafe('watchdog_timeout', 120) * 1000;
$available_modules = array_values(array_diff($all_modules, $disabled_modules));
$available_modules[] = 'portal';
$available_modules[] = 'launcher';
$available_modules[] = 'osd';
$available_modules[] = 'osd-tv';
$available_modules[] = 'osd-pip';
$available_modules[] = 'player';
$available_modules[] = 'taskManager';
$available_modules[] = 'sap-loader';

$module_to_app_map = array(
    'vclub'         => 'video club',
    'audioclub'     => 'audio club',
    'media_browser' => 'explorer',
    'weather.day'   => 'weather',
    'ex'            => 'ex.ua',
    'game.lines'    => 'lines',
    'game.memory'   => 'memory',
    'game.sudoku'   => 'sudoku',
    'internet'      => 'browser',
    'game.2048'     => '2048'
);

$available_modules = array_map(function($module) use ($module_to_app_map){
    return isset($module_to_app_map[$module]) ? $module_to_app_map[$module] : $module;
}, $available_modules);

$apps = $profile['apps'];

$user_apps = array();

foreach ($apps as $app){

    if (!in_array(strtolower($app['name']), $available_modules) && $app['name'] != 'taskManager'){
        continue;
    }

    $app['name'] = !empty($app['name']) ? _($app['name']) : '';
    $app['description'] = !empty($app['description']) ? _($app['description']) : '';

    $user_apps[] = $app;
}

foreach ($installed_apps as $app) {
    $user_apps[] = array(
        'type'     => 'app',
        'category' => 'apps',
        'backgroundColor' => $app['icon_color'],
        'name'     => $app['alias'],
        'description'  => $app['description'],
        'icons' => array(
            'paths' => array(
                '480'  => 'img/480/',
                '576'  => 'img/576/',
                '720'  => 'img/720/',
                '1080' => 'img/1080/'
            ),
            'states' => array(
                'normal' => $app['icons'].'/2015.png',
                'active' => $app['icons'].'/2015.focus.png',
            )
        ),
        'url' => $app['app_url'].'/',
        'legacy' => true
    );
}

$profile['apps'] = $user_apps;
header('Content-Type: application/json');
echo json_encode($profile, 192);