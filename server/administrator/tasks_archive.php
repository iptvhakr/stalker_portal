<?php
session_start();

ob_start();

include "../conf_serv.php";
include "../lib/func.php";
include "./lib/tasks.php";

$error = '';

$db = new Database(DB_NAME);

moderator_access();

echo '<pre>';
//print_r($_FILES);
//print_r($_SESSION);
//print_r($_POST);
echo '</pre>';

$search = @$_GET['search'];
$letter = @$_GET['letter'];
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
<title>Архив Видео заданий</title>
</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0" width="600">
<tr>
    <td align="center" valign="middle" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;Архив Видео заданий&nbsp;</b></font>
    </td>
</tr>
<tr>
    <td width="100%" align="left" valign="bottom">
        <a href="stat_moderators.php"><< Назад</a> 
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
<?

function get_total_tasks($uid){
    $db  = Database::getInstance(DB_NAME);
    
    $sql = "select * from moderator_tasks where to_usr=$uid and archived=0";
    $rs  = $db->executeQuery($sql);
    return $rs->getRowCount();
}

function get_open_tasks($uid){
    $db  = Database::getInstance(DB_NAME);
    
    $sql = "select * from moderator_tasks where ended=0 and to_usr=$uid and archived=0";
    $rs  = $db->executeQuery($sql);
    return $rs->getRowCount();
}

function get_closed_tasks($uid){
    $db = Database::getInstance(DB_NAME);
    
    $sql = "select * from moderator_tasks where ended=1 and to_usr=$uid and archived=0";
    $rs  = $db->executeQuery($sql);
    return $rs->getRowCount();
}

function get_rejected_tasks($uid){
    $db = Database::getInstance(DB_NAME);
    
    $sql = "select * from moderator_tasks where rejected=1 and to_usr=$uid and archived=0";
    $rs  = $db->executeQuery($sql);
    return $rs->getRowCount();
}

function page_bar(){
    global $MAX_PAGE_ITEMS;
    global $page;
    global $total_pages;
    
    $page_bar = '';

    for($i = 1; $i <= $total_pages; $i++){
        if(($i-1) != $page){
            $page_bar .= ' <a href="?page='.($i-1).'&search='.@$_GET['search'].'&letter='.@$_GET['letter'].'">'.$i.'</a> |';
        }
        else
        {
            $page_bar .= '<b> '.$i.' </b>|';
        }
    }
    return $page_bar;
}

if (@$_GET['id']){
    
    $archive_id = intval($_GET['id']);
    
    $sql = "select * from administrators where access=2";
    $rs=$db->executeQuery($sql);
    
    
    while(@$rs->next()){
        $arr = $rs->getCurrentValuesAsHash();
        $uid = $arr['id']
        ?>
        
        <table border="0" align="center" width="760">
        <tr>
        <td align="center">
        <b><?echo $arr['login'] ?></b>
        <table border="1" width="100%" cellspacing="0">
            <tr>
                <td>#</td>
                <td>Видео</td>
                <td>Дата открытия</td>
                <td>Дата закрытия</td>
                <td width="100">Длительность медиа, м</td>
            </tr>
            <?
            
            $from_time = date("Y-m-d H:i:s",strtotime ("-1 month"));
            
            //$sql = "select * from moderator_tasks where moderator_tasks.ended=1 and rejected=0 and end_time>'$from_time' and moderator_tasks.to_usr=$uid";
            $sql_done = "select * from moderator_tasks where moderator_tasks.ended=1 and rejected=0 and archived=$archive_id and moderator_tasks.to_usr=$uid";
            //echo $sql_done;
            $rs_done = $db->executeQuery($sql_done);
            
            $length = 0;
            $total_length = 0;
            $num = 0;
            while(@$rs_done->next()){
                $arr_done=$rs_done->getCurrentValuesAsHash();
                $num++;
                $length = get_media_length_by_id($arr_done['media_id']);
                $total_length += $length;
                echo "<tr>";
                echo "<td>$num</td>";
                echo "<td><a href='msgs.php?task={$arr_done['id']}'>".get_media_name_by_id($arr_done['media_id'])."</a></td>";
                echo "<td nowrap>".$arr_done['start_time']."</td>";
                echo "<td nowrap>".$arr_done['end_time']."</td>";
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
            <td width="100">Длительность медиа, м</td>
        </tr>
    <?
    $sql_rej = "select * from moderator_tasks where moderator_tasks.ended=1 and rejected=1 and archived=$archive_id and moderator_tasks.to_usr=$uid";
    //echo $sql_rej;
    $rs_rej = $db->executeQuery($sql_rej);
    $num = 0;
    while(@$rs_rej->next()){
        $arr_rej=$rs_rej->getCurrentValuesAsHash();
        $num++;
        $length = get_media_length_by_id($arr_rej['media_id']);
        echo "<tr>";
        echo "<td>$num</td>";
        echo "<td><a href='msgs.php?task={$arr_rej['id']}'>".get_media_name_by_id($arr_rej['media_id'])."</a></td>";
        echo "<td nowrap>".$arr_rej['start_time']."</td>";
        echo "<td nowrap>".$arr_rej['end_time']."</td>";
        echo "<td align='right'>".$length."</td>";
        echo "</tr>";
    }
    
    ?>
    </table>
<br>
<br>
<hr>
<br>
<br>
        
    <?
    }
}
else{
    $page=@$_REQUEST['page']+0;
    $MAX_PAGE_ITEMS = 30;
    
    $query = "select * from tasks_archive";
    $rs = $db->executeQuery($query);
    $total_items = $rs->getRowCount();
    
    $page_offset=$page*$MAX_PAGE_ITEMS;
    $total_pages=(int)($total_items/$MAX_PAGE_ITEMS+0.999999);
    
    $sql = "select * from tasks_archive LIMIT $page_offset, $MAX_PAGE_ITEMS";
    $rs  = $db->executeQuery($sql);
    
    while(@$rs->next()){
        $arr=$rs->getCurrentValuesAsHash();
        ?>
        
        <table border="1" width="200" cellspacing="0">
          <tr>
            <td align="center">
               <a href="tasks_archive.php?id=<?echo $arr['id']?>"><?echo $arr['year'].'-'.$arr['month']?></a>
            </td>
          </tr>
        </table>
        <br>
    
    <?
    }
    echo "<table width='600' align='center' border=0>\n";
    echo "<tr>\n";
    echo "<td width='100%' align='center'>\n";
    echo page_bar();
    echo "</td>\n";
    echo "</tr>\n";
    echo "</table>\n";
}
?>

</td>
</tr>
</table>
</body>
</html>