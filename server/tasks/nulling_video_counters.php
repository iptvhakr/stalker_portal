<?php
/*
    
*/
include "./common.php";

$db = new Database();

$day = date("j");

if ($day <= 15){
    $field_name = 'count_first_0_5';
}else{
    $field_name = 'count_second_0_5';
}

$sql = "update video set $field_name=0";
$rs=$db->executeQuery($sql);
if ($rs){
    echo 1;
}else{
    echo 0;
}

?>