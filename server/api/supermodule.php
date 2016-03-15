<?php

include "./common.php";

use Stalker\Lib\Core\Config;
use Stalker\Lib\Core\Cache;
use Stalker\Lib\Core\Stb;
use Stalker\Lib\Core\Mysql;
use Stalker\Lib\Core\Middleware;

if (!isset($_GET['key']) || !isset($_GET['mac']) || !isset($_GET['uid']) || !isset($_GET['type'])){
    return false;
}

$mac = Cache::getInstance()->get($_GET['key']);

if (!$mac || $mac != $_GET['mac']){
    return false;
}

$apps = new AppsManager();
$external_apps = $apps->getList(true);

$installed_apps = array_values(array_filter($external_apps, function($app){
    return $app['installed'] == 1 && $app['status'] == 1 && !empty($app['alias']);
}));

$installed_apps = array_map(function($app){
    return 'external_'.$app['alias'];
}, $installed_apps);

// change order order according to the package
if (Config::get('enable_tariff_plans')){

    $user = User::getInstance(Stb::getInstance()->id);

    $user_enabled_modules = $user->getServicesByType('module');

    if ($user_enabled_modules === null){
        $user_enabled_modules = array();
    }

    if (Config::getSafe('enable_modules_order_by_package', false)){

        $static_modules = array_diff(Config::get('all_modules'), $user_enabled_modules);

        $all_modules = array_merge($static_modules, $user_enabled_modules);
    }else{

        $flipped_installed_apps = array_flip($installed_apps);

        $installed_apps = array_values(array_filter($user_enabled_modules, function($module) use ($flipped_installed_apps){
            return isset($flipped_installed_apps[$module]);
        }));

        $all_modules = array_merge(Config::get('all_modules'), $installed_apps);
    }
}else{
    $all_modules = array_merge(Config::get('all_modules'), $installed_apps);
}

$disabled_modules = Stb::getDisabledModulesByUid((int) $_GET['uid']);

$available_modules = array_diff($all_modules, $disabled_modules);

/*$base_modules = array(
    "reset",
    "context_menu",
    "main_menu",
    "alert",
    "speedtest",
    "layer.base",
    "layer.list",
    "layer.setting",
    "layer.simple",
    "layer.input",
    "layer.sidebar",
    "layer.search_box",
    "layer.bottom_menu",
    "layer.scrollbar",
    "layer.vclub_info",
    "image.viewer",
    "password_input",
    "series_switch",
    "duration_input"
);

$available_modules = array_merge($base_modules, $available_modules);*/

$dependencies = array(
    'tv' => array('tv', 'tv_archive', 'time_shift', 'time_shift_local', 'epg.reminder',
                  'epg.recorder', 'epg', 'epg.simple', 'downloads_dialog', 'downloads',
                  'remotepvr', 'pvr_local'),
    'vclub' => array('vclub', 'downloads_dialog', 'downloads'),
);

if (!empty($_GET['single_module'])){

    $single_modules = explode(',', $_GET['single_module']);

    $modules = array();

    foreach ($single_modules as $single_module) {
        if (isset($dependencies[$single_module])) {
            $modules = array_merge($modules, $dependencies[$single_module]);
        }else{
            $modules = array_merge($modules, array($single_module));
        }
    }

    $available_modules = array_intersect($modules, $available_modules);
}

if ($_GET['type'] == '.js'){

    header("Content-Type: application/javascript");

    foreach ($available_modules as $module){

        if (strpos($module, 'external_') === 0){

            $module = str_replace('external_', '', $module);

            $module_url = 'http'.(((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) ? 's' : '')
                .'://'.$_SERVER['HTTP_HOST']
                .'/'.Config::getSafe('portal_url', '/stalker_portal/')
                .'server/api/ext_module.php?name='.$module;

            echo file_get_contents($module_url);

        }else{
            $file = PROJECT_PATH.'/../c/'.$module.'.js';

            if (file_exists($file)){
                readfile($file);
            }
        }
    }
}elseif (strpos($_GET['type'], '.css') !== false){

    if(preg_match('/_(\d+)\.css/', $_GET['type'], $match)){
        $resolution_prefix = '_'.$match[1];
    }else{
        $resolution_prefix = '';
    }

    $user = Stb::getByMac($mac);

    if (empty($user)){
        return false;
    }

    $theme = empty($user['theme']) || !array_key_exists($user['theme'], Middleware::getThemes())
        ? Mysql::getInstance()->from('settings')->get()->first('default_template')
        : $user['theme'];

    $path = Config::getSafe('portal_url', '/stalker_portal/');

    ob_start(function($buffer) use ($resolution_prefix, $theme, $path){
        return str_replace(
            array(
                'i'.$resolution_prefix.'/',
                'i/',
                'fonts/'),
            array(
                $path.'c/template/'.$theme.'/i'.$resolution_prefix.'/',
                $path.'c/template/'.$theme.'/i/',
                $path.'c/template/'.$theme.'/fonts/'),
            $buffer);
    });

    header("Content-Type: text/css");

    foreach ($available_modules as $module){

        if (strpos($module, 'external_') === 0){
            continue;
        }

        $file = PROJECT_PATH.'/../c/template/'.$theme.'/'.$module.$resolution_prefix.'.css';

        if (file_exists($file)){
            readfile($file);
        }
    }
}

ob_end_flush();