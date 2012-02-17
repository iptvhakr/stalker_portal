<?php

require_once "../server/common.php";

$oauth_server = new OAuthServer(new AuthAccessHandler());
$oauth_server->handleAuthRequest();

?>