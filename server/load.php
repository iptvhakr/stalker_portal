<?php

$start_time = microtime(1);

// no cache
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");
header("Last-Modified: Thu, 01 Jan 1970 00:00:00 GMT");
header("Pragma: no-cache");
header("Cache-Control: max-age=0, no-cache, must-revalidate");

require_once "common.php";

set_error_handler(array($debug = Debug::getInstance(), 'parsePHPError'));

$JsHttpRequest = new JsHttpRequest("utf-8");
Stb::getInstance();
$loader = new DataLoader($_REQUEST['type'], $_REQUEST['action']);
$GLOBALS['_RESULT'] = $loader->getResult();

echo "generated in: ".round(microtime(1) - $start_time, 3)."s; query counter: ".Mysql::get_num_queries()."; ".$debug->getErrorStr();
?>