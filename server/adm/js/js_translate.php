<?php

$_SERVER['TARGET'] = 'ADM';

include "../../common.php";

$locales = array();

$allowed_locales = Config::get("allowed_locales");

foreach ($allowed_locales as $lang => $locale){
    $locales[substr($locale, 0, 2)] = $locale;
}

$accept_language = !empty($_SERVER["HTTP_ACCEPT_LANGUAGE"]) ? $_SERVER["HTTP_ACCEPT_LANGUAGE"] : null;

if (!empty($_GET['language']) && array_key_exists(substr($_GET['language'], 0, 2), $locales)) {
    $language = substr($_GET['language'], 0, 2);
} else if (!empty($_COOKIE['language']) && (array_key_exists($_COOKIE['language'], $locales) || in_array($_COOKIE['language'], $locales))){
    $language = substr($_COOKIE['language'], 0, 2);
}else if ($accept_language && array_key_exists(substr($accept_language, 0, 2), $locales)){
    $language = substr($accept_language, 0, 2);
}else{
    reset($locales);
    $language = substr(key($locales), 0, 2);
}

$locale = $locales[$language];

setlocale(LC_MESSAGES, $locale);
setlocale(LC_TIME, $locale);
putenv('LC_MESSAGES=' . $locale);
bindtextdomain('stb', PROJECT_PATH . '/locale');
textdomain('stb');
bind_textdomain_codeset('stb', 'UTF-8');


$words['Wait'] = _('Wait');
$words['Request_is_being_prossessed'] = _('Request is being processed');
$words['Done'] = _('Done');
$words['Failed'] = _('Failed');
$words['Clean'] = _('Clean');
$words['CMD_Exists'] = _('This URL already exists');
$words['Jan'] = _('Jan');
$words['Feb'] = _('Feb');
$words['Mar'] = _('Mar');
$words['Apr'] = _('Apr');
$words['May'] = _('May');
$words['Jun'] = _('Jun');
$words['Jul'] = _('Jul');
$words['Aug'] = _('Aug');
$words['Sep'] = _('Sep');
$words['Oct'] = _('Oct');
$words['Nov'] = _('Nov');
$words['Dec'] = _('Dec');

echo json_encode($words);