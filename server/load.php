<?php

$start_time = microtime(1);

// no cache
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");
header("Last-Modified: Thu, 01 Jan 1970 00:00:00 GMT");
header("Pragma: no-cache");
header("Cache-Control: max-age=0, no-cache, must-revalidate");

require_once "conf_serv.php";
require_once "common.php";
//require_once "lib/config.php";
require_once "lib/subsys/php.php";
//require_once "lib/data.php";
//require_once "lib/func.php";

Stb::setModules($_ALL_MODULES, $_DISABLED_MODULES);
//Stb::setAllowedLanguages($_ALLOWED_LANG);
Stb::setAllowedLocales($_ALLOWED_LOCALES);

set_error_handler(array($debug = Debug::getInstance(), 'parsePHPError'));

$JsHttpRequest = new JsHttpRequest("utf-8");
Stb::getInstance();
$loader = new DataLoader($_REQUEST['type'], $_REQUEST['action']);
$GLOBALS['_RESULT'] = $loader->getResult();

//$db = Database::getInstance(DB_NAME);

//$log = Log::getInstance();

//$generated_in = round(microtime(1) - $start_time, 3);

//$log->savePageGenerationTime($generated_in);

echo "generated in: ".round(microtime(1) - $start_time, 3)."s; query counter: ".Mysql::get_num_queries()."; ".$debug->getErrorStr();
?>