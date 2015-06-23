<?php

include "./common.php";

if (Config::getSafe('enable_internal_billing', false)){

    $ids = Mysql::getInstance()->from("`users`")
        ->where(array(
            "(TO_DAYS(`expire_billing_date`) - TO_DAYS(NOW()) - 1) < 0 AND CAST(`expire_billing_date` AS CHAR) <> '0000-00-00 00:00:00' AND 1=" => 1,
            'status'=>0))
        ->get()->all('id');

    Mysql::getInstance()->update("`users`", array(
        'status' => 1,
        'last_change_status' => 'NOW()'
    ), array(
        " `id` IN ('" . implode("', '", $ids) . "') AND 1=" => 1,
        'status'                                            => 0
    ));

    $online = Middleware::getOnlineUsersId();
    $event = new SysEvent();
    $event->setUserListById(array_intersect($ids, $online));
    $event->sendCutOff();
}
?>