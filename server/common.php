<?php
date_default_timezone_set(TIMEZONE);

ini_set('display_errors', 1);
error_reporting(E_ALL);

define ('FATAL',E_USER_ERROR);
define ('ERROR',E_USER_WARNING);
define ('WARNING',E_USER_NOTICE);

if (!defined("PATH_SEPARATOR")){
    define("PATH_SEPARATOR", getenv("COMSPEC")? ";" : ":");
}

ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.dirname(__FILE__));

function __autoload($class_name) {
    $class = 'lib/'.strtolower($class_name).'.class.php';
    require_once $class;
}

?>