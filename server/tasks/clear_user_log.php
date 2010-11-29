<?php
/*
    
*/
include "../conf_serv.php";
include "../common.php";

$db = new Database(DB_NAME);

$from_ts = time() - 7*24*60*60;
$from_date = date("Y-m-d H:i:s", $from_ts); 

$sql = "delete from user_log where time<'$from_date'";

$rs=$db->executeQuery($sql);

$sql = "optimize table user_log";
$db->executeQuery($sql);

$from_time = date("Y-m-d H:i:s",strtotime ("-1 month"));
$sql = "delete from readed_anec where readed<'$from_time'";
$db->executeQuery($sql);

if ($rs){
    echo 1;
}else{
    echo 0;
}

?>