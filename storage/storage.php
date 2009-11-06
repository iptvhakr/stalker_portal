<?php

ini_set('display_errors',1);
error_reporting(E_ALL);

require_once("./config.php");
require_once("./lib/storage.class.php");

$server = new SoapServer('http://'.MASTER_IP.'/stalker_portal/server/storage/storage.wsdl.php');
$server->setClass("Storage");
$server->handle();

?>