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

if (@$_GET['reset'] && @$_GET['media_type'] && @$_GET['media_id']){

    Admin::checkAccess(AdminAccess::ACCESS_CONTEXT_ACTION);

    Mysql::getInstance()->update('media_claims',
        array(
            'sound_counter' => 0,
            'video_counter' => 0,
            'no_epg'        => 0,
            'wrong_epg'     => 0
        ),
        array('media_id' => intval($_GET['media_id']))
    );

    if ($_SERVER['HTTP_REFERER']){
        $location = $_SERVER['HTTP_REFERER'];
    }else{
        $location = 'claims.php';
    }
    header("Location: ".$location);
    exit;
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
</style>
<title><?= _('Claims')?></title>
</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0">
<tr>
    <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;<?= _('Claims')?>&nbsp;</b></font>
    </td>
</tr>
<tr>
    <td width="100%" align="left" valign="bottom">
        <a href="index.php"><< <?= _('Back')?></a> | <a href="claims_log.php"><?= _('Log')?></a>
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
            $page_bar .= ' <a href="?page='.($i-1).'&search='.@$_GET['search'].'&letter='.@$_GET['letter'].'&sort_by='.@$_GET['sort_by'].'">'.$i.'</a> |';
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

$where = '';

$total_items = Mysql::getInstance()->query("select * from daily_media_claims $where")->count();

$page_offset=$page*$MAX_PAGE_ITEMS;
$total_pages=(int)($total_items/$MAX_PAGE_ITEMS+0.999999);

$query = "select * from daily_media_claims $where order by date desc LIMIT $page_offset, $MAX_PAGE_ITEMS";

$all_claims = Mysql::getInstance()->query($query);

?>
<table border="0" align="center" width="620">
<tr>
<td>
</td>
</tr>
</table>

<?
echo "<center><table class='list' cellpadding='3' cellspacing='0'>\n";
echo "<tr>";
echo "<td class='list'><b>"._('Date')."</b></td>\n";
echo "<td class='list'><b>"._('Video-club sound')."</b></td>\n";
echo "<td class='list'><b>"._('Video-club video')."</b></td>\n";
echo "<td class='list'><b>"._('TV sound')."</b></td>\n";
echo "<td class='list'><b>"._('TV video')."</b></td>\n";
echo "<td class='list'><b>"._('No epg')."</b></td>\n";
echo "<td class='list'><b>"._('Wrong epg')."</b></td>\n";
echo "<td class='list'><b>"._('Karaoke sound')."</b></td>\n";
echo "<td class='list'><b>"._('Karaoke video')."</b></td>\n";
echo "</tr>\n";
while($arr = $all_claims->next()){

    echo "<tr>";
    echo "<td class='list'>".$arr['date']."</td>\n";
    echo "<td class='list'><a href='claims_log.php?date=".$arr['date']."&type=sound&media_type=vclub'>".$arr['vclub_sound']."</a></td>\n";
    echo "<td class='list'><a href='claims_log.php?date=".$arr['date']."&type=video&media_type=vclub'>".$arr['vclub_video']."</a></td>\n";
    echo "<td class='list'><a href='claims_log.php?date=".$arr['date']."&type=sound&media_type=itv'>".$arr['itv_sound']."</a></td>\n";
    echo "<td class='list'><a href='claims_log.php?date=".$arr['date']."&type=video&media_type=itv'>".$arr['itv_video']."</a></td>\n";
    echo "<td class='list'><a href='claims_log.php?date=".$arr['date']."&type=no_epg&media_type=itv'>".$arr['no_epg']."</a></td>\n";
    echo "<td class='list'><a href='claims_log.php?date=".$arr['date']."&type=wrong_epg&media_type=itv'>".$arr['wrong_epg']."</a></td>\n";
    echo "<td class='list'><a href='claims_log.php?date=".$arr['date']."&type=sound&media_type=karaoke'>".$arr['karaoke_sound']."</a></td>\n";
    echo "<td class='list'><a href='claims_log.php?date=".$arr['date']."&type=video&media_type=karaoke'>".$arr['karaoke_video']."</a></td>\n";
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