<?php

ini_set('display_errors',1);
error_reporting(E_ALL);

require_once("./config.php");

define('PROJECT_PATH', dirname(__FILE__));

function __autoload($class_name) {

    $class = PROJECT_PATH.'/lib/'.strtolower($class_name).'.class.php';

    if (!file_exists($class)){
        throw new Exception('Class file for "'.$class_name.'" not found');
    }

    require_once $class;
}

?>