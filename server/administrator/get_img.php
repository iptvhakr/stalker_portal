<?php

session_start();
include "./common.php";
Admin::checkAuth();

$image_url = Config::getSafe('vclub_info_provider', 'kinopoisk') == 'kinopoisk' ? 'kinopoisk.ru/' : 'image.tmdb.org/';

if (strpos($_GET['url'], 'http://') === 0 && strpos($_GET['url'], $image_url)){
    echo file_get_contents($_GET['url']);
}