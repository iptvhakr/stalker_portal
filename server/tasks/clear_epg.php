<?php
/*
    
*/
include "./common.php";

use Stalker\Lib\Core\Mysql;
use Stalker\Lib\Core\Config;

$from_date = date("Y-m-d H:i:s", time() - Config::getSafe('epg_history_weeks', 1)*7*24*60*60);

Mysql::getInstance()->delete('epg', array('time<' => $from_date));

if (Config::getSafe('use_optimize_table', true)){

    Mysql::getInstance()->query('ALTER TABLE `epg` DROP INDEX `ch_id_time`');
    Mysql::getInstance()->query('ALTER TABLE `epg` DROP INDEX `real_id`');
    Mysql::getInstance()->query('OPTIMIZE TABLE `epg`');
    Mysql::getInstance()->query('ALTER TABLE `epg` ADD INDEX `ch_id_time` (`ch_id`,`time`)');
    Mysql::getInstance()->query('ALTER TABLE `epg` ADD INDEX `real_id` (`real_id`)');
}

echo 1;
