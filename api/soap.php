<?php

require_once "../server/common.php";

if (!Config::getSafe('enable_soap_api', false)){
    header($_SERVER["SERVER_PROTOCOL"]." 403 Forbidden");
    echo "SOAP API is not enabled";
    exit;
}

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