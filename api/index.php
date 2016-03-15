<?php

require_once "../server/common.php";

use Stalker\Lib\Core\Config;
use Stalker\Lib\RESTAPI\v1\RESTManager;

if (!Config::getSafe('enable_api', false) &&
      strpos($_SERVER['QUERY_STRING'], 'tv_archive') != 2 &&
      strpos($_SERVER['QUERY_STRING'], 'stream_recorder') != 2 &&
      strpos($_SERVER['QUERY_STRING'], 'monitoring_links') != 2 &&
      strpos($_SERVER['QUERY_STRING'], 'tv_tmp_link') != 2){

    header($_SERVER['SERVER_PROTOCOL']." 404 Not Found");
    echo "API not enabled";
    exit;
}

RESTManager::setAuthParams(Config::getSafe('api_auth_login', ''), Config::getSafe('api_auth_password', ''));
RESTManager::enableLogger(Config::getSafe('enable_api_log', false));
RESTManager::handleRequest();