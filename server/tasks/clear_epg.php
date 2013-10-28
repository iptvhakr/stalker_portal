<?php
/*
    
*/
include "./common.php";

$from_date = date("Y-m-d H:i:s", time() - Config::getSafe('epg_history_weeks', 1)*7*24*60*60);

Mysql::getInstance()->delete('epg', array('time<' => $from_date));

Mysql::getInstance()->query('optimize table epg')->result();

echo 1;
