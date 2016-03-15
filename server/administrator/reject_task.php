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

    Admin::checkAccess(AdminAccess::ACCESS_EDIT);

    $task_id = (int) $_GET['id'];

    $task = Mysql::getInstance()->from('moderator_tasks')->where(array('id' => $task_id))->get()->first();

    if (!empty($task) && $task['ended'] == 0){
        Mysql::getInstance()->update('moderator_tasks',
            array(
                'ended'    => 1,
                'rejected' => 1,
                'end_time' => 'NOW()'
            ),
            array('id' => $task_id));

        Video::log($task['media_id'], '<a href="msgs.php?task='.$task_id.'">'._('task rejected').'</a>');
    }

    if (@$_GET['send_to']){
        header("Location: send_to.php?id=".$_GET['send_to']);
    }else{
        header("Location: tasks.php");
    }
    exit();
}
?>