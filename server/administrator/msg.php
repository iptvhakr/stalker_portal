<?php
session_start();

ob_start();

include "../common.php";
include "../lib/func.php";
include "./lib/tasks.php";

$error = '';

$db = new Database();

moderator_access();
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<style type="text/css">

body {
    font-family: Arial, Helvetica, sans-serif;
    font-weight: bold;
}
td {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 14px;
    text-decoration: none;
    color: #000000;
}
.list{
    border-width: 1px;
    border-style: solid;
    border-color: #E5E5E5;
}
a{
	color:#0000FF;
	font-weight: bold;
	text-decoration:none;
}
a:link,a:visited {
	color:#5588FF;
	font-weight: bold;
}
a:hover{
	color:#0000FF;
	font-weight: bold;
	text-decoration:underline;
}
a.msgs:hover, a.msgs:visited, a.msgs:link{
	font-family: Arial, Helvetica, sans-serif;
	font-size: 14px;
    color:#000000;
	font-weight: bold;
	text-decoration:none;
}
</style>
<title>Просмотр сообщения</title>
</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0">
<tr>
    <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;Просмотр сообщения&nbsp;</b></font>
    </td>
</tr>
<tr>
    <td width="100%" align="left" valign="bottom">
        <a href="<? echo $_SERVER['HTTP_REFERER'] ?>"><< Назад</a>
    </td>
</tr>
<tr>
    <td align="center">
    <font color="Red">
    <strong>
    <? echo $error?>
    </strong>
    </font>
    <br>
    <br>
    </td>
</tr>
<tr>
<td>
<?
$id = @$_GET['id'];

$sql = "select * from moderators_history where id=$id";
//echo $sql;
$rs=$db->executeQuery($sql);

$id = $rs->getValueByName(0, 'id');
$send_time = $rs->getValueByName(0, 'send_time');
$task_id = $rs->getValueByName(0, 'task_id');
$media_name = get_media_name_by_task_id($task_id);
$from_usr = $rs->getValueByName(0, 'from_usr');
$from = get_moderator_login_by_id($from_usr);
$to_usr = $rs->getValueByName(0, 'to_usr');
$msg = $rs->getValueByName(0, 'comment');

$reply_to = $rs->getValueByName(0, 'reply_to');

if ($reply_to){
    $sql = "select * from moderators_history where id=$reply_to";
    $rs=$db->executeQuery($sql);
    $reply_to_msg = $rs->getValueByName(0, 'comment');

    $msg = ">".$reply_to_msg."<br/><br/>".$msg;
}

if ($to_usr == @$_SESSION['uid']){
    $sql = "update moderators_history set readed=1, read_time=NOW() where id=$id";
    //echo $sql;
    $rs=$db->executeQuery($sql);
}
?>
<table border="0" align="center" width="620">
<tr>
<td align="center"><br>
<br>
<br>
<table width="100%" border="0" cellspacing="0">
<tr>
<?
if($from_usr != @$_SESSION['uid']){?>
    <td><a href="reply.php?id=<?echo $id?>">Ответить</a></td>
<?
}
?>
</tr>
</table>
<table width="100%" border="1" cellspacing="0">
<tr>
    <td>Дата</td>
    <td><? echo $send_time?></td>
</tr>
<tr>
    <td>Медиа</td>
    <td><? echo $media_name?></td>
</tr>
<tr>
    <td>От кого</td>
    <td><? echo $from?></td>
</tr>
<tr>
    <td colspan="2"><? echo $msg?></td>
</tr>
</table>
<?
if (check_access(array(1))){
?>
<table width="100%" border="0" cellspacing="0">
<tr>
    <td width="100%" align="right">
    <a href="#" onclick='if(confirm("Вы действительно хотите закрыть задание?")){document.location="close_task.php?id=<?echo $task_id?>"}'>Задание выполнено</a><br>
    <a href="#" onclick='if(confirm("Вы действительно хотите отклонить задание?")){document.location="reject_task.php?id=<?echo $task_id?>"}'>Задание отклонено</a>
    </td>
</tr>
</table>
<?
}
?>
<td>
</tr>
</table>

</td>
</tr>
</table>