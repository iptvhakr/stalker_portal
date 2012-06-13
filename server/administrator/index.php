<?php
session_start();

ob_start();

include "./common.php";
include "./lib/tasks.php";

$db = new Database();

moderator_access();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title><?= _('Stalker MW admin interface')?></title>
<style type="text/css">
td, table.menu {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 14px;
    text-decoration: none;
    color: #000000;
	border-width: 1px;
    border-style: solid;
    border-color: #E5E5E5;
	background-color:#88BBFF
}
td.other {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 14px;
    text-decoration: none;
    color: #000000;
	border-width: 1px;
    border-style: solid;
    border-color: #E5E5E5;
	background-color:#FFFFFF;
}
.td_stat{
    font-family: Arial, Helvetica, sans-serif;
    font-size: 14px;
    text-decoration: none;
    color: #000000;
    text-align: right;
	border-width: 0px;
    border-style: solid;
    border-color: #E5E5E5;
	background-color:#f5f5f5;
}
.style1 {
	color: #FFFFFF;
	font-weight: bold;
	font-size:16px
}
a{
	color:#FFFFFF;
	font-weight: bold;
	text-decoration:none;
}
a:link{
	color:#FFFFFF;
	font-weight: bold;
}
a:visited{
    color:#FFFFFF;
	font-weight: bold;
	text-decoration:none;
}
a:hover{
	color:#FFFFFF;
	font-weight: bold;
	text-decoration:underline;
}
.lang{
    color: blue !important;
    font-weight: normal !important;
    font-family: Arial, sans-serif;
    font-size: 14px;
}
</style>
<script type="text/javascript" src="js/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="js/jquery.cookies.2.2.0.js"></script>

<script type="text/javascript">

    $(function(){
        $('.lang').click(function(e){
            var lang = $(e.target).attr('lang');
            $.cookies.set('language', lang, {expiresAt: new Date( 2037, 1, 1 )});
            document.location = document.location;
        });
    });

</script>

</head>

<body>
<div style="width: 1100px; margin:0 auto; text-align: right">
    <a href="javascript://" class="lang" lang="en">en</a> | <a href="javascript://" class="lang" lang="ru">ru</a>
</div>
<br>
<br>
<table width="80%" border="0" align="center">
  <tr>
    <td align="right" class="other" style="border-width: 0px;"><?echo date("Y-m-d H:i:s")?></td>
  </tr>
</table>

<table width="80%"  border="1" align="center" cellpadding="3" cellspacing="0" class="menu">
  <tr>
    <td colspan="3" align="center"><span class="style1"><?= _('Sections')?></span></td>
  </tr>
  
  <tr>
    <td width="47%"><div align="center"><a href="add_itv.php"><?= _('IPTV channels')?></a></div></td>
    <td width="6%">&nbsp;</td>
    <td width="47%"><div align="center"><a href="users.php"><?= _('Users')?></a></div></td>
  </tr>
  
  <tr>
    <td><div align="center"><a href="add_video.php"><?= _('VIDEO CLUB')?></a></div></td>
    <td>&nbsp;</td>
    <td align="center"><a href="events.php"><?= _('Events')?></a></td>
  </tr>
  
  <tr>
    <td><div align="center"><a href="add_karaoke.php"><?= _('KARAOKE')?></a></div></td>
    <td>&nbsp;</td>
    <td align="center"><a href="logout.php">[<?echo $_SESSION['login']?>] <?= _('Logout')?></a></td>
  </tr>
  
  <tr>
    <td><div align="center"><a href="add_radio.php"><?= _('RADIO')?></a></div></td>
    <td>&nbsp;</td>
    <td align="center"></td>
  </tr>
  
  <tr>
    <td><!--<div align="center"><a href="ad.php">РЕКЛАМА</a></div>--></td>
    <td>&nbsp;</td>
    <td align="center"></td>
  </tr>

  <tr>
    <td><div align="center"><?if (Config::get('enable_tariff_plans')){?><a href="tariffs.php"><?= _('TARIFFS')?></a><?}?></div></td>
    <td>&nbsp;</td>
    <td align="center"><?
    if (check_access(array(1,2))){
        echo "<a href='tasks.php'>".sprintf(_('Tasks (new messages: %s)'), get_count_unreaded_msgs_by_uid())."</a>";
    }
    ?></td>
  </tr>
  
</table>

<br>
<?
function get_online_users(){
    global $db;
    
    $sql = "select count(id) as online from users where keep_alive>now()-".Config::get('watchdog_timeout')*2;
    $rs=$db->executeQuery($sql);
    $online = @$rs->getValueByName(0, 'online');
    return $online;
}

function get_offline_users(){
    global $db;
    
    $sql = "select count(id) as offline from users where keep_alive<now()-".Config::get('watchdog_timeout')*2;
    $rs=$db->executeQuery($sql);
    $offline = @$rs->getValueByName(0, 'offline');
    return $offline;
}

$online = get_online_users();
$offline = get_offline_users();

$cur_tv = get_cur_playing_type($db, 'itv');
$cur_vclub = get_cur_active_playing_type($db, 'vclub');
$cur_tv_archive = Mysql::getInstance()->from('users')->where(array('UNIX_TIMESTAMP(keep_alive)>' => time() - Config::get('watchdog_timeout')*2, 'now_playing_type' => 11))->get()->count();
$cur_records = Mysql::getInstance()->from('users')->where(array('UNIX_TIMESTAMP(keep_alive)>' => time() - Config::get('watchdog_timeout')*2, 'now_playing_type' => 12))->get()->count();
$cur_aclub = get_cur_active_playing_type($db, 'aclub');
$cur_karaoke = get_cur_active_playing_type($db, 'karaoke');
$cur_radio = get_cur_playing_type($db, 'radio');
$cur_infoportal = get_cur_infoportal($db);

?>
<table width="80%" align="center">
<tr>
<td class="other" width="150">
<table width="150"  border="0" align="left" cellpadding="0" cellspacing="0">
    <tr>
        <td class="td_stat" style="color:green" width="80">online:</td>
        <td class="td_stat"><? echo $online ?></td>
    </tr>
    <tr>
        <td class="td_stat" style="color:red">offline:</td>
        <td class="td_stat"><? echo $offline ?></td>
    </tr>
    <tr>
        <td class="td_stat">&nbsp;</td>
        <td class="td_stat"></td>
    </tr>
    <tr>
        <td class="td_stat"><?= _('tv')?>:</td>
        <td class="td_stat"><? echo $cur_tv ?></td>
    </tr>
    <tr>
        <td class="td_stat"><?= _('videoclub')?>:</td>
        <td class="td_stat"><? echo $cur_vclub ?></td>
    </tr>
    <tr>
        <td colspan="2">
        <table cellpadding="0" cellspacing="0" border="0" width="100%">
        <?
        $sql = "select * from storages";
        $rs=$db->executeQuery($sql);
        while(@$rs->next()){
            $storage_name = $rs->getCurrentValueByName('storage_name');
            $sql_2 = "select count(*) as counter from users where now_playing_type=2 and storage_name='$storage_name' and UNIX_TIMESTAMP(keep_alive)>UNIX_TIMESTAMP(NOW())-".Config::get('watchdog_timeout')*2;
            $rs_2  = $db->executeQuery($sql_2);
            $counter = $rs_2->getValueByName(0, 'counter');
            echo '<tr>';
            echo '<td class="td_stat" width="80"><b>'.$storage_name.'</b>:</td>';
            echo '<td class="td_stat"><a href="users_on_storage.php?storage='.$storage_name.'&type=2" style="color:black">'.$counter.'</a></td>';
            echo '</tr>';
        }
        ?>
        </table>
        </td>
    </tr>
    <!--<tr>
        <td class="td_stat"><?/*= _('audioclub')*/?>:</td>
        <td class="td_stat"><?/* echo $cur_aclub */?></td>
    </tr>-->
    <tr>
        <td class="td_stat"><?= _('karaoke')?>:</td>
        <td class="td_stat"><? echo $cur_karaoke ?></td>
    </tr>
    <tr>
        <td class="td_stat"><?= _('radio')?>:</td>
        <td class="td_stat"><? echo $cur_radio ?></td>
    </tr>
    <tr>
        <td class="td_stat"><?= _('infoportal')?>:</td>
        <td class="td_stat"><? echo $cur_infoportal ?></td>
    </tr>
    </tr>
</table>
</td>

<td class="other" width="150" valign="top" style="background-color: whiteSmoke">
<table width="150"  border="0" align="left" cellpadding="0" cellspacing="0">
    <tr>
        <td class="td_stat" height="64" colspan="2"></td>
    </tr>
    <tr>
        <td class="td_stat"><?= _('tv archive')?>:</td>
        <td class="td_stat"><? echo $cur_tv_archive ?></td>
    </tr>
    <tr>
        <td colspan="2">
        <table cellpadding="0" cellspacing="0" border="0" width="100%">
        <?
        $sql = "select * from storages where for_records=1";
        $rs=$db->executeQuery($sql);
        while(@$rs->next()){
            $storage_name = $rs->getCurrentValueByName('storage_name');
            $sql_2 = "select count(*) as counter from users where now_playing_type=11 and storage_name='$storage_name' and UNIX_TIMESTAMP(keep_alive)>UNIX_TIMESTAMP(NOW())-".Config::get('watchdog_timeout')*2;
            $rs_2  = $db->executeQuery($sql_2);
            $counter = $rs_2->getValueByName(0, 'counter');
            echo '<tr>';
            echo '<td class="td_stat" width="80"><b>'.$storage_name.'</b>:</td>';
            echo '<td class="td_stat"><a href="users_on_storage.php?storage='.$storage_name.'&type=11   " style="color:black">'.$counter.'</a></td>';
            echo '</tr>';
        }
        ?>
        </table>
        </td>
    </tr>
    <tr>
        <td class="td_stat"><?= _('records')?>:</td>
        <td class="td_stat"><? echo $cur_records ?></td>
    </tr>
</table>
</td>

<td class="other">
</td>
<td class="other" width="100">
<form action="users.php" method="GET">
<input type="text" name="search" value=""><input type="submit" value="<?= _('Search')?>"><br><font color="Gray"><?= _('search by MAC or IP')?></font>
</form>
</td>

</tr>
</table>
<br>
<table width="80%"  border="1" align="center" cellpadding="3" cellspacing="0" class="menu">
  <tr>
    <td colspan="3" align="center"><span class="style1"><?= _('Infoportal')?></span></td>
  </tr>
  
  <tr>
    <td width="47%" align="center"><a href="city_info.php"><?= _('City help')?></a></td>
    <td width="6%">&nbsp;</td>
    <td width="47%" align="center"><a href="anecdote.php"><?= _('Jokes')?></a></td>
  </tr>
  
  <!--<tr>
    <td align="center"><a href="add_recipes.php">Рецепты</a></td>
    <td>&nbsp;</td>
    <td align="center">&nbsp;</td>
  </tr>-->

</table>
<br>

<table width="80%"  border="1" align="center" cellpadding="3" cellspacing="0" class="menu">
  <tr>
    <td colspan="3" align="center"><span class="style1"><?= _('Statistics')?></span></td>
  </tr>
  
  <tr>
    <td width="47%" align="center"><a href="stat_video.php"><?= _('Video statistics')?></a></td>
    <td width="6%">&nbsp;</td>
    <td width="47%" align="center"><a href="stat_tv_users.php"><?= _('Users statistics for TV')?></a></td>
  </tr>
  
  <tr>
    <td align="center"><a href="stat_tv.php"><?= _('TV statistics')?></a></td>
    <td>&nbsp;</td>
    <td align="center"><a href="stat_video_users.php"><?= _('Users statistics for VIDEO')?></a></td>
  </tr>
  
  <tr>
    <td align="center"><a href="stat_moderators.php"><?= _('Moderators statistics')?></a></td>
    <td>&nbsp;</td>
    <td align="center"><a href="stat_anec_users.php"><?= _('Users statistics for Jokes')?></a></td>
  </tr>
  
  <tr>
    <td align="center"><a href="claims.php"><?= _('Claims')?></a></td>
    <td>&nbsp;</td>
    <td align="center"><a href="stat_not_active_users.php"><?= _('Inactive users')?></a></td>
  </tr>
</table>
<br>


<? if (@$_SESSION['login'] == 'alex' || @$_SESSION['login'] == 'duda' || @$_SESSION['login'] == 'azmus' || @$_SESSION['login'] == 'vitaxa' || check_access()){ ?>
<table width="80%"  border="1" align="center" cellpadding="3" cellspacing="0" class="menu">

<? if (@$_SESSION['login'] != 'azmus'){ ?>
  <tr>
    <td colspan="3" align="center"><span class="style1"><?= _('Settings')?></span></td>
  </tr>
  
  <tr>
    <td width="47%" align="center"><a href="setting_common.php"><?= _('Firmware auto update')?></a></td>
    <td width="6%">&nbsp;</td>
    <td width="47%" align="center"><a href="storages.php"><?= _('Storages')?></a></td>
  </tr>
<?}?>
  <tr>
    <td width="47%" align="center"><a href="epg_setting.php">EPG</a></td>
    <td width="6%">&nbsp;</td>
    <td width="47%" align="center">&nbsp;</td>
  </tr>
</table>
<?}?>
</body>
</html>
