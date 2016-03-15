<?php
/*
    
*/
include "./common.php";

use Stalker\Lib\Core\Mysql;

$day = date("j");

if ($day <= 15){
    $field_name = 'count_first_0_5';
}else{
    $field_name = 'count_second_0_5';
}


Mysql::getInstance()->update('video', array($field_name => 0));

echo 1;