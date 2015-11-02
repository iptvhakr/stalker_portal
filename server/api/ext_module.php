<?php

include "./common.php";

if (empty($_GET['name']) || empty($_GET['mac'])){
    exit;
}

$alias = str_replace('external_', '', $_GET['name']);

$app = Mysql::getInstance()->from('apps')->where(array('alias' => $alias))->get()->first();

if (empty($app) || $app['status'] == 0){
    exit;
}

$apps = new AppsManager();
$app = $apps->getAppInfo($app['id']);

if (!$app['installed']){
    exit;
}

header('Content-Type: application/x-javascript');

$user_theme = Mysql::getInstance()->from('users')->where(array('mac' => $_GET['mac']))->get()->first('theme');
$user_theme = empty($user_theme) || !array_key_exists($user_theme, Middleware::getThemes())
    ? Mysql::getInstance()->from('settings')->get()->first('default_template')
    : $user_theme;

$icon = $app['app_url'].'/img/{0}/'.$app['icons'].'/'.($user_theme == 'default' ? '2010' : '2014').'.png'
?>
/**
* Redirection to <?= $app['name'] ?> module.
*/
(function(){

main_menu.add('<?= $app['name'] ?>', [], '<?= $icon ?>', function(){

var params = '';

if (stb.user['web_proxy_host']){
params += '?proxy=http://';
if (stb.user['web_proxy_user']){
params += stb.user['web_proxy_user']+':'+stb.user['web_proxy_pass']+'@';
}
params += stb.user['web_proxy_host']+':' +stb.user['web_proxy_port'];
}

stb.setFrontPanel('.');

if (!params){
params += '?';
}else{
params += '&';
}

params = stb.add_referrer(params, this.module.layer_name);

_debug('url', '<?= $app['app_url'] ?>'+params);

window.location = '<?= $app['app_url'] ?>'+params;
}, {layer_name : "external_<?= $app['alias'] ?>"});

loader.next();
})();