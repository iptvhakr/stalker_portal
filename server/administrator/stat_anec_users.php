<?php
session_start();

ob_start();

include "./common.php";

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
<title><?= _('Monthly jokes views statistics')?></title>
</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0">
<tr>
    <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;<?= _('Monthly jokes views statistics')?>&nbsp;</b></font>
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
function page_bar(){
    global $MAX_PAGE_ITEMS;
    global $page;
    global $total_pages;
    
    $page_bar = '';

    for($i = 1; $i <= $total_pages; $i++){
        if(($i-1) != $page){
            $page_bar .= ' <a href="?page='.($i-1).'&search='.@$_GET['search'].'">'.$i.'</a> |';
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
if ($search){
    $where = 'where mac like "%'.$search.'%"';
}

$from_time = date("Y-m-d H:i:s",strtotime ("-1 month"));

if ($where){
    $where .= " and readed>'$from_time' ";
}else{
    $where .= " where readed>'$from_time' ";
}

$query = "select mac, count(mac) as count from readed_anec $where group by mac";
$total_items = Mysql::getInstance()->query($query)->count();

$page_offset=$page*$MAX_PAGE_ITEMS;
$total_pages=(int)($total_items/$MAX_PAGE_ITEMS+0.999999);

$query = "select mac, count(mac) as count,max(readed) as readed from readed_anec $where group by mac order by count desc LIMIT $page_offset, $MAX_PAGE_ITEMS";

$readed_anec = Mysql::getInstance()->query($query);

?>
<table border="0" align="center" width="620">
<tr>
<td>
<font color="Gray">* <?= _('Counting at least 5 jokes')?></font><br>
<br>

</td>
</tr>
</table>
<table border="0" align="center" width="620">
<tr>
<td>
<form action="" method="GET">
<input type="text" name="search" value="<? echo $search ?>"><input type="submit" value="<?= htmlspecialchars(_('Search'), ENT_QUOTES)?>">&nbsp;<font color="Gray"><?= _('search by mac')?></font>
</form>
</td>
</tr>
</table>
<?
echo "<center><table class='list' cellpadding='3' cellspacing='0'>\n";
echo "<tr>";
echo "<td class='list'><b>#</b></td>\n";
echo "<td class='list'><b>mac</b></td>\n";
echo "<td class='list'><b>"._('Views')."</b></td>\n";
echo "<td class='list'><b>"._('Last view')."</b></td>\n";
echo "</tr>\n";
$num = $page_offset;
while($arr = $readed_anec->next()){
    $num++;

    echo "<tr>";
    echo "<td class='list'>".$num."</td>\n";
    echo "<td class='list'>".$arr['mac']."</td>\n";
    echo "<td class='list'>".$arr['count']."</td>\n";
    echo "<td class='list'>".$arr['readed']."</td>\n";
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