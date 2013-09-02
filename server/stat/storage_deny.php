<?php
/*
    online, offline
*/
include "../common.php";

$in_param = $argv[1];

$counter = Mysql::getInstance()->from('storage_deny')->where(array('name' => $in_param))->get()->first('counter');

Mysql::getInstance()->update('storage_deny', array('counter' => 0), array('name' => $in_param));

echo $counter;
