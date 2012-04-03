<?php
session_start();

ob_start();

include "./common.php";

$error = '';
$action_name = 'add';
$action_value = 'Добавить';

$db = new Database();

moderator_access();

if (@$_SESSION['login'] != 'alex' && @$_SESSION['login'] != 'duda' && @$_SESSION['login'] != 'azmus' && @$_SESSION['login'] != 'vitaxa' && !check_access()){
    exit;
}

foreach (@$_POST as $key => $value){
    $_POST[$key] = trim($value);
}
    
if (@$_POST['add']){
    $sql = 'insert into epg_setting (
                uri
                ) 
            values (
                "'.@$_POST['uri'].'"
            )';
    $db->executeQuery($sql);
    header("Location: epg_setting.php");
}

$id = @intval($_GET['id']);

if (!empty($id)){
    
    if (@$_POST['edit']){
        $sql = 'update epg_setting set uri="'.@$_POST['uri'].'" where id='.intval($_GET['id']);
        $db->executeQuery($sql);
        header("Location: epg_setting.php");
    }elseif (@$_GET['del']){
        $sql = 'delete from epg_setting where id='.intval($_GET['id']);
        $db->executeQuery($sql);
        header("Location: epg_setting.php");
    }
}

if (@$_GET['edit'] && !empty($id)){
    $action_name = 'edit';
    $action_value = 'Сохранить';
    $edit = $db->executeQuery('select * from epg_setting where id='.$id)->getAllValues();
    $edit = @$edit[0];
}

if (isset($_GET['update_epg'])){
    $epg = new Epg();
    
    if (isset($_GET['force'])){
        $force = true;
    }else{
        $force = false;
    }
    
    $error = $epg->updateEpg($force);
}

$settings = $db->executeQuery('select * from epg_setting')->getAllValues();

$debug = '<!--'.ob_get_contents().'-->';
ob_clean();
echo $debug;
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
.list, .list td, .form{
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
<title>Настройки EPG</title>
</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0">
<tr>
    <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;Настройки EPG&nbsp;</b></font>
    </td>
</tr>
<tr>
    <td width="100%" align="left" valign="bottom">
        <a href="index.php"><< Назад</a> | <a href="?update_epg">Обновить EPG</a> | <a href="?update_epg&force">Принудительно обновить EPG</a>
    </td>
</tr>
<tr>
    <td align="center">
    <font color="Red">
    <strong>
    <pre>
    <? echo $error?>
    </pre>
    </strong>
    </font>
    <br>
    <br>
    </td>
</tr>
<tr>
<td align="center">
    <table class='list' cellpadding='3' cellspacing='0'>
        <tr>
            <td>ID</td>
            <td>URI</td>
            <td>ETag/MD5</td>
            <td>Обновлен</td>
            <td>&nbsp;</td>
        </tr>
        <? foreach ($settings as $setting){
                echo '<tr>';
                echo '<td>'.$setting['id'].'</td>';
                echo '<td>'.$setting['uri'].'</td>';
                echo '<td>'.$setting['etag'].'</td>';
                echo '<td>'.$setting['updated'].'</td>';
                echo '<td>';
                /*if ($storage['status'] == 1){
                    $status_str = 'on';
                    $color = 'Green';
                    $new_status=0;
                }else{
                    $status_str = 'off';
                    $color = 'Red';
                    $new_status=1;
                }
                echo '<a href="?status='.$new_status.'&id='.$storage['id'].'" style="color:'.$color.'" onclick="if(confirm(\'Вы действительно хотите изменить статус хранилища '.$storage['storage_name'].'?\')){return true}else{return false}">'.$status_str.'</a>&nbsp;';
                echo '<a href="?reset_cache=1&id='.$storage['id'].'" style="color:grey" onclick="if(confirm(\'Вы действительно хотите сбросить кеш хранилища '.$storage['storage_name'].'?\')){return true}else{return false}">сбросить кеш</a>&nbsp;';*/
                echo '<a href="?edit=1&id='.$setting['id'].'">edit</a>&nbsp;';
                echo '<a href="?del=1&id='.$setting['id'].'" onclick="if(confirm(\'Вы действительно хотите удалить настройку из базы удалить?\')){return true}else{return false}">del</a>';
                echo '</td>';
                echo '</tr>';
           }?>
    </table>
</td>
</tr>
<tr>
    <td align="center">
<br>
<br>
        <form method="POST">
            <table class="form">
                <tr>
                    <td>URI</td>
                    <td><input type="text" name="uri" value="<?echo @$edit['uri']?>" size="50"/></td>
                </tr>
                <tr>
                    <td></td>
                    <td><input type="submit" name="<? echo $action_name ?>" value="<? echo $action_value?>"/></td>
                </tr>
            </table>
        </form>
    </td>
</tr>
</table>