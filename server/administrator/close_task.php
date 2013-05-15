<?php
session_start();

ob_start();

include "./common.php";
include "./lib/tasks.php";

$error = '';

$db = Database::getInstance();

moderator_access();

if (@$_GET['id']){
    $task_id = intval($_GET['id']);

    $sql = "select * from moderator_tasks where id=$task_id";
    $rs  = $db->executeQuery($sql);
    $moderator_id = $rs->getValueByName(0, 'to_usr');
    //$moderator_id = $_SESSION['uid'];
    $video_id = $rs->getValueByName(0, 'media_id');
    
    $action = "<a href=\'msgs.php?task=$task_id\'>"._('task done')."</a>";
    $query = "insert into video_log (action, video_id, moderator_id, actiontime) values ('$action', $video_id, $moderator_id, NOW())";
    $db->executeQuery($query);
    
    $sql = "update moderator_tasks set ended=1, end_time=NOW() where id=".$_GET['id'];
    $db->executeQuery($sql);

    $sql  = "select * from video where id=".$video_id;
    $rs   = $db->executeQuery($sql);
    $path = $rs->getValueByName(0, 'path');

    $master = new VideoMaster();
    
    try {
        $master->startMD5SumInAllStorages($path);
        //var_dump('startMD5SumInAllStorages');
    }catch (Exception $exception){

    }
    
    header("Location: tasks.php");
    exit();
}
?>