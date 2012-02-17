<?php
session_start();

ob_start();

include "../common.php";
include "../lib/func.php";

$error = '';
$action_name = 'add';
$action_value = 'Добавить';

/*$db = new Database();*/

moderator_access();

if (@$_SESSION['login'] != 'alex' && @$_SESSION['login'] != 'duda'  && !check_access()){ 
    exit;
}

foreach (@$_POST as $key => $value){
    $_POST[$key] = trim($value);
}
/*
if (!empty($_POST['change_image_version']) && isset($_POST['image_version'])){
    $db->executeQuery('alter table users modify image_version varchar(64) not null default "'.$_POST['image_version'].'"');
    $db->executeQuery('update users set image_version="'.$_POST['image_version'].'"');
}

$first_user = $db->executeQuery('select * from users limit 0,1')->getAllValues();
$image_version = $first_user[0]['image_version'];*/

$image_update = new ImageAutoUpdate();


$settings = $image_update->getSettings();

if (!empty($_POST)){
    if (!empty($_POST['switch_autoupdate'])){
        $image_update->toggle();
    }else{
        $image_update->setSettings($_POST);
    }

    header("Location: setting_common.php");
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
}.list, .list td, .form{
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
.form{
    width: 80%;
}

.form td{
    width: 50%;
}

.form input{
    width: 90%;
}

h3{
    text-align: left;
    margin-left: 30px;
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
        <!--<h3>Автоматическое обновление (включено) <input type="button" value="Отключить"/></h3>-->
        <form method="POST">
            <h3>Автоматическое обновление (<? echo ($image_update->isEnabled()?'включено':'отключено')?>) <input type="submit" name="switch_autoupdate" value="<?echo ($image_update->isEnabled()?"Отключить":"Включить")?>"/></h3>
            <table class="form">
                <tr>
                    <td>ImageVersion</td>
                    <td><input type="text" name="require_image_version" value="<?echo $settings['require_image_version']?>"/></td>
                </tr>
                <tr>
                    <td>ImageDate</td>
                    <td><input type="text" name="require_image_date" value="<?echo $settings['require_image_date']?>"/></td>
                </tr>
                <tr>
                    <td>Необходимый ImageDescription</td>
                    <td><input type="text" name="image_description_contains" value="<?echo $settings['image_description_contains']?>"/></td>
                </tr>
                <tr>
                    <td>Необходимый ImageVersion</td>
                    <td><input type="text" name="image_version_contains" value="<?echo $settings['image_version_contains']?>"/></td>
                </tr>
                <tr>
                    <td>Вариант обновления</td>
                    <td>
                        <select name="update_type">
                            <option value="http_update" <?echo $settings['update_type']=='http_update'?'selected':''?>>http update</option>
                            <option value="reboot_dhcp" <?echo $settings['update_type']=='reboot_dhcp'?'selected':''?>>reboot dhcp</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td><input type="submit" value="Сохранить"/></td>
                </tr>
                <!--<tr>
                    <td colspan="2">Описание: </td>
                </tr>-->
            </table>
        </form>
    </td>
</tr>
</table>