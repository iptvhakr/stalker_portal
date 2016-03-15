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
<title><?= _('Inactive users per month')?></title>
</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0">
<tr>
    <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;<?= _('Inactive users per month')?>&nbsp;</b></font>
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
<td valign="top">
<?
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

$page=@$_REQUEST['page']+0;
$MAX_PAGE_ITEMS = 30;

if ($search){
    $query = 'select * from users where mac like "%'.$search.'%"';
}

$from_time = date("Y-m-d H:i:s",strtotime ("-1 month"));

$not_active_in_tv = Mysql::getInstance()->from('users')->where(array('time_last_play_tv<' => $from_time))->orderby('id')->get();

$not_active_in_video = Mysql::getInstance()->from('users')->where(array('time_last_play_video<' => $from_time))->orderby('id')->get();

?>

<table border="0" align="center">
<tr>
<td valign="top">
<?
$i = 1;
echo "<center><table class='list' cellpadding='3' cellspacing='0'>\n";
echo "<tr>";
echo "<td class='list'><b>#</b></td>\n";
echo "<td class='list'><b>mac</b></td>\n";
echo "<td class='list'><b>"._('Latest TV viewing')."</b></td>\n";
echo "</tr>\n";
while($arr = $not_active_in_tv->next()){

    echo "<tr>";
    echo "<td class='list'>".$i."</td>\n";
    echo "<td class='list'>".$arr['mac']."</td>\n";
    echo "<td class='list'>".$arr['time_last_play_tv']."</td>\n";
    echo "</tr>\n";
    $i++;
}
echo "</table>\n";
echo "</center>\n";
?>
</td>
<td>
&nbsp;
</td>
<td>
<?
echo "<center><table class='list' cellpadding='3' cellspacing='0'>\n";
echo "<tr>";
echo "<td class='list'><b>#</b></td>\n";
echo "<td class='list'><b>mac</b></td>\n";
echo "<td class='list'><b>"._('Latest VIDEO viewing')."</b></td>\n";
echo "</tr>\n";
$i = 1;
while($arr_video = $not_active_in_video->next()){

    echo "<tr>";
    echo "<td class='list'>".$i."</td>\n";
    echo "<td class='list'>".$arr_video['mac']."</td>\n";
    echo "<td class='list'>".$arr_video['time_last_play_video']."</td>\n";
    echo "</tr>\n";
    $i++;
}
echo "</table>\n";
echo "</center>\n";
?>
</td>
</tr>
</table>