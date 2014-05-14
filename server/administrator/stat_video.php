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
<title><?= _('Video views statistics per month')?></title>
</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0">
<tr>
    <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;<?= _('Video views statistics per month')?>&nbsp;</b></font>
    </td>
</tr>
<tr>
    <td width="100%" align="left" valign="bottom">
        <a href="index.php"><< <?= _('Back')?></a> | <a href="stat_daily_video.php"> <?= _('By days')?></a> | <a href="stat_video_genres.php"> <?= _('By genres')?></a>
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
function count_rate($sarr){
    if (is_array($rate = unserialize($sarr))){
        return array_sum($rate);
    }else{
        return 0;
    }
}

function get_status($id){
    $video = Video::getById($id);
    return $video['status'];
}

function get_status_color($id){
    if (get_status($id)){
        $color = 'green';
    }else{
        $color = 'red';
    }
    
    return $color;
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

function count_storages($id){

    return Mysql::getInstance()
        ->count()
        ->from('storage_cache')
        ->where(array(
            'status'     => 1,
            'media_type' => 'vclub',
            'media_id'   => $id
        ))
        ->get()
        ->counter();
}

$page=@$_REQUEST['page']+0;
$MAX_PAGE_ITEMS = 30;

//$where = 'where status=1 and accessed=1';
$where = 'where accessed=1';
if ($search){
    $where .= ' and name like "%'.$search.'%"';
}

$total_items = Mysql::getInstance()->query("select * from video $where")->count();

$page_offset=$page*$MAX_PAGE_ITEMS;
$total_pages=(int)($total_items/$MAX_PAGE_ITEMS+0.999999);


if(@$_GET['sort_by'] == 'total_counter'){
    $order = 'order by count desc';
}else{
    $order = 'order by counter desc';
}
$query = "select id, name, count, (count_second_0_5+count_first_0_5) as counter, last_played from video $where $order LIMIT $page_offset, $MAX_PAGE_ITEMS";
//echo $query;
$video = Mysql::getInstance()->query($query);
?>
<table border="0" align="center" width="620">
<tr>
<td>
<form action="" method="GET">
<input type="text" name="search" value="<? echo $search ?>"><input type="submit" value="<?= htmlspecialchars(_('Search'), ENT_QUOTES)?>">&nbsp;<font color="Gray"><?= _('search by movie title')?></font>
</form>
</td>
</tr>
</table>
<table>
 <tr>
   <td>
   <script>
   function sort_page(){
      opt_sort = document.getElementById('sort_by');
      url = 'stat_video.php?sort_by='+opt_sort.options[opt_sort.selectedIndex].value+<?echo '\'&search='.@$_GET['search'].'&page='.@$_GET['page'].'\';'?>;
      document.location = url;
   }
   </script>
   <?= _('Sort by')?>
   <select id="sort_by" onchange="sort_page()">
    <option value="counter"><?= _('Views')?></option>
    <option value="total_counter" <? if(@$_GET['sort_by'] == 'total_counter'){echo "selected";} ?>><?= _('Total views')?></option>
   </select>
   </td>
 </tr>
</table>
<?
echo "<center><table class='list' cellpadding='3' cellspacing='0'>\n";
echo "<tr>";
echo "<td class='list'><b>id</b></td>\n";
echo "<td class='list'><b>"._('Title')."</b></td>\n";
echo "<td class='list'><b>"._('Views')."</b></td>\n";
echo "<td class='list'><b>"._('Total views')."</b></td>\n";
echo "<td class='list'><b>"._('Last views')."</b></td>\n";
echo "<td class='list'><b>"._('How many storages')."</b></td>\n";
echo "</tr>\n";
while($arr = $video->next()){

    echo "<tr>";
    echo "<td class='list'>".$arr['id']."</td>\n";
    echo "<td class='list' style='color:".get_status_color($arr['id'])."'>".$arr['name']."</td>\n";
    echo "<td class='list'>".$arr['counter']."</td>\n";
    echo "<td class='list'>".$arr['count']."</td>\n";
    echo "<td class='list'>".$arr['last_played']."</td>\n";
    echo "<td class='list'>".count_storages($arr['id'])."</td>\n";
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