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
<title><?= _('Claims log')?></title>
</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0">
<tr>
    <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;<?= _('Claims log')?>&nbsp;</b></font>
    </td>
</tr>
<tr>
    <td width="100%" align="left" valign="bottom">
        <a href="claims.php"><< <?= _('Back')?></a>
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

function get_media_name_by_id($id, $type='vclub'){

    if ($type == 'itv'){
        $table = 'itv';
    }elseif ($type == 'karaoke'){
        $table = 'karaoke';
    }else{
        $table = 'video';
    }

    return Mysql::getInstance()->from($table)->where(array('id' => $id))->get()->first('name');
}

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

if (@$_GET['date']){
    if ($where){
        $where .= ' and ';
    }else{
        $where .= ' where ';
    }
    
    $where .= ' added>="'.$_GET['date'].' 00:00:00" ';
    $where .= ' and added<="'.$_GET['date'].' 23:59:59" ';
}

if (@$_GET['type']){
    if ($where){
        $where .= ' and ';
    }else{
        $where .= ' where ';
    }
    
    $where .= ' type="'.$_GET['type'].'" ';
}

if (@$_GET['media_type']){
    if ($where){
        $where .= ' and ';
    }else{
        $where .= ' where ';
    }
    
    $where .= ' media_type="'.$_GET['media_type'].'" ';
}

$total_items = Mysql::getInstance()->query("select * from media_claims_log $where")->count();

$page_offset=$page*$MAX_PAGE_ITEMS;
$total_pages=(int)($total_items/$MAX_PAGE_ITEMS+0.999999);

$query = "select * from media_claims_log $where order by added desc LIMIT $page_offset, $MAX_PAGE_ITEMS";

$all_log = Mysql::getInstance()->query($query);
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
echo "<td class='list'><b>"._('Type')."</b></td>\n";
echo "<td class='list'><b>"._('Media')."</b></td>\n";
echo "<td class='list'><b>"._('Claim on')."</b></td>\n";
echo "<td class='list'><b>"._('From')."</b></td>\n";
echo "<td class='list'><b>"._('When')."</b></td>\n";
echo "</tr>\n";
while($arr = $all_log->next()){

    echo "<tr>";
    echo "<td class='list'>".$arr['media_type']."</td>\n";
    echo "<td class='list'>".get_media_name_by_id($arr['media_id'], $arr['media_type'])."</td>\n";
    echo "<td class='list'>".$arr['type']."</td>\n";
    echo "<td class='list'><a href='profile.php?id=".$arr['uid']."'>".$arr['uid']."</a></td>\n";
    echo "<td class='list'>".$arr['added']."</td>\n";
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