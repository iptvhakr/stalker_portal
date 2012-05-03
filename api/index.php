<?php

require_once "../server/common.php";

if (!Config::getSafe('enable_api', false) &&
      (empty($_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['REMOTE_ADDR'] : $_SERVER['HTTP_X_REAL_IP']) != $_SERVER['SERVER_ADDR'] &&
      strpos($_SERVER['QUERY_STRING'], 'tv_archive') != 2 &&
      strpos($_SERVER['QUERY_STRING'], 'stream_recorder') != 2 &&
      strpos($_SERVER['QUERY_STRING'], 'tv_tmp_link') != 2){

    header($_SERVER['SERVER_PROTOCOL']." 404 Not Found");
    echo "API not enabled";
    exit;
}

RESTManager::setAuthParams(Config::getSafe('api_auth_login', ''), Config::getSafe('api_auth_password', ''));
RESTManager::enableLogger(Config::getSafe('enable_api_log', false));
RESTManager::handleRequest();

?>