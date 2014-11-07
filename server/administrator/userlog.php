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
<title><?= _('User logs')?></title>
</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0" width="620">
<tr>
    <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;<?= _('User logs')?>&nbsp;</b></font>
    </td>
</tr>
<tr>
    <td width="100%" align="left" valign="bottom">
        <a href="profile.php?id=<?echo @$_GET['id']?>"><< <?= _('Back')?></a>
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
function get_mac_by_id(){
    $stb = Stb::getById((int) $_GET['id']);
    return $stb['mac'];
}

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

if (!isset_date()){
    $date = date("Y-m-d");
}else{
    $date = $_GET['yy']."-".$_GET['mm']."-".$_GET['dd'];
}

$time_from = $date." 00:00:00";
$time_to = $date." 23:59:59";

$where .= "where time > '$time_from' and time < '$time_to'";

$mac = get_mac_by_id();

$where .= " and mac='$mac'";

//$where .= " and action<>'play_now()' and action<>'create_link()' and action<>'create_link' ";
$where .= " and action<>'create_link()' and action<>'create_link' ";

$page=@$_REQUEST['page']+0;
$MAX_PAGE_ITEMS = 30;

$total_items = Mysql::getInstance()->query("select * from user_log $where")->count();

$page_offset=$page*$MAX_PAGE_ITEMS;
$total_pages=(int)($total_items/$MAX_PAGE_ITEMS+0.999999);

$query = "select * from user_log $where order by time desc LIMIT  $page_offset, $MAX_PAGE_ITEMS";
//echo $query;
$log = Mysql::getInstance()->query($query);

function construct_YY(){
    if (empty($_GET['yy'])){
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
    if (empty($_GET['mm'])){
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
    if (empty($_GET['dd'])){
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

function parse_param($action, $param, $type){

    if($action == 'play'){
        
        switch ($type){
            case 1: // TV

                $channel = Mysql::getInstance()
                    ->from('itv')
                    ->where(array(
                        'cmd'    => $param,
                        'status' => 1
                    ))
                    ->get()->first();
                
                if (!empty($channel)){
                    $ch_name = $channel['name'];
                }else{
                    $ch_name = htmlspecialchars($param);
                }
                
                $name = '['._('Channel').'] '.$ch_name;
                break;
            case 2: // Video Club

                if (!preg_match("/(\d+)\.[a-z0-9]*$/", $param, $tmp_arr)){
                    $name = $param;
                    break;
                }

                $media_id = $tmp_arr[1];

                $video = Video::getById($media_id);

                if (!empty($video)){
                    $video_name = $video['name'];
                }else{
                    $video_name = htmlspecialchars($param);
                }
                
                $name = '['._('Video').'] '.$video_name;
                break;
            case 3: // Karaoke

                preg_match("/(\d+)\.[a-z]*$/", $param, $tmp_arr);
                $karaoke_id = $tmp_arr[1];

                $karaoke = Karaoke::getById($karaoke_id);

                if (!empty($karaoke)){
                    $karaoke_name = $karaoke['name'];
                }else{
                    $karaoke_name = htmlspecialchars($param);
                }
                
                $name = '['._('Karaoke').'] '.$karaoke_name;
                break;
            case 4: // Audio Club

                preg_match("/(\d+).mp3$/", $param, $tmp_arr);
                $audio_id = $tmp_arr[1];

                $audio = Mysql::getInstance()->from('audio')->where(array('id' => $audio_id))->get()->first();

                if (!empty($audio)){
                    $audio_name = $audio['name'];
                }else{
                    $audio_name = htmlspecialchars($param);
                }
                
                $name = '['._('Audio').'] '.$audio_name;
                break;
            case 5: // Radio

                $radio = Mysql::getInstance()->from('radio')->where(array('cmd' => $param, 'status' => 1))->get()->first();

                if (empty($radio)){
                    $ch_name = $radio['name'];
                }else{
                    $ch_name = htmlspecialchars($param);
                }
                
                $name = '['._('Radio').'] '.$ch_name;
                break;
            case 6: // My Records

                preg_match("/\/(\d+).mpg/", $param, $tmp_arr);
                $my_record_id = $tmp_arr[1];

                $record = Mysql::getInstance()
                    ->select('t_start, itv.name')
                    ->from('users_rec')
                    ->join('itv', 'users_rec.ch_id', 'itv.id', 'INNER')
                    ->where(array(
                        'users_rec.id' => $my_record_id
                    ))
                    ->get()
                    ->first();
                
                if (!empty($record)){
                    $my_record_name = $record['t_start'].' '.$record['name'];
                }else{
                    $my_record_name = htmlspecialchars($param);
                }
                
                $name = '['._('My records').'] '.$my_record_name;
                break;
            case 7: // Shared Records
                preg_match("/(\d+).mpg$/", $param, $tmp_arr);
                $shared_record_id = $tmp_arr[1];

                $record = Mysql::getInstance()
                    ->from('video_records')
                    ->where(array(
                        'id' => $shared_record_id
                    ))
                    ->get()
                    ->first();

                if (!empty($record)){
                    $shared_record_name = $record['descr'];
                }else{
                    $shared_record_name = htmlspecialchars($param);
                }
                
                $name = '['._('Records').'] '.$shared_record_name;
                break;
            case 8: // Video clips

                preg_match("/(\d+).mpg$/", $param, $tmp_arr);
                $media_id = $tmp_arr[1];

                $video = Mysql::getInstance()->from('video_clips')->where(array('id' => $media_id))->get()->first();

                if (!empty($video)){
                    $video_name = $video['name'];
                }else{
                    $video_name = htmlspecialchars($param);
                }
                
                $name = '['._('Clip').'] '.$video_name;
                break;
            default:
                $name = 'unknown media';
        }
        
        
    }else{
        if($param == '""'){
            $name = '';
        }else{
            $name = htmlspecialchars($param);
        }
    }
    return $name;
}

?>
<script type="text/javascript">
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
    action = 'userlog.php?id=' + <?echo $id?> + '&yy='+yy+'&mm='+mm+'&dd='+dd;
    document.location=action;
}
</script>
<table border="0" align="center" width="620">

    <tr>
        <td align="center">
        <b><?php echo $mac?></b>&nbsp;&nbsp;&nbsp;&nbsp;<?= _('Date')?>
        <select name="yy" id="yy">
            <? echo construct_YY()?>
        </select>
        <select name="mm" id="mm">
            <? echo construct_MM()?>
        </select>
        <select name="dd" id="dd">
            <? echo construct_DD()?>
        </select>
        &nbsp;<input type="button" value="<?= htmlspecialchars(_('Go'), ENT_QUOTES)?>" onclick="load_log()">
        <td>
    </tr>
</table>
<?
echo "<center><table class='list' cellpadding='3' cellspacing='0' width='620'>\n";
echo "<tr>";
echo "<td class='list'><b>"._('Time')."</b></td>\n";
echo "<td class='list'><b>"._('Stb action')."</b></td>\n";
echo "<td class='list'><b>"._('Parameter')."</b></td>\n";
echo "</tr>\n";
while($arr = $log->next()){

    echo "<tr>";
    echo "<td class='list' nowrap>".$arr['time']."</td>\n";
    echo "<td class='list'>".$arr['action']."</td>\n";
    echo "<td class='list'>".parse_param($arr['action'], $arr['param'], $arr['type'])."</td>\n";
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