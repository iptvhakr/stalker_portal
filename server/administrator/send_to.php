<?php
session_start();

ob_start();

include "./common.php";
include "./lib/tasks.php";

$error = '';

$db = new Database();

moderator_access();

if (count($_POST) > 0){
    $sql = "insert into moderator_tasks
                (
                    to_usr,
                    media_type,
                    media_id,
                    start_time
                )
                value
                (
                     {$_POST['to_usr']},
                     2,
                     {$_POST['id']},
                     NOW()
                )
                ";
    $rs=$db->executeQuery($sql);
    
    $task_id = $rs->getLastInsertId();
    
    $sql = "insert into moderators_history
                (
                    task_id,
                    from_usr,
                    to_usr,
                    comment,
                    send_time
                )
                value
                (
                     $task_id,
                     {$_SESSION['uid']},
                     {$_POST['to_usr']},
                    '{$_POST['comment']}',
                     NOW()
                )
                ";
    $rs=$db->executeQuery($sql);
    if (!$db->getLastError()){
        js_redirect('add_video.php', 'задание отправлено');
    }else{
        echo 'ошибка';
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
<title>Отправка ВИДЕО</title>
</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0">
<tr>
    <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;Отправка ВИДЕО&nbsp;</b></font>
    </td>
</tr>
<tr>
    <td width="100%" align="left" valign="bottom">
        <a href="index.php"><< Назад</a>
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
    global $db;
    $opt = '';
    
    $sql = "select * from administrators where access=1 or access=2";
    $rs=$db->executeQuery($sql);
    while(@$rs->next()){
        $arr=$rs->getCurrentValuesAsHash();
        $opt .= "<option value={$arr['id']}>{$arr['login']}\n";
    }
    return $opt;
}

function get_sended_video(){
    global $db;
    $id = @$_GET['id'];
    
    $sql = "select * from video where id=$id";
    $rs=$db->executeQuery($sql);
    
    $name = $rs->getValueByName(0, 'name');
    return $name;
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
    <td valign="top">Видео:</td>
    <td>
        <input type="text" size="32" readonly value="<?echo get_sended_video()?>">
    </td>
</tr>
<tr>
    <td>Кому:</td>
    <td>
    <select name="to_usr">
        <option>- - - - - - - - - - - - - 
        <? echo get_moderators() ?>
    </select>
    <input type="hidden" name="id" value="<?echo @$_GET['id']?>">
    </td>
</tr>
<tr>
    <td valign="top">Примечание:</td>
    <td>
        <textarea name="comment" cols="30" rows="8"></textarea>
    </td>
</tr>
<tr>
    <td></td>
    <td>
        <input type="submit" value="Отправить">
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
