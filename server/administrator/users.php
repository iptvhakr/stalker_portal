<?php
session_start();

ob_start();

include "./common.php";

$error = '';
$last_action = '';

Admin::checkAuth();

Admin::checkAccess(AdminAccess::ACCESS_VIEW);

$search = @$_GET['search'];
$letter = @$_GET['letter'];

if (@$_GET['action'] == 'cut_off' && !Config::getSafe('deny_change_user_status', false)){

    Admin::checkAccess(AdminAccess::ACCESS_CONTEXT_ACTION);

    cut_off_user(@$_GET['id']);
    header("Location: users.php?search=".$_GET['search']);
    exit();
}

if (@$_GET['del'] && !Config::getSafe('deny_delete_user', false)){

    Admin::checkAccess(AdminAccess::ACCESS_DELETE);

    $id = intval(@$_GET['id']);

    Mysql::getInstance()->delete('users', array('id' => $id));

    Mysql::getInstance()->delete('fav_itv', array('uid' => $id));
    Mysql::getInstance()->delete('fav_vclub', array('uid' => $id));
    Mysql::getInstance()->delete('media_favorites', array('uid' => $id));
    
    header("Location: users.php?search=".$_GET['search']."&page=".$_GET['page']);
    exit();
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

table.list tr:nth-child(odd) {
    background: #FFFFFF;
}

table.list tr:nth-child(even) {
    background: #EFF5FB;
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
<title><?= _('Users')?></title>
</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0" width="760">
<tr>
    <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;<?= _('Users')?>&nbsp;</b></font>
    </td>
</tr>
<tr>
    <td width="100%" align="left" valign="bottom">
        <a href="index.php"><< <?= _('Back')?></a> | <a href="user.php"><?= _('Add')?></a> | <a href="testers.php"><?= _('Testers')?></a> | <a href="stbgroups.php"><?= _('Stb groups')?></a> | <a href="all_userlog.php"><?= _('All logs')?></a> | <a href="today_user_status_report.php" target="_blank"><?= _('Report')?></a> | <a href="subscribe_import.php"><?= _('Subscription import')?></a>
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
function page_bar(){
    global $MAX_PAGE_ITEMS;
    global $page;
    global $total_pages;
    
    $page_bar = '';

    for($i = 1; $i <= $total_pages; $i++){
        if(($i-1) != $page){
            $page_bar .= ' <a href="?sort_by='.@$_GET['sort_by'].'&page='.($i-1).'&search='.@$_GET['search'].'&letter='.@$_GET['letter'].'&yy='.@$_GET['yy'].'&mm='.@$_GET['mm'].'&dd='.@$_GET['dd'].'&hh='.@$_GET['hh'].'&ii='.@$_GET['ii'].'">'.$i.'</a> |';
        }
        else
        {
            $page_bar .= '<b> '.$i.' </b>|';
        }
    }
    return $page_bar;
}

function get_last_time($time){
    $time_ts = datetime2timestamp($time);
    
    $time_now = time();
    
    $time_delta_s = $time_now - $time_ts;
    $str = '';
    
    $hh = floor($time_delta_s/3600);
    $ii = floor(($time_delta_s-$hh*3600)/60);
    
    if ($ii<10){
        $ii = '0'.$ii;
    }
    
    $ss = $time_delta_s-$hh*3600-$ii*60;
        
    if ($ss<10){
        $ss = '0'.$ss;
    }
    
    $str = $hh.':'.$ii.':'.$ss;
    return $str;
}

function get_cur_media($media_id){
    $media = array(
        0 => '--',
        1 => _('TV'),
        2 => _('Video'),
        3 => _('Karaoke'),
        4 => _('Audio'),
        5 => _('Radio'),
        6 => _('My records'),
        7 => _('Records'),
        9 => 'ad',
        10 => _('Media browser'),
        11 => _('Tv archive'),
        12 => _('Records'),
        14 => _('TimeShift'),
        20 => _('Infoportal'),
        21 => _('Infoportal'),
        22 => _('Infoportal'),
        23 => _('Infoportal'),
        24 => _('Infoportal'),
        25 => _('Infoportal')
    );
    
    if (!empty($media[$media_id])){
        return $media[$media_id];
    }else{
        return 'media_id: '.$media_id;
    }
}

function construct_YY(){
    if (!@$_GET['yy']){
        $year = date("Y");
    }else{
        $year = @$_GET['yy'];
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
    if (!@$_GET['mm']){
        $mon = date("n");
    }else{
        $mon = @$_GET['mm'];
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
    if (!@$_GET['dd']){
        $day = date("j");
    }else{
        $day = @$_GET['dd'];
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

function construct_HH(){
    if (!@$_GET['hh']){
        $hour = date("H");
    }else{
        $hour = @$_GET['hh'];
    }
    
    for ($i=0;$i<=24;$i++){
        if($i<10){
            $i = '0'.$i;
        }
        if ($i == $hour) {
            $hour .= "<option value='".$i."' selected>".$i."</option>";	
        }else{
            $hour .= "<option value='".$i."'>".$i."</option>";
        }
    }
    return $hour;
}

function construct_II(){
    if (!@$_GET['ii']){
        $minutes = date("i");
    }else{
        $minutes = @$_GET['ii'];
    }
    
    $opt = '';
    for ($i=0;$i<=59;$i++){
        if($i<10){
            $i = '0'.$i;
        }
        if ($i == $minutes) {
            $opt .= "<option value='".$i."' selected>".$i."</option>\n";	
        }else{
            $opt .= "<option value='".$i."'>".$i."</option>\n";
        }
    }
    return $opt;
}

function construct_time(){
    if (@$_GET['yy'] && @$_GET['mm'] && @$_GET['dd'] && @$_GET['hh'] && @$_GET['ii']){
        $time = $_GET['yy'].'-'.$_GET['mm'].'-'.$_GET['dd'].' '.$_GET['hh'].':'.$_GET['ii'];
        return $time;
    }else{
        return 0;
    }
}

function parse_param($action, $param){

    if($action == 'play'){
        $sub_param = substr($param, 0, 3);
        
        if ($sub_param == 'rtp'){

            $channel = Mysql::getInstance()->from('itv')->where(array('cmd' => $param))->get()->first();

            $name = '['._('Channel').'] '.$channel['name'];
            
        }else if ($sub_param == 'aut'){
            preg_match("/(\d+)\.[a-z]*$/", $param, $tmp_arr);
            $media_id = $tmp_arr[1];

            $video = Video::getById($media_id);

            $name = '['._('Video').'] '.$video['name'];
            
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

function cut_off_user($id){
    
    $status = get_user_status($id);
    $event = new SysEvent();
    $event->setUserListById($id);
    if ($status == 1){
        set_user_status($id, 0);
        $event->sendCutOn();
    }else{
        set_user_status($id, 1);
        $event->sendCutOff();
    }
}

function get_user_color($id){
    $status = get_user_status($id);
    $str = '';
    if ($status == 0){
        $str = '<font color="green">On</font>';
    }else if ($status == 1){
        $str = '<font color="red">Off</font>';
    }else{
        $str = '<font color="grey">Unknown</font>';
    }
    return $str;
}

function get_user_status($id){
    $stb = Stb::getById($id);
    return $stb['status'];
}

function set_user_status($id, $status){
    return Mysql::getInstance()->update('users', array('status' => $status, 'last_change_status' => 'NOW()'), array('id' => $id));
}

$page=@$_REQUEST['page']+0;
$MAX_PAGE_ITEMS = 30;

$where = '';
if ($search){
    $where = 'where mac like "%'.$search.'%" or ip like "%'.$search.'%" or login like "%'.$search.'%" or ls like "%'.$search.'%" or users.name like "%'.$search.'%" or fname like "%'.$search.'%"';
}

$from_time = construct_time();
if ($from_time){
    $where .= "where last_active<='$from_time'";
}

$now_timestamp = time() - Config::get('watchdog_timeout')*2;
$now_time = date("Y-m-d H:i:s", $now_timestamp);
switch (@$_GET['sort_by']){
    case 'online':{
            add_where($where, " keep_alive>'$now_time' order by id");
            break;
        }
    case 'offline':{
            add_where($where, " keep_alive<'$now_time' order by id");
            break;
        }
    case 'on':{
        add_where($where, " status=0 order by id");
        break;
        }
    case 'off':{
        add_where($where, " status=1 order by id");
        break;
        }
    case 'iptv':{
            add_where($where, " keep_alive>'$now_time' and now_playing_type=1 order by id");
            break;
        }
    case 'video':{
            add_where($where, " keep_alive>'$now_time' and now_playing_type=2 order by id");
            break;
        }
    case 'audioclub':{
        add_where($where, " keep_alive>'$now_time' and now_playing_type=4 order by id");
            break;
        }
    case 'ad':{
            add_where($where, " keep_alive>'$now_time' and now_playing_type=9 order by id");
            break;
        }
    case 'karaoke':{
            add_where($where, " keep_alive>'$now_time' and now_playing_type=3 order by id");
            break;
        }
    case 'radio':{
            add_where($where, " keep_alive>'$now_time' and now_playing_type=5 order by id");
            break;
        }
    case 'my_records':{
            add_where($where, " keep_alive>'$now_time' and now_playing_type=6 order by id");
            break;
        }
    case 'shared_records':{
            add_where($where, " keep_alive>'$now_time' and now_playing_type=7 order by id");
            break;
        }
    case 'city_info':{
            add_where($where, " keep_alive>'$now_time' and now_playing_type=20 order by id");
            break;
        }
    case 'anec_page':{
            add_where($where, " keep_alive>'$now_time' and now_playing_type=21 order by id");
            break;
        }
    case 'weather_page':{
            add_where($where, " keep_alive>'$now_time' and now_playing_type=22 order by id");
            break;
        }
    case 'game_page':{
            add_where($where, " keep_alive>'$now_time' and now_playing_type=23 order by id");
            break;
        }
    case 'horoscope_page':{
            add_where($where, " keep_alive>'$now_time' and now_playing_type=24 order by id");
            break;
        }
    case 'course_page':{
            add_where($where, " keep_alive>'$now_time' and now_playing_type=25 order by id");
            break;
        }
    case 'infoportal':{
            add_where($where, " keep_alive>'$now_time' and now_playing_type>=20 and now_playing_type<=29 order by id");
            break;
        }
    case 'tv_archive':{
            add_where($where, " keep_alive>'$now_time' and now_playing_type=11 order by id");
            break;
        }
    case 'records':{
            add_where($where, " keep_alive>'$now_time' and now_playing_type=12 order by id");
            break;
        }
    case 'timeshift':{
        add_where($where, " keep_alive>'$now_time' and now_playing_type=14 order by id");
        break;
    }
    case 'none':{
        }
    default:{

    }
}
//echo $where;
$query = "select * from users $where";

$total_items = Mysql::getInstance()->query($query)->count();

$page_offset=$page*$MAX_PAGE_ITEMS;
$total_pages=(int)($total_items/$MAX_PAGE_ITEMS+0.999999);

if (!$where){
    $where = 'order by id';
}

$query = "select users.*, tariff_plan.name as tariff_plan_name from users left join tariff_plan on tariff_plan.id=tariff_plan_id $where LIMIT $page_offset, $MAX_PAGE_ITEMS";
//echo $query;
$users = Mysql::getInstance()->query($query);

function add_where(&$where, $str){
    if ($where){
        $where .= " and $str";
    }else{
        $where .= " where $str";
    }
}
?>
<script type="text/javascript">
function sort_page(){
    var opt_sort = document.getElementById('sort_by');
    document.location = 'users.php?sort_by='+opt_sort.options[opt_sort.selectedIndex].value+<?echo '\'&search='.@$_GET['search'].'&letter='.@$_GET['letter'].'&yy='.@$_GET['yy'].'&mm='.@$_GET['mm'].'&dd='.@$_GET['dd'].'&hh='.@$_GET['hh'].'&ii='.@$_GET['ii'].'\';'?>;
}
</script>
<table border="0" align="center" width="620">
    <tr>
        <td>
            <form action="" method="GET">
            <input type="text" name="search" value="<? echo $search ?>"><input type="submit" value="<?= htmlspecialchars(_('Search'), ENT_QUOTES)?>">&nbsp;<font color="Gray"><?= _('search by MAC, IP, login or account number')?></font>
            </form>
        <td>
    </tr>
    <tr>
        <td>
            <select id="sort_by" onchange="sort_page()">
                <option value="none"><?= _('without sorting')?>
                <option value="online" <? if (@$_GET['sort_by'] == 'online') echo 'selected' ?>>online
                <option value="offline" <? if (@$_GET['sort_by'] == 'offline') echo 'selected' ?>>offline
                <option value="on" <? if (@$_GET['sort_by'] == 'on') echo 'selected' ?>>on
                <option value="off" <? if (@$_GET['sort_by'] == 'off') echo 'selected' ?>>off
                <option value="iptv" <? if (@$_GET['sort_by'] == 'iptv') echo 'selected' ?>>iptv
                <option value="video" <? if (@$_GET['sort_by'] == 'video') echo 'selected' ?>>video
                <option value="audioclub" <? if (@$_GET['sort_by'] == 'audioclub') echo 'selected' ?>>audioclub
                <option value="radio" <? if (@$_GET['sort_by'] == 'radio') echo 'selected' ?>>radio
                <option value="karaoke" <? if (@$_GET['sort_by'] == 'karaoke') echo 'selected' ?>>karaoke
                <option value="records" <? if (@$_GET['sort_by'] == 'records') echo 'selected' ?>>records
                <!--<option value="my_records" <?/* if (@$_GET['sort_by'] == 'my_records') echo 'selected' */?>>my records-->
                <!--<option value="shared_records" <?/* if (@$_GET['sort_by'] == 'shared_records') echo 'selected' */?>>shared records
                <option value="infoportal" <?/* if (@$_GET['sort_by'] == 'infoportal') echo 'selected' */?>>infoportal
                <option value="city_info" <?/* if (@$_GET['sort_by'] == 'city_info') echo 'selected' */?>>city_info
                <option value="anec_page" <?/* if (@$_GET['sort_by'] == 'anec_page') echo 'selected' */?>>anec_page
                <option value="weather_page" <?/* if (@$_GET['sort_by'] == 'weather_page') echo 'selected' */?>>weather_page
                <option value="game_page" <?/* if (@$_GET['sort_by'] == 'game_page') echo 'selected' */?>>game_page
                <option value="horoscope_page" <?/* if (@$_GET['sort_by'] == 'horoscope_page') echo 'selected' */?>>horoscope_page
                <option value="course_page" <?/* if (@$_GET['sort_by'] == 'course_page') echo 'selected' */?>>course_page
                <option value="ad" <?/* if (@$_GET['sort_by'] == 'ad') echo 'selected' */?>>ad-->
                <option value="tv_archive" <? if (@$_GET['sort_by'] == 'tv_archive') echo 'selected' ?>>tv_archive
                <option value="timeshift" <? if (@$_GET['sort_by'] == 'timeshift') echo 'selected' ?>>timeshift
            </select>
            <br>
            <br>
        <td>
    </tr>
    <tr>
        <td>
            <form action="" method="GET">
            <select name="yy" id="yy">
                <? echo construct_YY()?>
            </select>
            <select name="mm" id="mm">
                <? echo construct_MM()?>
            </select>
            <select name="dd" id="dd">
                <? echo construct_DD()?>
            </select>&nbsp;&nbsp;&nbsp;
            <select name="hh" id="hh">
                <? echo construct_HH()?>
            </select>:
            <select name="ii" id="ii">
                <? echo construct_II()?>
            </select>
            <input type="submit" value="<?= htmlspecialchars(_('Search'), ENT_QUOTES)?>"> <font color="Gray"><?= _('search inactive users')?></font>
            </form>
        <td>
    </tr>
</table>

<?
echo "<center><table class='list' cellpadding='3' cellspacing='0' width='1100'>\n";
echo "<tr>";
//echo "<td class='list'><b>id</b></td>\n";
echo "<td class='list'><b>#</b></td>\n";
echo "<td class='list'><b>MAC</b></td>\n";
echo "<td class='list'><b>IP</b></td>\n";
echo "<td class='list'><b>Login</b></td>\n";
echo "<td class='list'><b>Account</b></td>\n";
echo "<td class='list'><b>Name</b></td>\n";
echo "<td class='list'><b>Tariff</b></td>\n";
echo "<td class='list'><b>"._('Type')."</b></td>\n";
echo "<td class='list' width='250'><b>"._('Media')."</b></td>\n";
echo "<td class='list'><b>"._('Time from<br>last playback')."</b></td>\n";
echo "<td class='list'><b>&nbsp;</b></td>\n";
echo "<td class='list'><b>&nbsp;</b></td>\n";
echo "<td class='list'><b>"._('Last change<br>of status')."</b></td>\n";
echo "</tr>\n";
$i=0+$MAX_PAGE_ITEMS*$page;

while($arr = $users->next()){
    $i++;

    $now_playing_content = htmlspecialchars($arr['now_playing_content']);
    
    if ($arr['now_playing_type'] == 2 && $arr['storage_name']){
        $now_playing_content = '['.$arr['storage_name'].'] '.$now_playing_content;
    }

    $status = check_keep_alive($arr['keep_alive']);
    
    echo "<tr>";
    //echo "<td class='list'>".$arr['id']."</td>\n";
    echo "<td class='list'>".$i."</td>\n";
    //echo "<td class='list'><a href='userlog.php?id=".$arr['id']."'>".$arr['mac']."</a></td>\n";
    echo "<td class='list'><a href='profile.php?id=".$arr['id']."'>".$arr['mac']."</a></td>\n";
    echo "<td class='list'><a href='events.php?mac=".$arr['mac']."'>".$arr['ip']."</a></td>\n";
    echo "<td class='list'><a href='profile.php?id=".$arr['id']."'>".$arr['login']."</a></td>\n";
    echo "<td class='list'>".$arr['ls']."</td>\n";
    echo "<td class='list'>".$arr['fname']."</td>\n";
    echo "<td class='list'>".$arr['tariff_plan_name']."</td>\n";
    echo "<td class='list'>".(!$status && Config::getSafe('hide_media_info_for_offline_stb', false) ? '--' : get_cur_media($arr['now_playing_type']))."</td>\n";
    echo "<td class='list'>".(!$status && Config::getSafe('hide_media_info_for_offline_stb', false) ? '' : $now_playing_content)."</td>\n";
    echo "<td class='list'>".get_last_time($arr['now_playing_start'])."</td>\n";
    echo "<td class='list'><b>".($status ? '<font color="Green">online</font>' : '<font color="Red">offline</font>')."</b></td>\n";
    echo "<td class='list' nowrap>";
    if (Admin::isActionAllowed() && !Config::getSafe('deny_change_user_status', false)){
        echo "<a href='users.php?id=".$arr['id']."&search=".@$_GET['search']."&action=cut_off'>".get_user_color($arr['id'])."</a>";
    }else{
        echo "<b>".get_user_color($arr['id'])."</b>";
    }

    if (Admin::isActionAllowed() && !Config::getSafe('deny_delete_user', false)){
        echo "&nbsp;&nbsp;";
        echo "<a href='#' onclick='if(confirm(\""._('Do you really want to delete this record?')."\")){document.location=\"users.php?del=1&id=".$arr['id']."&page=".@$_GET['page']."&search=".@$_GET['search']."\"}'>del</a>";
    }

    echo "</td>\n";
    echo "<td class='list'>".$arr['last_change_status']."</td>\n";
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