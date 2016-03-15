<?php
session_start();

ob_start();

include "./common.php";

use Stalker\Lib\Core\Mysql;

$error = '';

Admin::checkAuth();

Admin::checkAccess(AdminAccess::ACCESS_VIEW);

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
<title><?= _('Video log')?></title>
</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0" width="620">
<tr>
    <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;<?= _('Video log')?>&nbsp;</b></font>
    </td>
</tr>
<tr>
    <td width="100%" align="left" valign="bottom">
        <a href="add_video.php"><< <?= _('Back')?></a>
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

function get_video_name(){
    $id = intval($_GET['id']);

    $video = Video::getById($id);
    return $video['name'];
}

function page_bar(){
    global $MAX_PAGE_ITEMS;
    global $page;
    global $total_pages;
    
    $page_bar = '';

    for($i = 1; $i <= $total_pages; $i++){
        if(($i-1) != $page){
            $page_bar .= ' <a href="?page='.($i-1).'&search='.@$_GET['search'].'&letter='.@$_GET['letter'].'&id='.@$_GET['id'].'&yy='.@$_GET['yy'].'&mm='.@$_GET['mm'].'&dd='.@$_GET['dd'].'">'.$i.'</a> |';
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

$where .= " where video_id=$id";

$page=@$_REQUEST['page']+0;
$MAX_PAGE_ITEMS = 30;

$total_items = Mysql::getInstance()->query("select * from video_log $where")->count();

$page_offset=$page*$MAX_PAGE_ITEMS;
$total_pages=(int)($total_items/$MAX_PAGE_ITEMS+0.999999);

$query = "select video_log.*, administrators.login as login  from video_log left join administrators on video_log.moderator_id=administrators.id $where LIMIT  $page_offset, $MAX_PAGE_ITEMS";
//echo $query;
$video_log = Mysql::getInstance()->query($query);

echo "<center><br>\n";
echo get_video_name();
echo "<table class='list' cellpadding='3' cellspacing='0' width='620'>\n";
echo "<tr>";
echo "<td class='list'><b>"._('Date')."</b></td>\n";
echo "<td class='list'><b>"._('Stb action')."</b></td>\n";
echo "<td class='list'><b>"._('Moderator')."</b></td>\n";
echo "</tr>\n";
while($arr = $video_log->next()){
    echo "<tr>";
    echo "<td class='list' nowrap>".$arr['actiontime']."</td>\n";
    echo "<td class='list'>".$arr['action']."</td>\n";
    echo "<td class='list'>".$arr['login']."</td>\n";
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