<?php
/*
    
*/
include "./common.php";

$from_date = date("Y-m-d H:i:s", time() - 7*24*60*60);

$from_id = Mysql::getInstance()
    ->select('max(id) as max_id')
    ->from('master_log')
    ->where(array('added<' => $from_date))
    ->get()
    ->first('max_id');

if ($from_id){

    Mysql::getInstance()->delete('master_log', array('id<' => $from_id));

    if (Config::getSafe('use_optimize_table', true)){

        Mysql::getInstance()->query('ALTER TABLE `master_log` DROP INDEX `added`');
        Mysql::getInstance()->query('optimize table master_log');
        Mysql::getInstance()->query('ALTER TABLE `master_log` ADD INDEX `added` (`added`)');
    }
}

echo 1;