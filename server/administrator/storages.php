<?php
session_start();

ob_start();

include "../common.php";
include "../lib/func.php";

$error = '';
$action_name = 'add';
$action_value = 'Добавить';

$db = new Database();

moderator_access();

if (@$_SESSION['login'] != 'alex' && @$_SESSION['login'] != 'duda' && !check_access()){ 
    exit;
}

foreach (@$_POST as $key => $value){
    $_POST[$key] = trim($value);
}
    
if (@$_POST['add']){
    $sql = 'insert into storages (
                storage_name, 
                storage_ip, 
                nfs_home_path, 
                max_online,
                for_moderator,
                for_records,
                external,
                for_simple_storage
                )
            values (
                "'.@$_POST['storage_name'].'",
                "'.@$_POST['storage_ip'].'",
                "'.@$_POST['nfs_home_path'].'",
                "'.@$_POST['max_online'].'",
                "'.@intval($_POST['for_moderator']).'",
                "'.@intval($_POST['for_records']).'",
                "'.@intval($_POST['external']).'",
                "'.@intval($_POST['for_simple_storage']).'"
            )';
    $db->executeQuery($sql);
    header("Location: storages.php");
}

$id = @intval($_GET['id']);

if (!empty($id)){
    
    if (@$_POST['edit']){
        $sql = 'update storages set
                    storage_name="'.@$_POST['storage_name'].'",
                    storage_ip="'.@$_POST['storage_ip'].'",
                    nfs_home_path="'.@$_POST['nfs_home_path'].'",
                    max_online="'.@$_POST['max_online'].'",
                    for_moderator="'.@intval($_POST['for_moderator']).'",
                    for_records="'.@intval($_POST['for_records']).'",
                    external="'.@intval($_POST['external']).'",
                    for_simple_storage="'.@intval($_POST['for_simple_storage']).'"
                where id='.intval($_GET['id']);
        $db->executeQuery($sql);
        header("Location: storages.php");
    }elseif (@$_GET['del']){
        $sql = 'delete from storages where id='.intval($_GET['id']);
        $db->executeQuery($sql);
        header("Location: storages.php");
    }elseif (isset($_GET['status'])){
        $new_status = $_GET['status'];
        $sql = 'update storages set status='.$new_status.' where id='.intval($_GET['id']);
        $db->executeQuery($sql);
        header("Location: storages.php");
    }
}

if (@$_GET['edit'] && !empty($id)){
    $action_name = 'edit';
    $action_value = 'Сохранить';
    $edit_storage = $db->executeQuery('select * from storages where id='.$id)->getAllValues();
    $edit_storage = @$edit_storage[0];
}

if (@$_GET['reset_cache'] && !empty($id)){
    $storage_name = $db->executeQuery('select * from storages where id='.$id)->getAllValues();
    $storage_name = @$storage_name[0]['storage_name'];
    if (!empty($storage_name)){
        $db->executeQuery('update storage_cache set changed="0000-00-00 00:00:00" where storage_name="'.$storage_name.'"');
        if (!$db->getLastError()){
            $error = 'Кеш '.$storage_name.' сброшен';
        }else{
            $error = $db->getLastError();
        }
    }else{
        $error = 'Хранилище с ID '.$id.' в базе отсутствует';
    }
}

if (@$_GET['reset_all_cache']){
    $db->executeQuery('update storage_cache set changed="0000-00-00 00:00:00"');
    if (!$db->getLastError()){
        $error = 'Кеш сброшен';
    }else{
        $error = $db->getLastError();
    }
}

$storages = $db->executeQuery('select * from storages')->getAllValues();

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
<title>Настройки параметров хранилищ</title>
</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0">
<tr>
    <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;Настройки параметров хранилищ&nbsp;</b></font>
    </td>
</tr>
<tr>
    <td width="100%" align="left" valign="bottom">
        <a href="index.php"><< Назад</a> | <a href="master_log.php">Логи</a> | <a href="?reset_all_cache=1" onclick="if(confirm('Вы действительно хотите сбросить весь кеш?')){return true}else{return false}">Сбросить кеш</a> | <a href="cache_refresh.php" onclick="if(confirm('Вы действительно хотите обновить весь кеш? Эта операция займет какое-то время.')){return true}else{return false}">Обновить кеш</a> | <a href="unique_video.php">Уникальные фильмы</a> | <a href="video_search.php">Поиск по хранилищам</a>
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
<td align="center">
    <table class='list' cellpadding='3' cellspacing='0'>
        <tr>
            <td>ID</td>
            <td>Имя</td>
            <td>IP</td>
            <td>Домашняя директория</td>
            <td>Max online</td>
            <td>&nbsp;</td>
        </tr>
        <? foreach ($storages as $storage){
                echo '<tr ';
                
                if ($storage['for_moderator']){
                    echo 'style="background-color: #ffecec;"';
                }
                
                echo '>';
                echo '<td>'.$storage['id'].'</td>';
                echo '<td>'.$storage['storage_name'].'</td>';
                echo '<td>'.$storage['storage_ip'].'</td>';
                echo '<td>'.$storage['nfs_home_path'].'</td>';
                echo '<td>'.$storage['max_online'].'</td>';
                echo '<td>';
                if ($storage['status'] == 1){
                    $status_str = 'on';
                    $color = 'Green';
                    $new_status=0;
                }else{
                    $status_str = 'off';
                    $color = 'Red';
                    $new_status=1;
                }
                echo '<a href="?status='.$new_status.'&id='.$storage['id'].'" style="color:'.$color.'" onclick="if(confirm(\'Вы действительно хотите изменить статус хранилища '.$storage['storage_name'].'?\')){return true}else{return false}">'.$status_str.'</a>&nbsp;';
                echo '<a href="?reset_cache=1&id='.$storage['id'].'" style="color:grey" onclick="if(confirm(\'Вы действительно хотите сбросить кеш хранилища '.$storage['storage_name'].'?\')){return true}else{return false}">сбросить кеш</a>&nbsp;';
                echo '<a href="?edit=1&id='.$storage['id'].'">edit</a>&nbsp;';
                echo '<a href="?del=1&id='.$storage['id'].'" onclick="if(confirm(\'Вы действительно хотите удалить хранилище '.$storage['storage_name'].' из базы?\')){return true}else{return false}">del</a>';
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
                    <td>Имя</td>
                    <td><input type="text" name="storage_name" value="<?echo @$edit_storage['storage_name']?>"/></td>
                </tr>
                <tr>
                    <td>IP</td>
                    <td><input type="text" name="storage_ip" value="<?echo @$edit_storage['storage_ip']?>"/></td>
                </tr>
                <tr>
                    <td>Домашняя директория</td>
                    <td><input type="text" name="nfs_home_path" value="<?echo @$edit_storage['nfs_home_path']?>"/></td>
                </tr>
                <tr>
                    <td>Max online</td>
                    <td><input type="text" name="max_online" value="<?echo @$edit_storage['max_online']?>"/></td>
                </tr>
                <tr>
                    <td>Хранение контента</td>
                    <td><input type="checkbox" name="for_simple_storage" value="1" <? if(@$edit_storage['for_simple_storage']){ echo 'checked="checked"'; } ?>/></td>
                </tr>
                <tr>
                    <td>Запись ТВ</td>
                    <td><input type="checkbox" name="for_records" value="1" <? if(@$edit_storage['for_records']){ echo 'checked="checked"'; } ?>/></td>
                </tr>
                <tr>
                    <td>Внешнее</td>
                    <td><input type="checkbox" name="external" value="1" <? if(@$edit_storage['external']){ echo 'checked="checked"'; } ?>/></td>
                </tr>
                <tr>
                    <td>Только модераторы</td>
                    <td><input type="checkbox" name="for_moderator" value="1" <? if(@$edit_storage['for_moderator']){ echo 'checked="checked"'; } ?>/></td>
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