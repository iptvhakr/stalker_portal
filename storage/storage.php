<?php

ini_set('display_errors',1);
error_reporting(E_ALL);

require_once("./config.php");
require_once("./lib/storage_soap.class.php");

//$server = new SoapServer('http://'.MASTER_IP.'/stalker_portal/server/storage/storage.wsdl.php');
$server = new SoapServer(null, array('uri' => 'urn:storage', 'soap_version' => SOAP_1_1));
$server->setClass("Storage_soap");
$server->handle();

?>