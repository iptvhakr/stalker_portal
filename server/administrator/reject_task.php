<?php
session_start();

ob_start();

include "../conf_serv.php";
include "../common.php";
include "../getid3/getid3.php";
include "../lib/func.php";
include "./lib/tasks.php";

$error = '';

$db = new Database(DB_NAME);

moderator_access();

if (@$_GET['id']){
    $sql = "update moderator_tasks set ended=1, rejected=1, end_time=NOW() where id=".$_GET['id'];
    $rs=$db->executeQuery($sql);
    if (@$_GET['send_to']){
        header("Location: send_to.php?id=".$_GET['send_to']);
    }else{
        header("Location: tasks.php");
    }
    exit();
}
?>