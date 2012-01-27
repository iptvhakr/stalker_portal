<?php
session_start();

ob_start();

include "../common.php";
include "../lib/func.php";

$error = '';

$db = new Database();

moderator_access();

$storage_name = @$_GET['storage'];

$sql = "select * from storages where storage_name='$storage_name'";
$rs = $db->executeQuery($sql);
if ($rs->getRowCount() != 1){
    echo '<center><h1>низя!</h1></center>';
    exit();
}

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
<title>Текущие просмотры на сторадже <? echo $storage_name ?></title>
</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0">
<tr>
    <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;Текущие просмотры на сторадже <? echo $storage_name ?>&nbsp;</b></font>
    </td>
</tr>
<tr>
    <td width="100%" align="left" valign="bottom">
        <a href="index.php"><< Назад</a>
        <?
            /*if (@$_SESSION['login'] == 'alex' || @$_SESSION['login'] == 'duda' || check_access()){
                ?>
                 | <a href="#" onclick='if(confirm("Внимание: Определение уникальных фильмов на <? echo $storage_name ?> займет некоторое время и нагрузит все стораджи!!! Вы уверены?!")){document.location="unique_video.php?storage=<? echo $storage_name ?>"}'>Уникальные фильмы на <? echo $storage_name ?></a>
                <?
            }*/
        ?>
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
function page_bar(){
    global $MAX_PAGE_ITEMS;
    global $page;
    global $total_pages;
    
    $page_bar = '';

    for($i = 1; $i <= $total_pages; $i++){
        if(($i-1) != $page){
            $page_bar .= ' <a href="?storage='.@$_GET['storage'].'&page='.($i-1).'">'.$i.'</a> |';
        }
        else
        {
            $page_bar .= '<b> '.$i.' </b>|';
        }
    }
    return $page_bar;
}

$page=@$_REQUEST['page']+0;
$MAX_PAGE_ITEMS = 30;

$where = " where now_playing_type=".intval($_GET['type'])." and storage_name='$storage_name' and UNIX_TIMESTAMP(keep_alive)>UNIX_TIMESTAMP(NOW())-120";

$query = "select * from users $where";

$rs = $db->executeQuery($query);
$total_items = $rs->getRowCount();

$page_offset=$page*$MAX_PAGE_ITEMS;
$total_pages=(int)($total_items/$MAX_PAGE_ITEMS+0.999999);

$query = "select * from users $where order by mac LIMIT $page_offset, $MAX_PAGE_ITEMS";
//echo $query;
$rs = $db->executeQuery($query);


echo "<center><table class='list' cellpadding='3' cellspacing='0'>\n";
echo "<tr>";
echo "<td class='list'><b>MAC</b></td>\n";
echo "<td class='list'><b>Фильм</b></td>\n";
echo "<td class='list'><b>Начало просмотра</b></td>\n";
echo "</tr>\n";
while(@$rs->next()){
    
    $arr=$rs->getCurrentValuesAsHash();
    
    echo "<tr>";
    echo "<td class='list'>".$arr['mac']."</td>\n";
    echo "<td class='list'>".$arr['now_playing_content']."</td>\n";
    echo "<td class='list'>".$arr['now_playing_start']."</td>\n";
    echo "</tr>\n";
}
echo "</table>\n";
echo "<table width='700' align='center' border=0>\n";
echo "<tr>\n";
echo "<td width='100%' align='center'>\n";
echo page_bar();
echo "</td>\n";
echo "</tr>\n";
echo "</table>\n";
echo "</center>\n";