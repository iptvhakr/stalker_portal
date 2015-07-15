<?php

include "./common.php";


if (!isset($_GET['key']) || !isset($_GET['mac']) || !isset($_GET['uid']) || !isset($_GET['type'])){
    return false;
}

$mac = Cache::getInstance()->get($_GET['key']);

if (!$mac || $mac != $_GET['mac']){
    return false;
}

$all_modules = Config::get('all_modules');
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

        $file = PROJECT_PATH.'/../c/'.$module.'.js';

        if (file_exists($file)){
            readfile($file);
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

    $theme = empty($user['theme']) || !in_array($user['theme'], Middleware::getThemes())
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

        $file = PROJECT_PATH.'/../c/template/'.$theme.'/'.$module.$resolution_prefix.'.css';

        if (file_exists($file)){
            readfile($file);
        }
    }
}

ob_end_flush();