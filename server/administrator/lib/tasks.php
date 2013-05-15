<?php
function get_media_name_by_id($id){
    global $db;
    
    $sql = "select * from video where id=$id";
    $rs=$db->executeQuery($sql);
    
    $name = $rs->getValueByName(0, 'name');
    return $name;
}

function get_count_all_msgs($task_id){
    global $db;
    $uid = $_SESSION['uid'];
    
    $sql = "select count(*) as counter from moderators_history where task_id=$task_id and to_usr=$uid";
    $rs=$db->executeQuery($sql);
    $counter = $rs->getValueByName(0, 'counter');
    return $counter;
}

function get_count_unreaded_msgs($task_id){
    global $db;
    $uid = $_SESSION['uid'];
    
    $sql = "select count(*) as counter from moderators_history where task_id=$task_id and to_usr=$uid and readed=0";
    $rs=$db->executeQuery($sql);
    $counter = $rs->getValueByName(0, 'counter');
    return $counter;
}

function get_count_unreaded_msgs_by_uid(){
    global $db;
    $uid = $_SESSION['uid'];
    
    $sql = "select count(moderators_history.id) as counter from moderators_history,moderator_tasks where moderators_history.task_id = moderator_tasks.id and moderators_history.to_usr=$uid and moderators_history.readed=0 and moderator_tasks.archived=0 and moderator_tasks.ended=0";
    $rs=$db->executeQuery($sql);
    $counter = $rs->getValueByName(0, 'counter');
    return $counter;
}

function get_media_name_by_task_id($task_id){
    global $db;
    
    $sql = "select video.name as name from moderator_tasks, video where video.id=moderator_tasks.media_id and moderator_tasks.id=$task_id";
    $rs=$db->executeQuery($sql);
    $name = $rs->getValueByName(0, 'name');
    return $name;
}

function get_media_length_by_id($id){
    global $db;
    
    $sql = "select time from video where id=$id";
    $rs=$db->executeQuery($sql);
    $time = intval($rs->getValueByName(0, 'time'));
    return $time;
}

function get_moderator_login_by_id($id){
    global $db;
    
    $sql = "select * from administrators where id=$id";
    $rs=$db->executeQuery($sql);
    $name = $rs->getValueByName(0, 'login');
    return $name;
}

function get_task_id_by_msg_id($id){
    global $db;
    
    $sql = "select * from moderators_history where id=$id";
    $rs=$db->executeQuery($sql);
    $task_id = $rs->getValueByName(0, 'task_id');
    return $task_id;
}

function is_answered($task_id){
    global $db;
    
    $uid = @$_SESSION['uid'];
    $sql = "select * from moderators_history where task_id=$task_id && to_usr!=from_usr order by id desc limit 0,1;";
    $rs=$db->executeQuery($sql);
    $from_usr = $rs->getValueByName(0, 'from_usr');
    
    if ($from_usr == $uid){
        return 1;
    }else{
        return 0;
    }
}

?>