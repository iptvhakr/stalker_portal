<?php
session_start();

ob_start();

include "./common.php";
include "./lib/tasks.php";

$error = '';

Admin::checkAuth();

Admin::checkAccess(AdminAccess::ACCESS_VIEW);

if (count($_POST) > 0){

    Admin::checkAccess(AdminAccess::ACCESS_CREATE);

    $hist_id = Mysql::getInstance()->insert('moderators_history',array(
        'task_id'   => $_POST['task_id'],
        'from_usr'  => $_SESSION['uid'],
        'to_usr'    => $_POST['to_usr'],
        'comment'   => $_POST['comment'],
        'send_time' => 'NOW()',
        'reply_to'  => $_POST['reply_to']
    ))->insert_id();

    if ($hist_id){
        js_redirect('tasks.php', _('message sended'));
    }else{
        echo 'error';
    }
    exit;
}

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
</style>
<title><?= _('Send message')?></title>
</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0">
<tr>
    <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;<?= _('Send message')?>&nbsp;</b></font>
    </td>
</tr>
<tr>
    <td width="100%" align="left" valign="bottom">
        <a href="tasks.php"><< <?= _('Back')?></a>
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
$reply_to = @$_GET['id'];

$task_id = get_task_id_by_msg_id($reply_to);
$media_name = get_media_name_by_task_id($task_id);

$to_id = Mysql::getInstance()->from('moderators_history')->where(array('id' => $reply_to))->get()->first('from_usr');

$to = get_moderator_login_by_id($to_id);


?>
<table border="0" align="center" width="620">
<tr>
<td align="center"><br>
<br>
<br>

<form method="POST">
<table border="0">
<tr>
    <td valign="top"><?= _('Movie')?>:</td>
    <td>
        <?echo $media_name?>
    </td>
</tr>
<tr>
    <td><?= _('To')?>:</td>
    <td>
        <?echo $to?>
        <input type="hidden" name="to_usr" value="<? echo $to_id?>">
        <input type="hidden" name="task_id" value="<? echo $task_id?>">
        <input type="hidden" name="reply_to" value="<? echo $reply_to?>">
    </td>
</tr>
<tr>
    <td valign="top"><?= _('Comment')?>:</td>
    <td>
        <textarea name="comment" cols="30" rows="8"></textarea>
    </td>
</tr>
<tr>
    <td></td>
    <td>
        <input type="submit" value="<?= htmlspecialchars(_('Send'), ENT_QUOTES)?>">
    </td>
</tr>

</table>
</form>
<td>
</tr>
</table>

</td>
</tr>
</table>
