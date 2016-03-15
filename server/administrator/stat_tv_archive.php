<?php
session_start();

ob_start();

include "./common.php";

use Stalker\Lib\Core\Mysql;

$error = '';

Admin::checkAuth();

Admin::checkAccess(AdminAccess::ACCESS_VIEW);

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
<title><?= _('TV Archive statistics per month')?></title>
</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0">
<tr>
    <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;<?= _('TV Archive statistics per month')?>&nbsp;</b></font>
    </td>
</tr>
<tr>
    <td width="100%" align="left" valign="bottom">
        <a href="index.php"><< <?= _('Back')?></a>
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
$from_time = date("Y-m-d H:i:s",strtotime ("-1 month"));

$played_archive = Mysql::getInstance()
    ->select('itv.name, count(ch_id) as counter, ch_id, SUM(length) as total_duration')
    ->from('played_tv_archive')
    ->join('itv', 'itv.id', 'played_tv_archive.ch_id', 'INNER')
    ->where(array(
        'playtime>=' => $from_time
    ))
    ->groupby('ch_id')
    ->orderby('counter', 'DESC')
    ->get();

echo "<center><table class='list' cellpadding='3' cellspacing='0'>\n";
echo "<tr>";
echo "<td class='list'><b>id</b></td>\n";
echo "<td class='list'><b>"._('Title')."</b></td>\n";
echo "<td class='list'><b>"._('Views')."</b></td>\n";
echo "<td class='list'><b>"._('Total time').", "._('s')."</b></td>\n";
echo "</tr>\n";
while($arr = $played_archive->next()){

    echo "<tr>";
    echo "<td class='list'>".$arr['ch_id']."</td>\n";
    echo "<td class='list'>".$arr['name']."</td>\n";
    echo "<td class='list'>".$arr['counter']."</td>\n";
    echo "<td class='list'>".$arr['total_duration']."</td>\n";
    echo "</tr>\n";
}
echo "</table>\n";
echo "<table width='700' align='center' border=0>\n";
echo "<tr>\n";
echo "<td width='100%' align='center'>\n";
echo "</td>\n";
echo "</tr>\n";
echo "</table>\n";
echo "</center>\n";