<?php
include "../lib/func.php";

$db = new Database('stalker_tmp');

$sql = "select * from user_log where action='play_now()'";
$rs = $db->executeQuery($sql);

while(@$rs->next()){
    $arr = $rs->getCurrentValuesAsHash();
    
    $sql2 = "select * from user_log where id>".$arr['id']." and mac='{$arr['mac']}' limit 0,1";
    $rs2   = $db->executeQuery($sql2);
    $next = $rs2->getValueByName(0, 'time');
    $cur_timestamp  = mysql2timestamp($arr['time']);
    $next_timestamp = mysql2timestamp($next);
    $dif_time = $next_timestamp-$cur_timestamp;
    echo "id: ".$arr['id']."\n";
    $update = "update user_log set dif_date=$dif_time where id={$arr['id']}";
    $rs3   = $db->executeQuery($update);
}

function mysql2timestamp($datetime){
    preg_match("/(\d+)-(\d+)-(\d+) (\d+):(\d+):(\d+)/", $datetime, $arr);
    return @mktime($arr[4], $arr[5], $arr[6], $arr[2], $arr[3], $arr[1]);
}
?>