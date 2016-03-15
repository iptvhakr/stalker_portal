<?php
session_start();

ob_start();

include "./common.php";
include "./lib/tasks.php";

use Stalker\Lib\Core\Mysql;

$error = '';

Admin::checkAuth();

Admin::checkAccess(AdminAccess::ACCESS_VIEW);

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
<title><?= _("View message")?></title>
</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0">
<tr>
    <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;<?= _("View message")?>&nbsp;</b></font>
    </td>
</tr>
<tr>
    <td width="100%" align="left" valign="bottom">
        <a href="<? echo $_SERVER['HTTP_REFERER'] ?>"><< <?= _('Back')?></a>
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

$history = Mysql::getInstance()->from('moderators_history')->where(array('id' => $id))->get()->first();

$id         = $history['id'];
$send_time  = $history['send_time'];
$task_id    = $history['task_id'];
$media_name = get_media_name_by_task_id($task_id);
$from_usr   = $history['from_usr'];
$from       = get_moderator_login_by_id($from_usr);
$to_usr     = $history['to_usr'];
$msg        = $history['comment'];
$reply_to   = $history['reply_to'];

if ($reply_to){

    $reply_to_msg = Mysql::getInstance()
        ->from('moderators_history')
        ->where(array(
            'id' => $reply_to
        ))
        ->get()
        ->first('comment');

    $msg = ">".$reply_to_msg."<br/><br/>".$msg;
}

if ($to_usr == @$_SESSION['uid']){

    Mysql::getInstance()->update('moderators_history',
        array(
            'readed'    => 1,
            'read_time' => 'NOW()'
        ),
        array('id' => $id)
    );
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
    <td><a href="reply.php?id=<?echo $id?>"><?= _('Reply')?></a></td>
<?
}
?>
</tr>
</table>
<table width="100%" border="1" cellspacing="0">
<tr>
    <td><?= _('Date')?></td>
    <td><? echo $send_time?></td>
</tr>
<tr>
    <td><?= _('Media')?></td>
    <td><? echo $media_name?></td>
</tr>
<tr>
    <td><?= _('From')?></td>
    <td><? echo $from?></td>
</tr>
<tr>
    <td colspan="2"><? echo $msg?></td>
</tr>
</table>
<?
if (Admin::isPageActionAllowed()){
?>
<table width="100%" border="0" cellspacing="0">
<tr>
    <td width="100%" align="right">
    <a href="#" onclick='if(confirm("<?= _('Are you sure you want to close this task?')?>")){document.location="close_task.php?id=<?echo $task_id?>"}'><?= _('Task accomplished')?></a><br>
    <a href="#" onclick='if(confirm("<?= _('Are you sure you want to reject this task?')?>")){document.location="reject_task.php?id=<?echo $task_id?>"}'><?= _('Task rejected')?></a>
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