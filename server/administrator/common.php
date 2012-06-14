<?php

$_SERVER['HTTP_TARGET'] = 'ADM';

include "../common.php";

$locales = array(
    'en' => 'en_GB.utf8',
    'ru' => 'ru_RU.utf8'
);

$accept_language = !empty($_SERVER["HTTP_ACCEPT_LANGUAGE"]) ? $_SERVER["HTTP_ACCEPT_LANGUAGE"] : null;

if (!empty($_COOKIE['language']) && array_key_exists($_COOKIE['language'], $locales)){
    $locale = $locales[$_COOKIE['language']];
}else if ($accept_language && array_key_exists(substr($accept_language, 0, 2), $locales)){
    $locale = $locales[substr($accept_language, 0, 2)];
}else{
    $locale = $locales[key($locales)];
}

setcookie("debug_key", "", time() - 3600, "/");

//$locale = 'en_GB.utf8';

setlocale(LC_MESSAGES, $locale);
putenv('LC_MESSAGES='.$locale);
bindtextdomain('stb', PROJECT_PATH.'/locale');
textdomain('stb');
bind_textdomain_codeset('stb', 'UTF-8');

include "../lib/func.php";
