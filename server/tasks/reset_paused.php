<?php
/*
    
*/
error_reporting(E_ALL);

include "./common.php";

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

echo count($uid_arr);