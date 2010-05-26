<?php
session_start();

ob_start();

include "../common.php";
include "../conf_serv.php";
include "../lib/func.php";
include "./lib/tasks.php";

$error = '';

$db = new Database(DB_NAME);

moderator_access();

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
a.msgs:hover, a.msgs:visited, a.msgs:link{
	font-family: Arial, Helvetica, sans-serif;
	font-size: 14px;
    color:#000000;
	font-weight: bold;
	text-decoration:none;
}
</style>
<title>Задания</title>
</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0">
<tr>
    <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;Задания&nbsp;</b></font>
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

$where = '';
$uid = $_SESSION['uid'];

if (check_access(array(1))){
    $where = 'where access=2 or access=1';
    //$where_main = '';
}
if (check_access(array(2))){
    $where = 'where id='.@$_SESSION['uid'];
    //$where_main = "and moderator_tasks.to_usr=$uid";
}
$sql = "select * from administrators $where order by login";
$rs=$db->executeQuery($sql);

while(@$rs->next()){
    $arr=$rs->getCurrentValuesAsHash();
    ?>
    
    <table border="0" align="center" width="680">
    <tr>
    <td align="center">
    <br>
    <b><?echo $arr['login'] ?></b> - <a href="last_closed_tasks.php?id=<?echo $arr['id'] ?>">видео</a>
    <br>
    <table border="1" width="100%" cellspacing="0">
        <tr>
            <td><b>#</b></td>
            <td><b>Видео</b></td>
            <td><b>Дата открытия</b></td>
            <td><b>Сообщения</b></td>
        </tr>
        <?
        $sql_open = "select * from moderator_tasks 
                        where 
                            moderator_tasks.ended=0
                            and archived=0
                            and moderator_tasks.to_usr={$arr['id']}";
        
        $rs_open = $db->executeQuery($sql_open);
        $num = 1;
        while(@$rs_open->next()){
            $arr_open=$rs_open->getCurrentValuesAsHash();
            
            echo "<tr ";
            if (is_answered($arr_open['id'])){
                echo "bgcolor='#ccffcc'";
            }else{
                echo "bgcolor='#ffcccc'";
            }
            echo " >";
            echo "<td>".$num.".</td>";
            echo "<td>".get_media_name_by_id($arr_open['media_id'])."</td>";
            echo "<td>".$arr_open['start_time']."</td>";
            echo "<td>".get_count_all_msgs($arr_open['id']).' / <a href="msgs.php?task='.$arr_open['id'].'" class="msgs"><b>'.get_count_unreaded_msgs($arr_open['id'])."</b></a></td>";
            echo "</tr>";
            $num++;
        }
        ?>
    </table>
    <br>
    <br>
    <br>
    <b><?echo $arr['login'] ?></b> - <a href="last_closed_karaoke.php?id=<?echo $arr['id'] ?>">караоке</a>
    <table border="1" width="100%" cellspacing="0">
    <tr>
        <td><b>#</b></td>
        <td><b>Название</b></td>
        <td><b>Исполнитель</b></td>
        <td><b>Дата добавления</b></td>
    </tr>
    
    <? 
        $sql_open_karaoke = "select * from karaoke
                                where
                                    archived=0
                                    and accessed=0
                                    and add_by={$arr['id']}";
        $rs_open_karaoke = $db->executeQuery($sql_open_karaoke);
        $num = 1;
        while(@$rs_open_karaoke->next()){
            $arr_open_kar = $rs_open_karaoke->getCurrentValuesAsHash();
            
            echo "<tr>";
            echo "<td>".$num.".</td>";
            echo "<td>".$arr_open_kar['name']."</td>";
            echo "<td>".$arr_open_kar['singer']."</td>";
            echo "<td>".$arr_open_kar['added']."</td>";
            echo "</tr>";
            $num++;
        }
    ?>
    
    </table>
    
    <td>
    </tr>
    </table>
    <br>
<br>
<br>
<hr>
<?
}
?>
</td>
</tr>
<tr>
<td>
<?
if (check_access(array(1))){
$sql = "select * from moderator_tasks where ended=0 and archived=0 and (UNIX_TIMESTAMP(NOW())-UNIX_TIMESTAMP(start_time))>864000";
$rs=$db->executeQuery($sql);
if ($rs->getRowCount() > 0){
    
    echo '<center><b><font color="Red">Истекло 10 суток</font></b></center>';
    echo '<table border="1" width="680" cellspacing="0">';
    echo "<tr>";
    echo "<td><b>#</b></td>";
    echo "<td><b>Видео</b></td>";
    echo "<td><b>Кому</b></td>";
    echo "<td><b>Дата открытия</b></td>";
    echo "<td>&nbsp;</td>";
    echo "</tr>";
    $num = 0;
    while(@$rs->next()){
        $arr=$rs->getCurrentValuesAsHash();
        $num++;
        echo "<tr>";
        echo "<td>".$num."</td>";
        //echo "<td><a href='msgs.php?task={$arr['id']}'>".get_media_name_by_id($arr['media_id'])."</a></td>";
        echo "<td>".get_media_name_by_id($arr['media_id'])."</td>";
        echo "<td>".get_moderator_login_by_id($arr['to_usr'])."</td>";
        echo "<td>".$arr['start_time']."</td>";
        echo '<td><a href="reject_task.php?id='.$arr['id'].'&send_to='.$arr['media_id'].'">отклонить</a></td>';
        echo "</tr>";
    }
    echo "</table>";
}
echo "<br>";
echo "<br>";
echo "<br>";
}
?>
</td>
</tr>
</table>