<?php
/*
    
*/
error_reporting(E_ALL);

include "../conf_serv.php";
include "../lib/func.php";

$db = new Database(DB_NAME);

$now_timestamp = time() - 86400;

$sql = "select * from vclub_paused where UNIX_TIMESTAMP(pause_time)<$now_timestamp";
$rs = $db->executeQuery($sql);
$uid_arr = $rs->getValuesByName('uid');

if (count($uid_arr) > 0){
    $uid_str = join(",", $uid_arr);
    $sql = "delete from vclub_paused where uid in ($uid_str)";
    $db->executeQuery($sql);
    
    $event = new SysEvent();
    $event->setUserListById($uid_arr);
    $event->sendResetPaused();
}

echo count($uid_arr);

?>