<?php
session_start();

ob_start();

include "../common.php";
include "../lib/func.php";

$error = '';
$action_name = 'add';
$action_value = 'Добавить';

moderator_access();

if (@$_SESSION['login'] != 'alex' && @$_SESSION['login'] != 'duda' && !check_access()){ 
    exit;
}

foreach (@$_POST as $key => $value){
    $_POST[$key] = trim($value);
}

$stb_groups = new StbGroup();
    
if (@$_POST['add']){
    
    $stb_groups->add($_POST['name']);
    
    header("Location: stbgroups.php");
}

$id = @intval($_GET['id']);

if (!empty($id)){
    
    if (@$_POST['edit']){
        $stb_groups->set(array('name' => $_POST['name']), $_GET['id']);
        
        header("Location: stbgroups.php");
    }elseif (@$_GET['del']){
        
        $stb_groups->del($id);
        
        header("Location: stbgroups.php");
    }
}

if (@$_GET['edit'] && !empty($id)){
    $action_name = 'edit';
    $action_value = 'Сохранить';
    $edit_group = $stb_groups->getById($id);
}

$groups = $stb_groups->getAll();

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
<title>Группы приставок</title>
</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0">
<tr>
    <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;Группы приставок&nbsp;</b></font>
    </td>
</tr>
<tr>
    <td width="100%" align="left" valign="bottom">
        <a href="users.php"><< Назад</a>
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
            <td>&nbsp;</td>
        </tr>
        <? foreach ($groups as $group){
                echo '<tr>';
                echo '<td>'.$group['id'].'</td>';
                echo '<td><a href="stbgroup_members.php?group_id='.$group['id'].'">'.$group['name'].'</a></td>';
                echo '<td>';
                
                echo '<a href="?edit=1&id='.$group['id'].'">edit</a>&nbsp;';
                echo '<a href="?del=1&id='.$group['id'].'" onclick="if(confirm(\'Вы действительно хотите удалить группу '.$group['name'].' из базы?\')){return true}else{return false}">del</a>';
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
                    <td><input type="text" name="name" value="<?echo @$edit_group['name']?>"></input></td>
                </tr>
                <tr>
                    <td></td>
                    <td><input type="submit" name="<? echo $action_name ?>" value="<? echo $action_value?>"></input></td>
                </tr>
            </table>
        </form>
    </td>
</tr>
</table>