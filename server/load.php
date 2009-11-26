<?php
putenv("TZ=Europe/Zaporozhye");

$start_time = microtime(1);
ini_set('display_errors',1);
error_reporting(E_ALL);

define ('FATAL',E_USER_ERROR);
define ('ERROR',E_USER_WARNING);
define ('WARNING',E_USER_NOTICE);

//session_start();

// no cache
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");
header("Last-Modified: Thu, 01 Jan 1970 00:00:00 GMT");
header("Pragma: no-cache");
header("Cache-Control: max-age=0, no-cache, must-revalidate");

require_once "./lib/config.php";
require_once "./lib/subsys/php.php";
require_once "./lib/data.php";
require_once "./lib/func.php";
require_once "./conf_serv.php";

set_error_handler(array($debug = Debug::getInstance(), 'parsePHPError'));

if (isset($_GET['JsHttpRequest'])){
    $JsHttpRequest = new JsHttpRequest("utf-8");
    $loader = new DataLoader($_REQUEST['type'], $_REQUEST['action']);
    $GLOBALS['_RESULT'] = $loader->getResult();
}else{
    $JsHttpRequest = new Subsys_JsHttpRequest_Php("utf-8");
    $_RESULT = get_data();
}

$db = Database::getInstance(DB_NAME);
$mysql = Mysql::getInstance();

echo "generated in: ".round(microtime(1) - $start_time, 3)."s; query counter: ".($db->query_counter+$mysql->getQueryCounter())."; ".$debug->getErrorStr();
?>