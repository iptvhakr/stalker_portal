<?php
session_start();

ob_start();

include "../common.php";
include "../conf_serv.php";
include "../getid3/getid3.php";
include "../lib/func.php";
include "./lib/tasks.php";

$error = '';

$db = new Database(DB_NAME);

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

    $sql = "update karaoke set archived=$archive_id, archived_time=NOW() where archived=0 and status=1 and accessed=1";
    $rs  = $db->executeQuery($sql);

    header("Location: last_closed_tasks.php?id=".$id);
    exit();
}


if (isset($_GET['accessed']) && @$_GET['id']){
    set_karaoke_accessed(@$_GET['id'], @$_GET['accessed']);
    $id = @$_GET['id'];
    if ($_GET['accessed'] == 1){
        chmod(KARAOKE_STORAGE_DIR.'/'.$id.'.mpg', 0444);
    }else{
        chmod(KARAOKE_STORAGE_DIR.'/'.$id.'.mpg', 0666);
    }
    header("Location: last_closed_karaoke.php?id=".@$_GET['uid']);
    exit;
}

if (isset($_GET['returned']) && @$_GET['id']){
	set_karaoke_returned(@$_GET['id'], @$_GET['returned'],@$_GET['reason']);
    header("Location: last_closed_karaoke.php?id=".@$_GET['uid']);
    exit;
}

if (isset($_GET['done']) && @$_GET['id']){
    set_karaoke_done(@$_GET['id'], @$_GET['done']);
    $id = @$_GET['id'];
    
    header("Location: last_closed_karaoke.php?id=".@$_GET['uid']);
    exit;
}


function set_karaoke_accessed($id, $val){
    global $db;
    if ($id){
        $query = "update karaoke set accessed=$val, added=NOW() where id=$id";
        $db->executeQuery($query);
    }
}

function set_karaoke_done($id, $val){
    global $db;
    if ($id){
        $query = "update karaoke set done=$val, done_time=NOW() where id=$id";
        $db->executeQuery($query);
    }
}

function set_karaoke_returned($id, $val, $txt){
    global $db;
    if ($id){
    	if ($val == 1){
    		$done = 0;
    	}else{
    		$done = 1;
    	}
        $query = "update karaoke set returned=$val, reason='$txt', done=$done where id=$id";
        $db->executeQuery($query);
    }
}

function get_karaoke_accessed($id){
    global $db;
    
    $query = "select * from karaoke where id=$id";
    $rs=$db->executeQuery($query);
    $accessed = $rs->getValueByName(0, 'accessed');
    return $accessed;
}

function get_done_karaoke($id){
    $db = Database::getInstance(DB_NAME);
    
    $query = "select * from karaoke where id=$id";
    $rs=$db->executeQuery($query);
    $accessed = $rs->getValueByName(0, 'done');
    return $accessed;
}

function get_karaoke_accessed_color($id){
    if (get_karaoke_accessed($id)){
        $color = 'green';
        $accessed = 0;
        $txt = 'on';
    }else{
        $color = 'red';
        $accessed = 1;
        $txt = 'off';
    }
    $letter = @$_GET['letter'];
    $search = @$_GET['search'];
    if (check_access(array(1))){
        return "<a href='last_closed_karaoke.php?accessed=$accessed&id=$id&uid=".@$_GET['id']."&letter=".@$_GET['letter']."&search=".@$_GET['search']."&page=".@$_GET['page']."'><font color='$color'>$txt</font></a>";
    }else{
        return "<font color='$color'><b>$txt</b></font>";
    }
}

function get_done_karaoke_color($id){
    if(get_done_karaoke($id)){
        $color = 'green';
        $done = 0;
        $txt = 'сделано';
    }else{
        $color = 'red';
        $done = 1;
        $txt = 'не сделано';
    }
    $letter = @$_GET['letter'];
    $search = @$_GET['search'];
    if (check_access(array(1))){
        return "<a href='last_closed_karaoke.php?done=$done&id=$id&uid=".@$_GET['id']."&letter=".@$_GET['letter']."&search=".@$_GET['search']."&page=".@$_GET['page']."'><font color='$color'>$txt</font></a>";
    }else{
    	return "<font color='$color'><b>$txt</b></font>";
    }
}

function return_karaoke($id, $returned, $reason){
	if($returned == 1){
		$txt = 'в возврате';
		$color = 'red';
		$returned = 0;
	}else{
		$txt = 'вернуть';
        $color = '#CBCB00';
        $returned = 1;
	}
	if (check_access(array(1))){
		//return "<a href='last_closed_karaoke.php?return=1&id=$id&uid=".@$_GET['id']."&letter=".@$_GET['letter']."&search=".@$_GET['search']."&page=".@$_GET['page']."'><font color='#CBCB00'>вернуть</font></a>";
		$str  = "<a href='#' ";
		if ($returned == 0){
			$str .= "title='$reason'";
		}
		$str .= " onclick='reason = prompt(\"Причина возврата:\"); if(reason){document.location = \"last_closed_karaoke.php?returned=$returned&id=$id&uid=".@$_GET['id']."&reason=\"+reason} '><font color='$color'>$txt</font></a>";
		return $str;
	}else{
	   $str  = "<a href='#' ";
        if ($returned == 0){
            $str .= "title='$reason'";
        }
        $str .= " ><font color='$color'>$txt</font></a>";
        return $str;
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
a.msgs:hover, a.msgs:visited, a.msgs:link{
	font-family: Arial, Helvetica, sans-serif;
	font-size: 14px;
    color:#000000;
	font-weight: bold;
	text-decoration:none;
}
</style>
<title>Выполненные Караоке ролики</title>
</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0">
<tr>
    <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;Выполненные Караоке ролики&nbsp;</b></font>
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
    $uid = @$_SESSION['uid'];
}
?>
    
    <table border="0" align="center" width="760">
    <tr>
    <td align="center">
    
    <table border="1" width="100%" cellspacing="0">
        <tr>
            <td>#</td>
            <td>Название</td>
            <td>Исполнитель</td>
            <td>Дата включения</td>
            <td>&nbsp;</td>
        </tr>
        <?
        
        
        //$sql_done = "select * from karaoke where status=1 and done=1 and archived=0 and add_by=$uid";
        $sql_done = "select * from karaoke where archived=0 and add_by=$uid order by name";
        
        $rs = $db->executeQuery($sql_done);
        
        $num = 0;
        while(@$rs->next()){
            $arr_done=$rs->getCurrentValuesAsHash();
            $num++;
            
            echo "<tr>";
            echo "<td>$num</td>";
            echo "<td>".$arr_done['name']."</td>";
            echo "<td>".$arr_done['singer']."</td>";
            echo "<td>".$arr_done['added']."</td>";
            echo "<td>";
            echo get_karaoke_accessed_color($arr_done['id'])."&nbsp;&nbsp;";
            echo get_done_karaoke_color($arr_done['id'])."&nbsp;&nbsp;";
            echo return_karaoke($arr_done['id'], $arr_done['returned'], $arr_done['reason']);
            echo "</td>";
            echo "</tr>";
        }
        ?>
    </table>
    <br>
    <br>
    
    <br>
    <br>
    <table border="0" width="100%">
    <!--<tr>
        <td align="right">
        <?//if (check_access(array(1))){?>
        <input type="button" value="В архив" onclick="if(confirm('Переместить в архив?')){document.location='last_closed_tasks.php?id=<?//echo @$_GET['id']?>&archive=1';}">
        <?//}?>
        </td>
    </tr>-->
    </table>
    <td>
    </tr>
    </table>
