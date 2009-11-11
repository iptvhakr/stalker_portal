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

//$JsHttpRequest = new Subsys_JsHttpRequest_Php("utf-8");
$JsHttpRequest = new JsHttpRequest("utf-8");

//$_RESULT = get_data();
$GLOBALS['_RESULT'] = get_data();

$end_time = microtime(1);
$load_time = $end_time - $start_time;
$db = Database::getInstance(DB_NAME);
echo "generated in: ".round($load_time, 2)."s; query counter: $db->query_counter; ".$debug->getErrorStr();
?>