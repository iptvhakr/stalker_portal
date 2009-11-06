<?php
session_start();

ob_start();

include "../conf_serv.php";
include "../lib/func.php";

$error = '';

$db = new Database(DB_NAME);

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
</head>
<head>
<title>Уникальные фильмы на <? echo $storage_name ?></title>
</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0">
<tr>
    <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;Уникальные фильмы на <? echo $storage_name ?>&nbsp;</b></font>
    </td>
</tr>
<tr>
    <td width="100%" align="left" valign="bottom">
        <a href="index.php"><< Назад</a> | 
        <?
            if (@$_SESSION['login'] == 'alex' || @$_SESSION['login'] == 'duda'){
                ?>
                <a href="#" onclick='if(confirm("Внимание: Определение уникальных фильмов на <? echo $storage_name ?> займет некоторое время и нагрузит все стораджи!!! Вы уверены?!")){document.location="unique_video.php?storage=<? echo $storage_name ?>"}'>Уникальные фильмы на <? echo $storage_name ?></a>
                <?
            }
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

$sql = "select * from video where status=1 and accessed=1";
$rs  = $db->executeQuery($sql);

$i=1;
echo "<center><table class='list' cellpadding='3' cellspacing='0'>\n";
echo "<tr>";
echo "<td class='list'><b>#</b></td>\n";
echo "<td class='list'><b>Фильм (имя папки)</b></td>\n";
echo "</tr>\n";
while(@$rs->next()){
    $dir = $rs->getCurrentValueByName('path');
    $id  = $rs->getCurrentValueByName('id');
    
    $master = new VideoMaster();
    $good_storages = $master->getAllGoodStoragesForMediaFromNet($id);
    
    if (@$good_storages == $storage_name && count($good_storages) == 1){
        echo "<tr>";
        echo "<td class='list'>".$i."</td>\n";
        echo "<td class='list'>".$dir."</td>\n";
        echo "</tr>\n";
        $i++;
    }
    unset($master);
}
echo "</table>\n";
echo "<table width='700' align='center' border=0>\n";
echo "<tr>\n";
echo "<td width='100%' align='center'>\n";
echo "</td>\n";
echo "</tr>\n";
echo "</table>\n";
echo "</center>\n";