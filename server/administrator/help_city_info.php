<?php
session_start();

ob_start();

include "../common.php";
include "../lib/func.php";

$error = '';

$db = new Database();

moderator_access();

if (@$_GET['del']){
    $query = "delete from help_city_info where id=".intval(@$_GET['id']);
    $rs=$db->executeQuery($query);
    header("Location: help_city_info.php");
}

if (isset($_GET['status']) && @$_GET['id']){
    $query = "update help_city_info set status='".intval(@$_GET['status'])."' where id=".intval(@$_GET['id']);
    $rs=$db->executeQuery($query);
    header("Location: help_city_info.php");
}

if (!$error){
    
    if (@$_GET['save'] && !$error){
    
        if(@$_POST['title'] && @$_POST['number']){
    
            $query = "insert into help_city_info (
                                        num,
                                        title,
                                        number
                                        ) 
                                values ('".@$_POST['num']."', 
                                        '".@$_POST['title']."', 
                                        '".@$_POST['number']."'
                                        )";
            //echo $query;
            $rs=$db->executeQuery($query);
            header("Location: help_city_info.php");
        }
        else{
            $error = 'Ошибка: необходимо заполнить все поля';
        }
    }
    
    if (@$_GET['update'] && !$error){
        
        if(@$_POST['title'] && @$_POST['number']){
            
            $query = "update help_city_info 
                                set title='".$_POST['title']."', 
                                num='".$_POST['num']."', 
                                number='".$_POST['number']."'
                            where id=".intval(@$_GET['id']);
            $rs=$db->executeQuery($query);
            header("Location: help_city_info.php");
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
<title>Редактирование списка справочных служб</title>
</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0">
<tr>
    <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;Редактирование списка справочных служб&nbsp;</b></font>
    </td>
</tr>
<tr>
    <td width="100%" align="left" valign="bottom">
        <a href="index.php"><< Назад</a> | <a href="city_info.php">Экстренные службы</a> | <a href="other_city_info.php">Другие службы</a>
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
$query = "select * from help_city_info order by num";

$rs=$db->executeQuery($query);
echo "<center><table class='list' cellpadding='3' cellspacing='0'>";
echo "<tr>";
echo "<td class='list'><b>#</b></td>";
echo "<td class='list'><b>Служба</b></td>";
echo "<td class='list'><b>Номер</b></td>";
echo "</tr>";
while(@$rs->next()){
    
    $arr=$rs->getCurrentValuesAsHash();
    
    echo "<tr>";
    echo "<td class='list'>".$arr['num']."</td>";
    echo "<td class='list'>".$arr['title']."</td>";
    echo "<td class='list'>".$arr['number']."</td>";
    echo "<td class='list'><a href='?edit=1&id=".$arr['id']."#form'>edit</a>&nbsp;&nbsp;";
    echo "<a href='#' onclick='if(confirm(\"Удалить данную запись?\")){document.location=\"help_city_info.php?del=1&id=".$arr['id']."\"}'>del</a>&nbsp;&nbsp;\n";
    
    echo "</tr>";
}
echo "</table></center>";

if (@$_GET['edit']){
    $query = "select * from help_city_info where id=".intval(@$_GET['id']);
    $rs=$db->executeQuery($query);
    while(@$rs->next()){
        $arr=$rs->getCurrentValuesAsHash();
        $title = $arr['title'];
        $number = $arr['number'];
        $num = $arr['num'];
    }
}
?>
<script>
function save(){
    form_ = document.getElementById('form_')
    
    id = document.getElementById('id').value
    
    action = 'help_city_info.php?id='+id

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
            Номер по порядку: 
           </td>
           <td>
            <input id="num" name="num" size="10" type="text" value="<? echo @$num ?>">
           </td>
        </tr>
        <tr>
           <td align="right">
            Служба: 
           </td>
           <td>
            <input type="text" name="title" size="50" id="title" value="<? echo @$title ?>">
            <input type="hidden" id="id" value="<? echo @$_GET['id'] ?>">
            <input type="hidden" id="action" value="<? if(@$_GET['edit']){echo "edit";} ?>">
           </td>
        </tr>
        <tr>
           <td align="right">
            Номер: 
           </td>
           <td>
            <input id="number" name="number" size="50" type="text" value="<? echo @$number ?>">
           </td>
        </tr>
        <tr>
           <td>
           </td>
           <td>
            <input type="button" value="Сохранить" onclick="save()">&nbsp;<input type="button" value="Новый" onclick="document.location='help_city_info.php'">
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