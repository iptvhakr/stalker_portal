<?php

$start_time = microtime(1);

// no cache
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");
header("Last-Modified: Thu, 01 Jan 1970 00:00:00 GMT");
header("Pragma: no-cache");
header("Cache-Control: max-age=0, no-cache, must-revalidate");

require_once "common.php";
require_once "lib/config.php";
require_once "lib/subsys/php.php";
require_once "lib/data.php";
require_once "lib/func.php";
require_once "conf_serv.php";

set_error_handler(array($debug = Debug::getInstance(), 'parsePHPError'));

if (isset($_GET['JsHttpRequest'])){
    $JsHttpRequest = new JsHttpRequest("utf-8");
    $loader = new DataLoader($_REQUEST['type'], $_REQUEST['action']);
    $GLOBALS['_RESULT'] = $loader->getResult();
    $mysql = Mysql::getInstance();
    $counter = $mysql->get_num_queries();
}else{
    $JsHttpRequest = new Subsys_JsHttpRequest_Php("utf-8");
    $_RESULT = get_data();
    $counter = 0;
}

$db = Database::getInstance(DB_NAME);

$log = Log::getInstance();

$generated_in = round(microtime(1) - $start_time, 3);

$log->savePageGenerationTime($generated_in);

echo "generated in: ".$generated_in."s; query counter: ".($db->query_counter+$counter)."; ".$debug->getErrorStr();
?>