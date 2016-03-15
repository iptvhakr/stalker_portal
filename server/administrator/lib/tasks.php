<?php

use Stalker\Lib\Core\Mysql;

function get_media_name_by_id($id){

    $video = Video::getById($id);
    return $video['name'];
}

function get_count_all_msgs($task_id){

    return Mysql::getInstance()
        ->count()
        ->from('moderators_history')
        ->where(array(
            'task_id' => $task_id,
            'to_usr'  => $_SESSION['uid']
        ))
        ->get()
        ->counter();
}

function get_count_unreaded_msgs($task_id){

    return Mysql::getInstance()
        ->count()
        ->from('moderators_history')
        ->where(array(
            'task_id' => $task_id,
            'to_usr'  => $_SESSION['uid'],
            'readed'  => 0
        ))
        ->get()
        ->counter();
}

function get_count_unreaded_msgs_by_uid(){

    $uid = $_SESSION['uid'];
    
    $sql = "select count(moderators_history.id) as counter from moderators_history,moderator_tasks where moderators_history.task_id = moderator_tasks.id and moderators_history.to_usr=$uid and moderators_history.readed=0 and moderator_tasks.archived=0 and moderator_tasks.ended=0";

    return Mysql::getInstance()->query($sql)->first('counter');
}

function get_media_name_by_task_id($task_id){
    
    $video = Mysql::getInstance()->query("select video.name as name from moderator_tasks, video where video.id=moderator_tasks.media_id and moderator_tasks.id=$task_id")->first();
    return $video['name'];
}

function get_media_length_by_id($id){

    $video = Video::getById($id);
    return $video['time'];
}

function get_moderator_login_by_id($id){

    $admin = Mysql::getInstance()->from('administrators')->where(array('id' => $id))->get()->first();
    return $admin['login'];
}

function get_task_id_by_msg_id($id){

    return Mysql::getInstance()->from('moderators_history')->where(array('id' => $id))->get()->first('task_id');
}

function is_answered($task_id){

    $uid = (int) $_SESSION['uid'];

    $sql = "select * from moderators_history where task_id=$task_id && to_usr!=from_usr order by id desc limit 0,1;";

    $from_usr = Mysql::getInstance()->query($sql)->first('from_usr');

    return $from_usr == $uid;
}
