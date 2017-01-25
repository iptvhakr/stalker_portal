<?php

include "./common.php";

use Stalker\Lib\Core\Mysql;
use Stalker\Lib\Core\Middleware;

if (empty($_GET['name']) || empty($_GET['mac'])){
    exit;
}

$alias = str_replace('external_', '', $_GET['name']);

$app = Mysql::getInstance()->from('apps')->where(array('alias' => $alias))->get()->first();

if (empty($app) || $app['status'] == 0){
    exit;
}

$apps = new AppsManager();
$app = $apps->getAppInfoWoFetch($app['id']);

if (!$app['installed']){
    exit;
}

header('Content-Type: application/x-javascript');

$user = Mysql::getInstance()->from('users')->where(array('mac' => $_GET['mac']))->get()->first();

$disabled_for_mag200_apps = array('youtube.com', 'zoomby', 'megogo', 'olltv');

if ($user && $user['stb_type'] == 'MAG200' && in_array(strtolower($app['name']), $disabled_for_mag200_apps) !== false){
    exit;
}

$user_theme = empty($user['theme']) || !array_key_exists($user['theme'], Middleware::getThemes())
    ? Mysql::getInstance()->from('settings')->get()->first('default_template')
    : $user['theme'];

$icon = $app['app_url'].'/img/{0}/'.$app['icons'].'/'.($user_theme == 'default' ? '2010' : '2014').'.png';

if ($app['options'] && $options = json_decode($app['options'], true)){
    $app['app_url'] .= (strpos($app['app_url'], '?') ? '&' : '?').http_build_query($options);
}

?>
/**
* Redirection to <?= $app['name'] ?> module.
*/
(function(){

main_menu.add('<?= $app['name'] ?>', [], '<?= $icon ?>', function(){

var params = '';

var url = '<?= $app['app_url']?>';

if (stb.user['web_proxy_host']){
    params += (url.indexOf('?') == -1 ? '?' : '&')+'proxy=http://';
    if (stb.user['web_proxy_user']){
        params += stb.user['web_proxy_user']+':'+stb.user['web_proxy_pass']+'@';
    }
    params += stb.user['web_proxy_host']+':' +stb.user['web_proxy_port'];
}

stb.setFrontPanel('.');

if (!params && url.indexOf('?') == -1){
    params += '?';
}else{
    params += '&';
}

params = stb.add_referrer(params, this.module.layer_name);

_debug('url', url+params);

window.location = url+params;

}, {layer_name : "external_<?= $app['alias'] ?>"});

loader.next();
})();