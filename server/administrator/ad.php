<?php
session_start();

ob_start();
include "./common.php";

$error = '';
$action_name = 'add';
$action_value = 'Добавить';

Admin::checkAuth();

Admin::checkAccess(AdminAccess::ACCESS_VIEW);

foreach (@$_POST as $key => $value){
    $_POST[$key] = trim($value);
}


$id = @intval($_GET['id']);

$ad = new Advertising();

//if (!empty($id)){
    
    if (@$_POST['edit'] || @$_POST['add']){

        Admin::checkAccess(AdminAccess::ACCESS_CREATE);
        Admin::checkAccess(AdminAccess::ACCESS_EDIT);

        $ad->setMain(@$_POST['title'], @$_POST['text'], @$_POST['video_id']);
        
        header("Location: ad.php");
    }elseif (@$_GET['del']){

        Admin::checkAccess(AdminAccess::ACCESS_DELETE);

        $ad->delMain();
        
        header("Location: ad.php");
    }
//}

if (@$_GET['edit'] && !empty($id)){
    
    $action_name = 'edit';
    $action_value = 'Сохранить';
    $edit_main_ad = $ad->getMain();
}

$main_ad = $ad->getMain();

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
<title>Реклама</title>
</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0">
<tr>
    <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;Реклама&nbsp;</b></font>
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
    <table class='list' cellpadding='3' cellspacing='0'>
        <tr>
            <td>Заголовок</td>
            <td>Видео ID</td>
            <td>&nbsp;</td>
        </tr>
        <? //foreach ($main_ads as $main_ad){
           if (!empty($main_ad)){
                echo '<tr>';
                echo '<td>'.$main_ad['title'].'</td>';
                echo '<td>'.$main_ad['video_id'].'</td>';
                echo '<td>';
                echo '<a href="?edit=1&id='.$main_ad['id'].'">edit</a>&nbsp;';
                echo '<a href="?del=1&id='.$main_ad['id'].'" onclick="if(confirm(\'Вы действительно хотите удалить рекламу из базы удалить?\')){return true}else{return false}">del</a>';
                echo '</td>';
                echo '</tr>';
           }
           //}?>
    </table>
</td>
</tr>
<tr>
    <td align="center">
<br>
<br>

<? if (@$_GET['edit'] || empty($main_ad)){ ?>
        <form method="POST">
            <table class="form">
                <tr>
                    <td>Заголовок</td>
                    <td><input type="text" name="title" size="37" maxlength="40" value="<?echo @$edit_main_ad['title']?>"></input></td>
                </tr>
                <tr>
                    <td valign="top">Текст</td>
                    <td><textarea name="text" cols="30" rows="15"><?echo @$edit_main_ad['text']?></textarea></td>
                </tr>
                <tr>
                    <td>Видео ID</td>
                    <td><input type="text" name="video_id" value="<?echo @$edit_main_ad['video_id']?>"></input></td>
                </tr>
                <tr>
                    <td></td>
                    <td><input type="submit" name="<? echo $action_name ?>" value="<? echo $action_value?>"></input></td>
                </tr>
            </table>
        </form>
<?}?>
    </td>
</tr>
</table>