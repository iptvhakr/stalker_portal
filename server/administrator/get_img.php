<?php

session_start();
include "./common.php";
Admin::checkAuth();

if (strpos($_GET['url'], 'http://') === 0 && strpos($_GET['url'], 'kinopoisk.ru/')){
    echo file_get_contents($_GET['url']);
}