<?php

require_once "../../server/common.php";

use Stalker\Lib\Core\Config;

if (!Config::getSafe('enable_api_v2', false)){
    echo "API v2 not enabled";
    exit;
}

use Stalker\Lib\RESTAPI\v2\RESTApiManager;
use Stalker\Lib\OAuth\AuthAccessHandler;

$server = new RESTApiManager(new AuthAccessHandler());
$server->handleRequest();