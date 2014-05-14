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

    $task_id = Mysql::getInstance()->insert('moderator_tasks', array(
        'to_usr'     => $_POST['to_usr'],
        'media_type' => 2,
        'media_id'   => $_POST['id'],
        'start_time' => 'NOW()'
    ))->insert_id();

    Mysql::getInstance()->insert('moderators_history', array(
        'task_id'   => $task_id,
        'from_usr'  => $_SESSION['uid'],
        'to_usr'    => $_POST['to_usr'],
        'comment'   => $_POST['comment'],
        'send_time' => 'NOW()'
    ));

    Video::log((int) $_POST['id'], '<a href="msgs.php?task='.$task_id.'">'._('task open').'</a>', (int) $_POST['to_usr']);

    if ($task_id){
        js_redirect('add_video.php', _('the task has been sent'));
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
<title><?= _('Create task')?></title>
</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0">
<tr>
    <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;<?= _('Create task')?>&nbsp;</b></font>
    </td>
</tr>
<tr>
    <td width="100%" align="left" valign="bottom">
        <a href="index.php"><< <?= _('Back')?></a>
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
function get_moderators(){

    $opt = '';

    $moderators = Mysql::getInstance()->from('administrators')->get();

    while($arr = $moderators->next()){
        $opt .= "<option value={$arr['id']}>{$arr['login']}\n";
    }
    return $opt;
}

function get_sended_video(){

    $video = Video::getById(intval($_GET['id']));

    return $video['name'];
}
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
        <input type="text" size="32" readonly value="<?echo get_sended_video()?>">
    </td>
</tr>
<tr>
    <td><?= _('To')?>:</td>
    <td>
    <select name="to_usr">
        <option>- - - - - - - - - - - - - 
        <? echo get_moderators() ?>
    </select>
    <input type="hidden" name="id" value="<?echo @$_GET['id']?>">
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
