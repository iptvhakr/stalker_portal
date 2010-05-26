<?php
session_start();

ob_start();

include "../common.php";
include "../conf_serv.php";
include "../getid3/getid3.php";
include "../lib/func.php";
include "./lib/tasks.php";

$error = '';

$db = Database::getInstance(DB_NAME);

moderator_access();

if (@$_GET['id']){
    $task_id = intval($_GET['id']);
    
    $sql = "select * from moderator_tasks where id=$task_id";
    $rs  = $db->executeQuery($sql);
    $moderator_id = $rs->getValueByName(0, 'to_usr');
    $video_id = $rs->getValueByName(0, 'media_id');
    
    $action = "<a href=\'msgs.php?task=$task_id\'>выполнено задание</a>";
    $query = "insert into video_log (action, video_id, moderator_id, actiontime) values ('$action', $video_id, $moderator_id, NOW())";
    $db->executeQuery($query);
    
    $sql = "update moderator_tasks set ended=1, end_time=NOW() where id=".$_GET['id'];
    $db->executeQuery($sql);
    
    header("Location: tasks.php");
    exit();
}
?>