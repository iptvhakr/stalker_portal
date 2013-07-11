<?php
/*
    
*/
include "./common.php";

$from_date = date("Y-m-d H:i:s", time() - 7*24*60*60);

Mysql::getInstance()->delete('master_log', array('added<' => $from_date));

Mysql::getInstance()->query('optimize table master_log')->result();

echo 1;