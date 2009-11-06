<?php
session_start();

ob_start();
include "../conf_serv.php";
include "../lib/func.php";

$error = '';

$db = new Database(DB_NAME);

moderator_access();

if (@$_GET['del']){
    $query = "delete from radio where id=".intval(@$_GET['id']);
    $rs=$db->executeQuery($query);
    header("Location: add_radio.php");
}

if (isset($_GET['status']) && @$_GET['id']){
    $query = "update radio set status='".intval(@$_GET['status'])."' where id=".intval(@$_GET['id']);
    $rs=$db->executeQuery($query);
    header("Location: add_radio.php");
}

if (!$error){
    
    if (@$_POST['number'] && !check_number($_POST['number']) && !@$_GET['update']){
        $error = 'Ошибка: Номер канала "'.intval($_POST['number']).'" уже используется';
    }
    
    if (@$_GET['save'] && !$error){
    
        if(@$_GET['cmd'] && @$_GET['name']){
    
            $query = "insert into radio (
                                        name,
                                        number,
                                        cmd
                                        ) 
                                values ('".@$_POST['name']."', 
                                        '".@$_POST['number']."', 
                                        '".@$_POST['cmd']."'
                                        )";
            //echo $query;
            $rs=$db->executeQuery($query);
            header("Location: add_radio.php");
        }
        else{
            $error = 'Ошибка: необходимо заполнить все поля';
        }
    }
    
    if (@$_GET['update'] && !$error){
        
        if(@$_GET['cmd'] && @$_GET['name']){
            
            $query = "update radio 
                                set name='".$_POST['name']."', 
                                cmd='".$_GET['cmd']."', 
                                number='".$_POST['number']."'
                            where id=".intval(@$_GET['id']);
            $rs=$db->executeQuery($query);
            header("Location: add_radio.php");
        }
        else{
            $error = 'Ошибка: необходимо заполнить все поля';
        }
    }
}

function check_number($num){
    global $db;
    $total_items = 1;
    $query = "select * from radio where number=".intval($num);
    $rs=$db->executeQuery($query);
	$total_items = $rs->getRowCount();
	if ($total_items > 0){
	    return 0;
	}else{
	    return 1;
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
Редактирование списка РАДИО каналов
</title>
</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0">
<tr>
    <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;Редактирование списка РАДИО каналов&nbsp;</b></font>
    </td>
</tr>
<tr>
    <td width="100%" align="left" valign="bottom">
        <a href="index.html"><< Назад</a>
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
$query = "select * from radio order by number";

$rs=$db->executeQuery($query);
echo "<center><table class='list' cellpadding='3' cellspacing='0'>";
echo "<tr>";
echo "<td class='list'><b>id</b></td>";
echo "<td class='list'><b>Номер</b></td>";
echo "<td class='list'><b>Имя</b></td>";
echo "<td class='list'><b>Адрес</b></td>";
echo "</tr>";
while(@$rs->next()){
    
    $arr=$rs->getCurrentValuesAsHash();
    
    echo "<tr>";
    echo "<td class='list'>".$arr['id']."</td>";
    echo "<td class='list'>".$arr['number']."</td>";
    echo "<td class='list'>".$arr['name']."</td>";
    echo "<td class='list'>".$arr['cmd']."</td>";
    echo "<td class='list'><a href='?edit=1&id=".$arr['id']."#form'>edit</a>&nbsp;&nbsp;";
    echo "<a href='?del=1&id=".$arr['id']."'>del</a>&nbsp;&nbsp;";
    if ($arr['status']){
        echo "<a href='?status=0&id=".$arr['id']."'><font color='Green'>on</font></a>&nbsp;&nbsp;";
    }else{
        echo "<a href='?status=1&id=".$arr['id']."'><font color='Red'>off</font></a>&nbsp;&nbsp;";
    }
    echo "</tr>";
}
echo "</table></center>";

if (@$_GET['edit']){
    $query = "select * from radio where id=".intval(@$_GET['id']);
    $rs=$db->executeQuery($query);
    while(@$rs->next()){
        $arr=$rs->getCurrentValuesAsHash();
        $name = $arr['name'];
        $number = $arr['number'];
        $cmd = $arr['cmd'];
        $status = $arr['status'];
    }
}
?>
<script>
function save(){
    form_ = document.getElementById('form_')
    
    name = document.getElementById('name').value
    cmd = document.getElementById('cmd').value
    id = document.getElementById('id').value
    
    action = 'add_radio.php?name='+name+'&cmd='+cmd+'&id='+id

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

function popup(src){
     window.open( src, 'win_'+src, 'width=300,height=200,toolbar=0,location=0,directories=0,menubar=0,scrollbars=0,resizable=1,status=0,fullscreen=0')
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
            Номер: 
           </td>
           <td>
            <input type="text" name="number" id="number" value="<? echo @$number ?>"  maxlength="3">
           </td>
        </tr>
        <tr>
           <td align="right">
            Название: 
           </td>
           <td>
            <input type="text" name="name" size="50" id="name" value="<? echo @$name ?>">
            <input type="hidden" id="id" value="<? echo @$_GET['id'] ?>">
            <input type="hidden" id="action" value="<? if(@$_GET['edit']){echo "edit";} ?>">
           </td>
        </tr>
        <tr>
           <td align="right">
            Адрес: 
           </td>
           <td>
            <input id="cmd" name="cmd" size="50" type="text" value="<? echo @$cmd ?>">
           </td>
        </tr>
        <tr>
           <td>
           </td>
           <td>
            <input type="button" value="Сохранить" onclick="save()">&nbsp;<input type="button" value="Новый" onclick="document.location='add_radio.php'">
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