<?php
session_start();

ob_start();

include "../conf_serv.php";
include "../common.php";
include "../lib/func.php";

$error = '';
$action_name = 'add';
$action_value = 'Добавить';

$db = new Database(DB_NAME);

moderator_access();

if (@$_SESSION['login'] != 'alex' && @$_SESSION['login'] != 'duda'  && !check_access()){ 
    exit;
}

foreach (@$_POST as $key => $value){
    $_POST[$key] = trim($value);
}

if (!empty($_POST['change_image_version']) && isset($_POST['image_version'])){
    $db->executeQuery('alter table users modify image_version varchar(64) not null default "'.$_POST['image_version'].'"');
    $db->executeQuery('update users set image_version="'.$_POST['image_version'].'"');
}

$first_user = $db->executeQuery('select * from users limit 0,1')->getAllValues();
$image_version = $first_user[0]['image_version'];

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
<title>Общие настройки</title>
</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0" width="640">
<tr>
    <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;Общие настройки&nbsp;</b></font>
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
    <td align="center">
        <form method="POST">
            <table class="form">
                <tr>
                    <td>Необходимый образ NAND</td>
                    <td><input type="text" name="image_version" value="<?echo $image_version?>"></input></td>
                    <td><sup>*для MAG200</sup></td>
                </tr>
                <tr>
                    <td></td>
                    <td><input type="submit" name="change_image_version" value="Сохранить"></input></td>
                    <td></td>
                </tr>
            </table>
        </form>
    </td>
</tr>
</table>