<?php

require_once "../../server/common.php";

/*use Stalker\Lib\Core\Config;

if (!Config::getSafe('enable_api_v3', false)){
    echo "API v3 not enabled";
    exit;
}*/

use Stalker\Lib\RESTAPI\v3\RESTApiManager;
use Stalker\Lib\OAuth\AuthAccessHandler;

$server = new RESTApiManager(new AuthAccessHandler());
$server->handleRequest();