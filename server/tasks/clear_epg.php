<?php
/*
    
*/
include "./common.php";

$db = new Database();

$from_ts = time() - 7*24*60*60;
$from_date = date("Y-m-d H:i:s", $from_ts); 

$sql = "delete from epg where time<'$from_date'";

$rs=$db->executeQuery($sql);

$sql = "optimize table epg";
$db->executeQuery($sql);

if ($rs){
    echo 1;
}else{
    echo 0;
}

?>