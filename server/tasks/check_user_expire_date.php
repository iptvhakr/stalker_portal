<?php

namespace Stalker\Tasks;

include "./common.php";

use Stalker\Lib\Core;


if (Core\Config::getSafe('enable_internal_billing', false)){

    $ids = Core\Mysql::getInstance()->from("`users`")
        ->where(array(
            "(TO_DAYS(`expire_billing_date`) - TO_DAYS(NOW()) - 1) < 0 AND CAST(`expire_billing_date` AS CHAR) <> '0000-00-00 00:00:00' AND 1=" => 1,
            'status'=>0))
        ->get()->all('id');

    Core\Mysql::getInstance()->update("`users`", array(
        'status' => 1,
        'last_change_status' => 'NOW()'
    ), array(
        " `id` IN ('" . implode("', '", $ids) . "') AND 1=" => 1,
        'status'                                            => 0
    ));

    $online = Core\Middleware::getOnlineUsersId();
    $event = new \SysEvent();
    $event->setUserListById(array_intersect($ids, $online));
    $event->sendCutOff();
}
?>