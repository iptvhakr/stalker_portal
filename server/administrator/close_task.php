<?php
session_start();

ob_start();

include "./common.php";
include "./lib/tasks.php";

use Stalker\Lib\Core\Mysql;

$error = '';

Admin::checkAuth();

Admin::checkAccess(AdminAccess::ACCESS_VIEW);

if (@$_GET['id']){
    $task_id = intval($_GET['id']);

    Admin::checkAccess(AdminAccess::ACCESS_EDIT);

    $task = Mysql::getInstance()->from('moderator_tasks')->where(array('id' => $task_id))->get()->first();

    $moderator_id = $task['to_usr'];
    $video_id     = $task['media_id'];
    
    $action = "<a href=\'msgs.php?task=$task_id\'>"._('task done')."</a>";

    Mysql::getInstance()->insert('video_log', array(
        'action'       => $action,
        'video_id'     => $video_id,
        'moderator_id' => $moderator_id,
        'actiontime'   => 'NOW()'
    ));

    Mysql::getInstance()->update('moderator_tasks', array('ended' => 1, 'end_time' => 'NOW()'), array('id' => intval($_GET['id'])));

    $video = Video::getById($video_id);
    $path = $video['path'];

    $master = new VideoMaster();
    
    try {
        $master->startMD5SumInAllStorages($path);
    }catch (Exception $exception){

    }
    
    header("Location: tasks.php");
    exit();
}
?>