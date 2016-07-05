<?php
/*
    
*/

if (php_sapi_name() != "cli") {
    exit;
}

error_reporting(E_ALL);

include "./common.php";

use Stalker\Lib\Core\Mysql;

$uid_arr = Mysql::getInstance()
    ->from('vclub_paused')
    ->where(array(
        'UNIX_TIMESTAMP(pause_time)<' => time() - 86400
    ))
    ->get()
    ->all('uid');

if (count($uid_arr) > 0){

    Mysql::getInstance()->query("delete from vclub_paused where uid in (".implode(', ', $uid_arr).")");
    
    $event = new SysEvent();
    $event->setUserListById($uid_arr);
    $event->sendResetPaused();
}

if (count($argv) == 1){
    sleep(rand(0, 600));
}


\Stalker\Lib\Core\Middleware::checkUpdates();