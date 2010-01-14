<?php
session_start();

ob_start();

include "../conf_serv.php";
include "../lib/func.php";
include "./lib/tasks.php";

$db = new Database(DB_NAME);

moderator_access();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Администрирование абонентского портала</title>
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
</style>
</head>

<body>
<br>
<br>
<br>
<table width="80%" border="0" align="center">
  <tr>
    <td align="right" class="other" style="border-width: 0px;"><?echo date("Y-m-d H:i:s")?></td>
  </tr>
</table>

<table width="80%"  border="1" align="center" cellpadding="3" cellspacing="0" class="menu">
  <tr>
    <td colspan="3" align="center"><span class="style1">Администрирование портала</span></td>
  </tr>
  
  <tr>
    <td width="47%"><div align="center"><a href="add_itv.php">IPTV Каналы</a></div></td>
    <td width="6%">&nbsp;</td>
    <td width="47%"><div align="center"><a href="users.php">Пользователи</a></div></td>
  </tr>
  
  <tr>
    <td><div align="center"><a href="add_video.php">ВИДЕО КЛУБ</a></div></td>
    <td>&nbsp;</td>
    <td align="center"><a href="events.php">События</a></td>
  </tr>
  
  <tr>
    <td><div align="center"><a href="add_karaoke.php">КАРАОКЕ</a></div></td>
    <td>&nbsp;</td>
    <td align="center"><a href="logout.php">[<?echo $_SESSION['login']?>] Выход</a></td>
  </tr>
  
  <tr>
    <td><div align="center"><a href="add_radio.php">РАДИО</a></div></td>
    <td>&nbsp;</td>
    <td align="center"></td>
  </tr>

  <tr>
    <td></td>
    <td>&nbsp;</td>
    <td align="center"><?
    if (check_access(array(1,2))){
        echo "<a href='tasks.php'>Задания (новые сообщения:".get_count_unreaded_msgs_by_uid().")</a>";
    }
    ?></td>
  </tr>
  
</table>

<br>
<?
function get_online_users(){
    global $db;
    
    $sql = "select count(id) as online from users where keep_alive>now()-2*60";
    $rs=$db->executeQuery($sql);
    $online = @$rs->getValueByName(0, 'online');
    return $online;
}

function get_offline_users(){
    global $db;
    
    $sql = "select count(id) as offline from users where keep_alive<now()-2*60";
    $rs=$db->executeQuery($sql);
    $offline = @$rs->getValueByName(0, 'offline');
    return $offline;
}

$online = get_online_users();
$offline = get_offline_users();

$cur_tv = get_cur_playing_type($db, 'itv');
$cur_vclub = get_cur_active_playing_type($db, 'vclub');
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
        <td class="td_stat">тв:</td>
        <td class="td_stat"><? echo $cur_tv ?></td>
    </tr>
    <tr>
        <td class="td_stat">видеоклуб:</td>
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
            $sql_2 = "select count(*) as counter from users where now_playing_type=2 and storage_name='$storage_name' and UNIX_TIMESTAMP(keep_alive)>UNIX_TIMESTAMP(NOW())-120";
            $rs_2  = $db->executeQuery($sql_2);
            $counter = $rs_2->getValueByName(0, 'counter');
            echo '<tr>';
            echo '<td class="td_stat" width="80"><b>'.$storage_name.'</b>:</td>';
            echo '<td class="td_stat"><a href="users_on_storage.php?storage='.$storage_name.'" style="color:black">'.$counter.'</a></td>';
            echo '</tr>';
        }
        ?>
        </table>
        </td>
    </tr>
    <tr>
        <td class="td_stat">аудиоклуб:</td>
        <td class="td_stat"><? echo $cur_aclub ?></td>
    </tr>
    <tr>
        <td class="td_stat">караоке:</td>
        <td class="td_stat"><? echo $cur_karaoke ?></td>
    </tr>
    <tr>
        <td class="td_stat">радио:</td>
        <td class="td_stat"><? echo $cur_radio ?></td>
    </tr>
    <tr>
        <td class="td_stat">инфопортал:</td>
        <td class="td_stat"><? echo $cur_infoportal ?></td>
    </tr>
    </tr>
</table>
</td>

<td class="other">
</td>
<td class="other" width="100">
<form action="users.php" method="GET">
<input type="text" name="search" value=""><input type="submit" value="Поиск"><br><font color="Gray">поиск по MAC или IP</font>
</form>
</td>

</tr>
</table>
<br>
<table width="80%"  border="1" align="center" cellpadding="3" cellspacing="0" class="menu">
  <tr>
    <td colspan="3" align="center"><span class="style1">Инфопортал</span></td>
  </tr>
  
  <tr>
    <td width="47%" align="center"><a href="city_info.php">Городская справка</a></td>
    <td width="6%">&nbsp;</td>
    <td width="47%" align="center"><a href="anecdote.php">Анекдоты</a></td>
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
    <td colspan="3" align="center"><span class="style1">Статистика</span></td>
  </tr>
  
  <tr>
    <td width="47%" align="center"><a href="stat_video.php">Статистика Видео</a></td>
    <td width="6%">&nbsp;</td>
    <td width="47%" align="center"><a href="stat_tv_users.php">Абонентская статистика по ТВ</a></td>
  </tr>
  
  <tr>
    <td align="center"><a href="stat_tv.php">Статистика ТВ</a></td>
    <td>&nbsp;</td>
    <td align="center"><a href="stat_video_users.php">Абонентская статистика по ВИДЕО</a></td>
  </tr>
  
  <tr>
    <td align="center"><a href="stat_moderators.php">Статистика Модераторов</a></td>
    <td>&nbsp;</td>
    <td align="center"><a href="stat_anec_users.php">Абонентская статистика по Анекдотам</a></td>
  </tr>
  
  <tr>
    <td align="center"><a href="claims.php">Жалобы</a></td>
    <td>&nbsp;</td>
    <td align="center"><a href="stat_not_active_users.php">Неактивные абоненты</a></td>
  </tr>
</table>
<br>


<? if (@$_SESSION['login'] == 'alex' || @$_SESSION['login'] == 'duda' || check_access()){ ?>
<table width="80%"  border="1" align="center" cellpadding="3" cellspacing="0" class="menu">
  <tr>
    <td colspan="3" align="center"><span class="style1">Настройки</span></td>
  </tr>
  
  <tr>
    <td width="47%" align="center"><a href="setting_common.php">Общие</a></td>
    <td width="6%">&nbsp;</td>
    <td width="47%" align="center"><a href="storages.php">Хранилища</a></td>
  </tr>
</table>
<?}?>
</body>
</html>
