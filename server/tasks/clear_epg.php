<?php
/*
    
*/
include "./common.php";

$from_date = date("Y-m-d H:i:s", time() - 7*24*60*60);

Mysql::getInstance()->delete('epg', array('time<' => $from_date));

Mysql::getInstance()->query('optimize table epg')->result();

echo 1;
