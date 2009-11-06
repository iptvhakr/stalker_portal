<?php

include "../conf_serv.php";
include "../lib/func.php";

set_time_limit(0);

$db = new Database(DB_NAME);

$sql = "select * from moderator_tasks where ended=1";
$rs=$db->executeQuery($sql);

$sub_query = '';

while(@$rs->next()){
    $arr = $rs->getCurrentValuesAsHash();
    
    $task_id      = $arr['id'];
    $video_id     = $arr['media_id'];
    $moderator_id = $arr['to_usr'];
    $actiontime   = $arr['end_time'];
    
    $action = "<a href=\'msgs.php?task=$task_id\'>выполнено задание</a>";
    
    if ($sub_query){
        $sub_query .= ", ";
    }
    
    $sub_query .= " ('$action', $video_id, $moderator_id, '$actiontime')"; 
    
}

$query = "insert into video_log (action, video_id, moderator_id, actiontime) values $sub_query";
//$db->executeQuery($query);
echo $query;
?>