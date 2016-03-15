<?php

include "./common.php";

use Stalker\Lib\Core\Mysql;
use Stalker\Lib\Core\Config;

$tmp = Mysql::getInstance()->select(array(
        'count(id) as `count`',
        '`reseller_id`'
    ))->from("users")->where(array(
        'UNIX_TIMESTAMP(keep_alive)>' => time() - Config::get('watchdog_timeout') * 2
    ))->groupby('reseller_id')->get()->all();
$users_online = array(0 => 0);
foreach ($tmp as $row) {
    if (!array_key_exists((int)$row['reseller_id'], $users_online)) {
        $users_online[(int)$row['reseller_id']] = 0;
    }
    $users_online[(int)$row['reseller_id']] += $row['count'];
}

$users_online['total'] = array_sum($users_online);

Mysql::getInstance()->insert('users_activity', array('users_online' => json_encode($users_online)));