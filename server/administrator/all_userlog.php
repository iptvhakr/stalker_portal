<?php
session_start();

ob_start();

include "./common.php";

$error = '';

Admin::checkAuth();

Admin::checkAccess(AdminAccess::ACCESS_VIEW);

$search = @$_GET['search'];
$letter = @$_GET['letter'];
$date = @$_GET['date'];
$id = @$_GET['id'];

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
<title>
<?= _('All users log')?>
</title>
</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0" width="620">
<tr>
    <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;<?= _('All users log')?>&nbsp;</b></font>
    </td>
</tr>
<tr>
    <td width="100%" align="left" valign="bottom">
        <a href="users.php"><< <?= _('Back')?></a>
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

function isset_date(){
    if (@$_GET['yy'] && @$_GET['mm'] && @$_GET['dd']){
        return 1;
    }else{
        return 0;
    }
}

function page_bar(){
    global $MAX_PAGE_ITEMS;
    global $page;
    global $total_pages;
    
    $page_bar = '';
    
    if (!$page){
        $start_page = 1;
    }else{
        $start_page = $page;
    }
    if ($page<$total_pages){
        $end_page = $page +2;
    }else{
        $end_page = $total_pages;
    }

    for($i = $start_page; $i <= $end_page; $i++){
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

if (!isset_date()){
    $date = date("Y-m-d");
}else{
    $date = $_GET['yy']."-".$_GET['mm']."-".$_GET['dd'];
}

$time_from = $date." 00:00:00";
$time_to = $date." 24:00:00";

$where .= ' order by time desc';
$page=@$_REQUEST['page']+0;
$MAX_PAGE_ITEMS = 30;

$total_items = Mysql::getInstance()->query("select * from user_log $where")->count();

$page_offset=$page*$MAX_PAGE_ITEMS;
$total_pages=(int)($total_items/$MAX_PAGE_ITEMS+0.999999);

$query = "select * from user_log $where LIMIT $page_offset, $MAX_PAGE_ITEMS";
$user_log = Mysql::getInstance()->query($query)->all();

function construct_YY(){
    if (!$_GET['yy']){
        $year = date("Y");
    }else{
        $year = $_GET['yy'];
    }
    $yy  = "<option value='".$year."'>".$year."</option>";
    $yy .= "<option value='".($year+1)."'>".($year+1)."</option>";
    return $yy;
}

function construct_MM(){
    $month = array(
        1 => _('January'),
        2 => _('February'),
        3 => _('March'),
        4 => _('April'),
        5 => _('May'),
        6 => _('June'),
        7 => _('July'),
        8 => _('August'),
        9 => _('September'),
        10 => _('October'),
        11 => _('November'),
        12 => _('December'),
    );
    $mm = '';
    if (!$_GET['mm']){
        $mon = date("n");
    }else{
        $mon = $_GET['mm'];
    }
    for ($i=1;$i<=12;$i++){
        if ($i == $mon) {
            $mm .= "<option value='".$i."' selected>".$month[$i]."</option>";	
        }else{
            $mm .= "<option value='".$i."'>".$month[$i]."</option>";
        }
    }
    return $mm;
}

function construct_DD(){
    if (!$_GET['dd']){
        $day = date("j");
    }else{
        $day = $_GET['dd'];
    }
    $dd = '';
    for ($i=1;$i<=31;$i++){
        if ($i == $day) {
            $dd .= "<option value='".$i."' selected>".$i."</option>";	
        }else{
            $dd .= "<option value='".$i."'>".$i."</option>";
        }
    }
    return $dd;
}

function parse_param($action, $param){


    if($action == 'play()' || $action == 'play_not_to()'){
        $sub_param = substr($param, 0, 3);
        
        if ($sub_param == 'rtp'){

            $channel = Mysql::getInstance()->from('itv')->where(array('cmd' => $param))->get()->first();

            $name = '['._('Channel').'] ';

            if (!empty($channel)){
                $name .= $channel['name'];
            }else{
                $name .= 'undefined';
            }

        }else if ($sub_param == 'aut'){
            preg_match("/auto \/media\/(\d+)\.[a-z]*$/", $param, $tmp_arr);
            $media_id = $tmp_arr[1];

            $video = Video::getById($media_id);

            $name = '['._('Video').'] ';

            if (!empty($video)){
                $name .= $video['name'];
            }
            
        }else{
            $name = '';
        }
    }else{
        if($param == '""'){
            $name = '';
        }else{
            $name = $param;
        }
    }
    return $name;
}

?>
<script>
function load_log(){
    yy = document.getElementById('yy').options[document.getElementById('yy').selectedIndex].value;
    mm = document.getElementById('mm').options[document.getElementById('mm').selectedIndex].value;
    dd = document.getElementById('dd').options[document.getElementById('dd').selectedIndex].value;
    if (dd < 10){
        dd = '0'+dd
    }
    if (mm < 10){
        mm = '0'+mm
    }
    action = 'userlog.php?yy='+yy+'&mm='+mm+'&dd='+dd+'&id='<?if(@$id){echo $id;}?>;
    document.location=action
}
</script>
<table border="0" align="center" width="620">
    <tr>
        <td align="center">
        <td>
    </tr>
</table>
<?
echo "<center><table class='list' cellpadding='3' cellspacing='0' width='620'>\n";
echo "<tr>";
echo "<td class='list'><b>"._('Time')."</b></td>\n";
echo "<td class='list'><b>MAC</b></td>\n";
echo "<td class='list'><b>"._('Stb action')."</b></td>\n";
echo "<td class='list'><b>"._('Parameter')."</b></td>\n";
echo "</tr>\n";
foreach($user_log as $arr){

    echo "<tr>";
    echo "<td class='list' nowrap>".$arr['time']."</td>\n";
    echo "<td class='list' nowrap>".$arr['mac']."</td>\n";
    echo "<td class='list'>".$arr['action']."</td>\n";
    echo "<td class='list'>".parse_param($arr['action'], $arr['param'])."</td>\n";
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