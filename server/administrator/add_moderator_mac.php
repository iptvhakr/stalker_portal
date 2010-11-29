<?php
session_start();

ob_start();

include "../conf_serv.php";
include "../common.php";
include "../lib/func.php";

$error = '';

$db = new Database(DB_NAME);

moderator_access();


if (@$_GET['del']){
    $query = "delete from moderators where id=".intval(@$_GET['id']);
    $rs=$db->executeQuery($query);
    header("Location: add_moderator_mac.php");
    exit;
}

if (isset($_GET['status']) && @$_GET['id']){
    $query = "update moderators set status='".intval(@$_GET['status'])."' where id=".intval(@$_GET['id']);
    $rs=$db->executeQuery($query);
    header("Location: add_moderator_mac.php");
    exit;
}

if (!$error){
    //var_dump($_POST);
    if (@$_GET['save']){
    
        if(@$_POST['mac']){
    
            $query = "insert into moderators (name,mac) values ('".@$_POST['name']."','".@$_POST['mac']."')";
            //echo $query;
            $rs=$db->executeQuery($query);
            header("Location: add_moderator_mac.php");
        }
        else{
            $error = 'Ошибка: необходимо заполнить все поля';
        }
    }
    
    if (@$_GET['update']){
        
        if(@$_POST['mac']){
            
            $query = "update moderators set name='".$_POST['name']."', mac='".$_POST['mac']."' where id=".intval(@$_GET['id']);
            //echo $query;
            $rs=$db->executeQuery($query);
            header("Location: add_moderator_mac.php");
        }
        else{
            $error = 'Ошибка: необходимо заполнить все поля';
        }
    }
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
<title>
Редактирование списка MAC адресов модераторов
</title>
</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0">
<tr>
    <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;Редактирование списка MAC адресов модераторов&nbsp;</b></font>
    </td>
</tr>
<tr>
    <td width="100%" align="left" valign="bottom">
        <a href="add_video.php"><< Назад</a>
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
$query = "select * from moderators";

$rs=$db->executeQuery($query);
echo "<center><table class='list' cellpadding='3' cellspacing='0'>";
echo "<tr>";
echo "<td class='list'><b>#</b></td>";
echo "<td class='list'><b>Имя</b></td>";
echo "<td class='list'><b>MAC</b></td>";
echo "<td class='list'>&nbsp;</td>";
echo "</tr>";
$i=1;
while(@$rs->next()){
    
    $arr=$rs->getCurrentValuesAsHash();
    
    echo "<tr>";
    echo "<td class='list'>".$i."</td>";
    echo "<td class='list'>".$arr['name']."</td>";
    echo "<td class='list'>".$arr['mac']."</td>";
    echo "<td class='list'>";
    if ($arr['status']){
        echo "<a href='?status=0&id=".$arr['id']."'><font color='Green'>on</font></a>&nbsp;&nbsp;";
    }else{
        echo "<a href='?status=1&id=".$arr['id']."'><font color='Red'>off</font></a>&nbsp;&nbsp;";
    }
    echo "<a href='?edit=1&id=".$arr['id']."#form'>edit</a>&nbsp;&nbsp;";
    echo "<a href='?del=1&id=".$arr['id']."'>del</a></td>";
    echo "</tr>";
    $i++;
}

echo "</table></center>";

if (@$_GET['edit']){
    $query = "select * from moderators where id=".intval(@$_GET['id']);
    $rs=$db->executeQuery($query);
    while(@$rs->next()){
        $arr=$rs->getCurrentValuesAsHash();
        $mac = $arr['mac'];
        $name = $arr['name'];
    }
}
?>
<script>
function save(){
    form_ = document.getElementById('form_')
    
    //name = document.getElementById('name').value
    //cmd = document.getElementById('cmd').value
    id = document.getElementById('id').value
    
    //action = 'add_web.php?name='+name+'&cmd='+cmd+'&id='+id
    action = 'add_moderator_mac.php?id='+id
    
    if(document.getElementById('action').value == 'edit'){
        action += '&update=1'
    }
    else{
        action += '&save=1'
    }
    
    form_.action = action
    form_.method = 'POST'
    form_.submit()
}
</script>
<br>
<table align="center" class='list'>
<tr>
    <td>
    &nbsp;
    </td>
</tr>
<tr>
    <td>
    <form id="form_" method="POST">
    <table align="center">
        <tr>
           <td align="right">
            Имя: 
           </td>
           <td>
            <input type="text" name="name" id="name" value="<? echo @$name ?>">
           </td>
        </tr>
        <tr>
           <td align="right">
            MAC: 
           </td>
           <td>
            <input type="text" name="mac" id="mac" value="<? echo @$mac ?>">
            <input type="hidden" id="id" value="<? echo @$_GET['id'] ?>">
            <input type="hidden" id="action" value="<? if(@$_GET['edit']){echo "edit";} ?>">
           </td>
        </tr>
        <tr>
           <td>
           </td>
           <td>
            <input type="button" value="Сохранить" onclick="save()">&nbsp;<input type="button" value="Новый" onclick="document.location='add_moderator_mac.php'">
           </td>
        </tr>
    </table>
    </form>
    <a name="form"></a>
    </td>
</tr>
</table>
</td>
</tr>
</table>
</body>
</html>