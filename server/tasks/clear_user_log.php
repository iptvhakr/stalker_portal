<?php
/*
    
*/
include "./common.php";

$from_date = date("Y-m-d H:i:s", time() - Config::getSafe("user_log_ttl", 1)*24*60*60);

$from_id = Mysql::getInstance()
    ->select('max(id) as max_id')
    ->from('user_log')
    ->where(array('time<' => $from_date))
    ->get()
    ->first('max_id');

if ($from_id){

    Mysql::getInstance()->delete('user_log', array('id<' => $from_id));

    if (Config::getSafe('use_optimize_table', true)){
        Mysql::getInstance()->query('optimize table user_log');
    }
}

$from_time = date("Y-m-d H:i:s",strtotime ("-1 month"));

$from_id = Mysql::getInstance()
    ->select('max(id) as max_id')
    ->from('readed_anec')
    ->where(array('readed<' => $from_time))
    ->get()
    ->first('max_id');

if ($from_id){
    Mysql::getInstance()->delete('readed_anec', array('id<' => $from_id));
}

echo 1;