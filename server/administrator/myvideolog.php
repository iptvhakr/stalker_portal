<?php
session_start();

ob_start();

include "./common.php";

$error = '';

$db = new Database();

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
</style>
<title>Видео лог</title>
</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0" width="620">
<tr>
    <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;Видео лог&nbsp;</b></font>
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
</table>

<?

function get_video_name($id){
    global $db;
    //$id = intval($_GET['id']);
    $query = "select * from video where id=$id";
    $rs = $db->executeQuery($query);
    $name = $rs->getValueByName(0, 'name');
    return $name;
}

function page_bar(){
    global $MAX_PAGE_ITEMS;
    global $page;
    global $total_pages;
    
    $page_bar = '';

    for($i = 1; $i <= $total_pages; $i++){
        if(($i-1) != $page){
            $page_bar .= ' <a href="?page='.($i-1).'">'.$i.'</a> |';
        }
        else
        {
            $page_bar .= '<b> '.$i.' </b>|';
        }
    }
    return $page_bar;
}

$where = '';

$id = intval(@$_GET['id']);

//$where .= " where video_id=$id";
$where .= " where moderator_id=".$_SESSION['uid'];

$page=@$_REQUEST['page']+0;
$MAX_PAGE_ITEMS = 30;

$query = "select * from video_log $where";
$rs = $db->executeQuery($query);
$total_items = $rs->getRowCount();

$page_offset=$page*$MAX_PAGE_ITEMS;
$total_pages=(int)($total_items/$MAX_PAGE_ITEMS+0.999999);

$query = "select video_log.*, administrators.login as login  from video_log left join administrators on video_log.moderator_id=administrators.id $where order by video_log.actiontime desc LIMIT  $page_offset, $MAX_PAGE_ITEMS";
//echo $query;
$rs = $db->executeQuery($query);

echo "<center><br>\n";
//echo get_video_name();
echo "<table class='list' cellpadding='3' cellspacing='0' width='620'>\n";
echo "<tr>";
echo "<td class='list'><b>Дата</b></td>\n";
echo "<td class='list'><b>Название</b></td>\n";
echo "<td class='list'><b>Модератор</b></td>\n";
echo "<td class='list'><b>Действие</b></td>\n";
echo "</tr>\n";
while(@$rs->next()){
    
    $arr=$rs->getCurrentValuesAsHash();
    
    echo "<tr>";
    echo "<td class='list' nowrap>".$arr['actiontime']."</td>\n";
    echo "<td class='list'>".get_video_name($arr['video_id'])."</td>\n";
    echo "<td class='list'>".$arr['login']."</td>\n";
    echo "<td class='list'>".$arr['action']."</td>\n";
    echo "</tr>\n";
}
echo "</table>\n";
echo "<table width='600' align='center' border=0>\n";
echo "<tr>\n";
echo "<td width='100%' align='center'>\n";
echo page_bar();
echo "</td>\n";
echo "</tr>\n";
echo "</table>\n";
echo "</center>\n";
?>