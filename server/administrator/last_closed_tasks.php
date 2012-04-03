<?php
session_start();

ob_start();

include "./common.php";
include "./lib/tasks.php";

$error = '';

$db = new Database();

moderator_access();

if (@$_GET['archive'] == 1 && @$_GET['id']){
    $id = intval(@$_GET['id']);
    
    $year  = date("Y");
    $month = date("n");
    
    if ($month == 1){
        $month = 12;
        $year--;
    }else{
        $month--;
    }
    
    // video archive
    $sql  = "select * from tasks_archive where month=$month and year=$year";
    $rs   = $db->executeQuery($sql);
    
    if (intval($rs->getRowCount()) > 0){
        $archive_id = $rs->getValueByName(0, 'id');
    }else{
        $sql = "insert into tasks_archive (`date`, `year`, `month`) value (NOW(), $year, $month)";
        $rs  = $db->executeQuery($sql);
        $archive_id = $rs->getLastInsertId();
    }
    
    $sql = "update moderator_tasks set archived=$archive_id, archived_time=NOW() where archived=0 and ended=1 and to_usr=$id";
    $rs = $db->executeQuery($sql);
    
    // karaoke archive
    $sql  = "select * from karaoke_archive where month=$month and year=$year";
    $rs   = $db->executeQuery($sql);
    
    if (intval($rs->getRowCount()) > 0){
        $archive_id = $rs->getValueByName(0, 'id');
    }else{
        $sql = "insert into karaoke_archive (`date`, `year`, `month`) value (NOW(), $year, $month)";
        $rs  = $db->executeQuery($sql);
        $archive_id = $rs->getLastInsertId();
    }

    $sql = "update karaoke set archived=$archive_id, archived_time=NOW() where archived=0 and status=1 and accessed=1 and done=1";
    $rs  = $db->executeQuery($sql);

    header("Location: last_closed_tasks.php?id=".$id);
    exit();
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
a.msgs:hover, a.msgs:visited, a.msgs:link{
	font-family: Arial, Helvetica, sans-serif;
	font-size: 14px;
    color:#000000;
	font-weight: bold;
	text-decoration:none;
}
</style>
<title>Выполненные задания</title>
</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0">
<tr>
    <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;Выполненные задания&nbsp;</b></font>
    </td>
</tr>
<tr>
    <td width="100%" align="left" valign="bottom">
        <a href="tasks.php"><< Назад</a>
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

<table border="0" align="center" width="620">
<tr>
<td>
<font color="Gray">* отчет формируется по закрытым за месяц фильмам</font>
</td>
</tr>
</table>

</td>
</tr>
</table>
<?

$where = '';

if (check_access(array(1))){
    $uid = @$_GET['id'];
}else{
	if (@$_SESSION['uid'] != @$_GET['id']){
		$uid = 0;
	}else{
		$uid = $_SESSION['uid'];
	}
}
?>
    
    <table border="0" align="center" width="760">
    <tr>
    <td align="center">
    
    <table border="1" width="100%" cellspacing="0">
        <tr>
            <td>#</td>
            <td>Видео</td>
            <td>Дата открытия</td>
            <td>Дата закрытия</td>
            <td>Длительность медиа, м</td>
        </tr>
        <?
        
        $from_time = date("Y-m-d H:i:s",strtotime ("-1 month"));
        
        //$sql = "select * from moderator_tasks where moderator_tasks.ended=1 and rejected=0 and end_time>'$from_time' and moderator_tasks.to_usr=$uid";
        $sql = "select * from moderator_tasks where moderator_tasks.ended=1 and rejected=0 and archived=0 and moderator_tasks.to_usr=$uid order by end_time";
        
        $rs = $db->executeQuery($sql);
        
        $length = 0;
        $total_length = 0;
        $num = 0;
        while(@$rs->next()){
            $arr=$rs->getCurrentValuesAsHash();
            $num++;
            $length = get_media_length_by_id($arr['media_id']);
            $total_length += $length;
            echo "<tr>";
            echo "<td>$num</td>";
            echo "<td><a href='msgs.php?task={$arr['id']}'>".get_media_name_by_id($arr['media_id'])."</a></td>";
            echo "<td>".$arr['start_time']."</td>";
            echo "<td>".$arr['end_time']."</td>";
            echo "<td align='right'>".$length."</td>";
            echo "</tr>";
        }
        ?>
    </table>
    <table border="0" width="100%">
        <tr>
            <td  width="100%" align="right"> Суммарная длительность, м:  <b><? echo $total_length?></b></td>
        </tr>
    </table>
    <br>
    <br>
    <center>Отклоненные задания</center>
    <table border="1" width="100%" cellspacing="0">
        <tr>
            <td>#</td>
            <td>Видео</td>
            <td>Дата открытия</td>
            <td>Дата закрытия</td>
            <td>Длительность медиа, м</td>
        </tr>
    <?
    $sql = "select * from moderator_tasks where moderator_tasks.ended=1 and rejected=1 and archived=0 and moderator_tasks.to_usr=$uid";
    
    $rs = $db->executeQuery($sql);
    $num = 0;
    while(@$rs->next()){
        $arr=$rs->getCurrentValuesAsHash();
        $num++;
        $length = get_media_length_by_id($arr['media_id']);
        echo "<tr>";
        echo "<td>$num</td>";
        echo "<td><a href='msgs.php?task={$arr['id']}'>".get_media_name_by_id($arr['media_id'])."</a></td>";
        echo "<td>".$arr['start_time']."</td>";
        echo "<td>".$arr['end_time']."</td>";
        echo "<td align='right'>".$length."</td>";
        echo "</tr>";
    }
    
    ?>
    </table>
    <br>
    <br>
    <table border="0" width="100%">
    <tr>
        <td align="right">
        <?if (check_access(array(1))){?>
        <input type="button" value="В архив" onclick="if(confirm('Переместить в архив?')){document.location='last_closed_tasks.php?id=<?echo @$_GET['id']?>&archive=1';}">
        <?}?>
        </td>
    </tr>
    </table>
    <td>
    </tr>
    </table>
