<?php

include "./common.php";

if (empty($_GET['name'])){
    exit;
}

$alias = $_GET['name'];

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

?>
/**
* Redirection to <?= $app['name'] ?> module.
*/
(function(){

main_menu.add(<?= $app['name'] ?>, [], 'mm_ico_<?= $app['alias'] ?>.png', function(){

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

_debug('url', <?= $app['app_url'] ?>+params);

window.location = <?= $app['app_url'] ?>+params;
}, {layer_name : "<?= $app['alias'] ?>"});

loader.next();
})();