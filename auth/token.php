<?php

require_once "../server/common.php";

use Stalker\Lib\OAuth\AuthAccessHandler;
use Stalker\Lib\OAuth\OAuthServer;

$oauth_server = new OAuthServer(new AuthAccessHandler());
$oauth_server->handleAuthRequest();

?>