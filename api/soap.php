<?php

require_once "../server/common.php";

use Stalker\Lib\SOAPApi\v1\SoapApiServer;

$api_server = new SoapApiServer();

if (isset($_GET['wsdl'])){
    $api_server->outputWsdl();
}elseif (isset($_GET['docs'])){
    $api_server->outputDocs();
}elseif (isset($_GET['phpsoapclient'])){
    $api_server->outputPhpClient();
}else{
    $api_server->handleRequest();
}