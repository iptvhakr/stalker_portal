<?php

include "./common.php";

if (Config::getSafe('enable_internal_billing', false) && Config::getSafe('number_of_days_to_send_message', false) !== FALSE){

    $locales = array();

    $allowed_locales = Config::get("allowed_locales");

    foreach ($allowed_locales as $lang => $locale){
        $locales[substr($locale, 0, 2)] = $locale;
    }

    $accept_language = !empty($_SERVER["HTTP_ACCEPT_LANGUAGE"]) ? $_SERVER["HTTP_ACCEPT_LANGUAGE"] : null;

    if (!empty($_COOKIE['language']) && array_key_exists($_COOKIE['language'], $locales)){
        $locale = $locales[$_COOKIE['language']];
    }else if ($accept_language && array_key_exists(substr($accept_language, 0, 2), $locales)){
        $locale = $locales[substr($accept_language, 0, 2)];
    }else{
        $locale = $locales[key($locales)];
    }

    setcookie("debug_key", "", time() - 3600, "/");

    setlocale(LC_MESSAGES, $locale);
    setlocale(LC_TIME, $locale);
    putenv('LC_MESSAGES='.$locale);
    bindtextdomain('stb', PROJECT_PATH.'/locale');
    textdomain('stb');
    bind_textdomain_codeset('stb', 'UTF-8');

    $end_billing_interval = Config::getSafe('number_of_days_to_send_message', false);

    $users = Mysql::getInstance()->select(array(
        'id',
        '(TO_DAYS(`expire_billing_date`) - TO_DAYS(NOW())) as `end_billing_days`'
    ))->from("`users`")
        ->where(array(
            "(TO_DAYS(`expire_billing_date`) - TO_DAYS(NOW())) <= $end_billing_interval AND CAST(`expire_billing_date` AS CHAR) <> '0000-00-00 00:00:00' AND 1=" => 1,
            'status'=>0))
        ->get()->all();

    $event = new SysEvent();
    $event->setTtl(86340);
    $msg_more = 'Term of your account will expire in "%s" days. In order to prevent tripping, prolong your account';
    $msg_today = "Term of your account will expire today. In order to prevent tripping, prolong your account";
    $msg = '';
    foreach($users as $user){
        $event->setUserListById($user['id']);
        $msg = $user['end_billing_days'] > 0? sprintf(_($msg_more), $user['end_billing_days']) : $msg_today;
        $event->sendMsg($msg);
        echo '-----------------------------------------------------------', PHP_EOL;
        echo $user['id'], PHP_EOL;
        echo $msg, PHP_EOL;
    }
}
?>