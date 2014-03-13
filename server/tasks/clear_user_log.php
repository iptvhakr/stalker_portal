<?php
/*
    
*/
include "./common.php";

$from_date = date("Y-m-d H:i:s", time() - Config::getSafe("user_log_ttl", 1)*24*60*60);

Mysql::getInstance()->delete('user_log', array('time<' => $from_date));

Mysql::getInstance()->query('optimize table user_log');

$from_time = date("Y-m-d H:i:s",strtotime ("-1 month"));

Mysql::getInstance()->delete('readed_anec', array('readed<' => $from_time));

echo 1;