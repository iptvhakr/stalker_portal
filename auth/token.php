<?php

require_once "../server/common.php";

use Stalker\Lib\OAuth\AuthAccessHandler;
use Stalker\Lib\OAuth\OAuthServer;

$oauth_server = new OAuthServer(new AuthAccessHandler());
$oauth_server->setTokenType(Config::getSafe("api_v2_access_type", "bearer"));
$oauth_server->handleAuthRequest();
