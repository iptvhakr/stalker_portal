<?php

require_once "../server/conf_serv.php";
require_once "../server/common.php";

/*spl_autoload_register(function($class_name){
    $class = '../server/lib/'.strtolower($class_name).'.class.php';
    require_once $class;
});*/

RESTManager::handleRequest();

?>