<?php
session_start();

ob_start();

include "../common.php";
include "../lib/func.php";

$locale = 'ru_RU.utf8';

setlocale(LC_MESSAGES, $locale);
putenv('LC_MESSAGES='.$locale);

bindtextdomain('stb', PROJECT_PATH.'/locale');
textdomain('stb');
bind_textdomain_codeset('stb', 'UTF-8');

$error = '';

$db = new Database();

moderator_access();

if (@$_GET['del']){
    $query = "delete from itv where id=".intval(@$_GET['id']);
    $rs=$db->executeQuery($query);
    header("Location: add_itv.php");
}

if (isset($_GET['status']) && @$_GET['id']){
    $query = "update itv set status='".intval(@$_GET['status'])."' where id=".intval(@$_GET['id']);
    $rs=$db->executeQuery($query);
    header("Location: add_itv.php");
}

if (!$error){
    
    if (@$_POST['censored'] == 'on'){
        $censored = 1;
    }else{
        $censored = 0;
    }

    if (@$_POST['use_http_tmp_link'] == 'on'){
        $use_http_tmp_link = 1;
    }else{
        $use_http_tmp_link = 0;
    }

    if (@$_POST['wowza_tmp_link'] == 'on'){
        $wowza_tmp_link = 1;
    }else{
        $wowza_tmp_link = 0;
    }
    
    if (@$_POST['wowza_dvr'] == 'on'){
        $wowza_dvr = 1;
    }else{
        $wowza_dvr = 0;
    }

    if (@$_POST['enable_tv_archive'] == 'on'){
        $enable_tv_archive = 1;
    }else{
        $enable_tv_archive = 0;
    }

    $enable_monitoring = @intval($_POST['enable_monitoring']);

    $enable_wowza_load_balancing = @intval($_POST['enable_wowza_load_balancing']);

    if (@$_POST['base_ch'] == 'on'){
        $base_ch = 1;
    }else{
        $base_ch = 0;
    }
    
    if (@$_POST['bonus_ch'] == 'on'){
        $bonus_ch = 1;
    }else{
        $bonus_ch = 0;
    }
    
    if (@$_POST['hd'] == 'on'){
        $hd = 1;
    }else{
        $hd = 0;
    }

    if (@$_POST['number'] && !check_number($_POST['number']) && !@$_GET['update']){
        $error = 'Ошибка: Номер канала "'.intval($_POST['number']).'" уже используется';
    }
    
    if (@$_GET['save'] && !$error){
    
        if(@$_GET['cmd'] && @$_GET['name'] && @$_POST['tv_genre_id'] > 0){

            $channel = Itv::getChannelById($ch_id);

            if ($channel['enable_tv_archive'] != $enable_tv_archive || $channel['wowza_dvr'] != $wowza_dvr){

                if ($channel['enable_tv_archive']){

                    if ($channel['wowza_dvr']){
                        $archive = new WowzaTvArchive();
                    }else{
                        $archive = new TvArchive();
                    }

                    $archive->deleteTask($ch_id);
                }
            }
    
            $query = "insert into itv (
                                        name,
                                        number,
                                        use_http_tmp_link,
                                        wowza_tmp_link,
                                        wowza_dvr,
                                        censored,
                                        base_ch,
                                        bonus_ch,
                                        hd,
                                        cost,
                                        cmd,
                                        cmd_1,
                                        cmd_2,
                                        cmd_3,
                                        mc_cmd,
                                        enable_wowza_load_balancing,
                                        enable_tv_archive,
                                        enable_monitoring,
                                        monitoring_url,
                                        descr,
                                        tv_genre_id, 
                                        status,
                                        xmltv_id,
                                        service_id,
                                        volume_correction
                                        ) 
                                values ('".@$_POST['name']."',
                                        '".@$_POST['number']."', 
                                        '".$use_http_tmp_link."',
                                        '".$wowza_tmp_link."',
                                        '".$wowza_dvr."',
                                        '".$censored."',
                                        '".$base_ch."',
                                        '".$bonus_ch."',
                                        '".$hd."',
                                        '".@$_POST['cost']."',
                                        '".(empty($_GET['cmd']) ? $_POST['cmd_1'] : $_GET['cmd'])."',
                                        '".@$_POST['cmd_1']."',
                                        '".@$_POST['cmd_2']."',
                                        '".@$_POST['cmd_3']."',
                                        '".@$_POST['mc_cmd']."',
                                        '".$enable_wowza_load_balancing."',
                                        '".$enable_tv_archive."',
                                        '".$enable_monitoring."',
                                        '".@$_POST['monitoring_url']."',
                                        '".@$_POST['descr']."',
                                        '".@$_POST['tv_genre_id']."', 
                                        1,
                                        '".@$_POST['xmltv_id']."',
                                        '".trim($_POST['service_id'])."',
                                        ".intval($_POST['volume_correction'])."
                                        )";
            echo $query;
            $rs=$db->executeQuery($query);
            //var_dump($rs);
            $ch_id = $rs->getLastInsertId();

            if ($channel['enable_tv_archive'] != $enable_tv_archive || $channel['wowza_dvr'] != $wowza_dvr){

                if ($enable_tv_archive){

                    if ($wowza_dvr){
                        $archive = new WowzaTvArchive();
                    }else{
                        $archive = new TvArchive();
                    }

                    $archive->createTask($ch_id);
                }
            }

            /*if ($wowza_dvr){
                $archive = new WowzaTvArchive();
            }else{
                $archive = new TvArchive();
            }

            if ($enable_tv_archive){
                $archive->createTask($ch_id);
            }else{
                $archive->deleteTask($ch_id);
            }*/
            
            header("Location: add_itv.php");
            exit;
        }
        else{
            $error = 'Ошибка: необходимо заполнить все поля';
        }
    }
    
    if (@$_GET['update'] && !$error){
        
        if(@$_GET['name']){

            $ch_id = intval(@$_GET['id']);

            $channel = Itv::getChannelById($ch_id);

            if ($channel['enable_tv_archive'] != $enable_tv_archive || $channel['wowza_dvr'] != $wowza_dvr){

                if ($channel['enable_tv_archive']){

                    if ($channel['wowza_dvr']){
                        $archive = new WowzaTvArchive();
                    }else{
                        $archive = new TvArchive();
                    }

                    $archive->deleteTask($ch_id);
                }
            }

            $query = "update itv 
                                set name='".$_POST['name']."',
                                cmd='".(empty($_GET['cmd']) ? $_POST['cmd_1'] : $_GET['cmd'])."',
                                cmd_1='".@$_POST['cmd_1']."',
                                cmd_2='".@$_POST['cmd_2']."',
                                cmd_3='".@$_POST['cmd_3']."',
                                mc_cmd='".$_POST['mc_cmd']."',
                                enable_wowza_load_balancing='".$enable_wowza_load_balancing."',
                                enable_tv_archive='".$enable_tv_archive."',
                                enable_monitoring='".$enable_monitoring."',
                                monitoring_url='".$_POST['monitoring_url']."',
                                wowza_tmp_link='".$wowza_tmp_link."',
                                wowza_dvr='".$wowza_dvr."',
                                use_http_tmp_link='".$use_http_tmp_link."',
                                censored='".$censored."',
                                base_ch='".$base_ch."',
                                bonus_ch='".$bonus_ch."', 
                                hd='".$hd."', 
                                cost='".$_POST['cost']."', 
                                number='".$_POST['number']."', 
                                descr='".$_POST['descr']."', 
                                tv_genre_id='".$_POST['tv_genre_id']."',
                                xmltv_id='".$_POST['xmltv_id']."',
                                service_id='".trim($_POST['service_id'])."',
                                volume_correction=".intval($_POST['volume_correction'])."
                            where id=".intval(@$_GET['id']);
            var_dump($query);
            $rs=$db->executeQuery($query);

            if ($channel['enable_tv_archive'] != $enable_tv_archive || $channel['wowza_dvr'] != $wowza_dvr){

                if ($enable_tv_archive){

                    if ($wowza_dvr){
                        $archive = new WowzaTvArchive();
                    }else{
                        $archive = new TvArchive();
                    }

                    $archive->createTask($ch_id);
                }
            }

            header("Location: add_itv.php");
            exit;
        }
        else{
            $error = 'Ошибка: необходимо заполнить все поля';
        }
    }
}

function check_number($num){
    global $db;
    $total_items = 1;
    $query = "select * from itv where number=".intval($num);
    $rs=$db->executeQuery($query);
	$total_items = $rs->getRowCount();
	if ($total_items > 0){
	    return 0;
	}else{
	    return 1;
	}
}

function get_screen_name($addr){
    preg_match("/(\S+)\s(\S+):\/\/(\d+).(\d+).(\d+).(\d+):(\d+)/", $addr, $tmp_arr);
    $img_str = '/iptv/mpg/'.$tmp_arr[6].'_'.$tmp_arr[7].'.mpg';
    return $img_str;
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
<title>
Редактирование списка IPTV каналов
</title>
</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0">
<tr>
    <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;Редактирование списка IPTV каналов&nbsp;</b></font>
    </td>
</tr>
<tr>
    <td width="100%" align="left" valign="bottom">
        <a href="index.php"><< Назад</a>
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
<td align="center">
<div style="display:none;border: 1px solid #E0E0E0;width: 400px; text-align:center;">
Внимание, с 1 апреля вводится подписка на каналы! Во избежание недоразумений менять номера каналов и ставить опции необходимо по согласованию.</div><br>

<?
$query = "select itv.*, tv_genre.title as genres_name, media_claims.media_type, media_claims.media_id, media_claims.sound_counter, media_claims.video_counter from itv left join media_claims on itv.id=media_claims.media_id and media_claims.media_type='itv' inner join tv_genre on itv.tv_genre_id=tv_genre.id group by itv.id order by number";

//echo $query;

$rs=$db->executeQuery($query);
echo "<center><table class='list' cellpadding='3' cellspacing='0'>";
echo "<tr>";
echo "<td class='list'><b>id</b></td>";
echo "<td class='list'><b>Номер</b></td>";
echo "<td class='list'><b>Услуга</b></td>";
echo "<td class='list'><b>Имя</b></td>";
echo "<td class='list'><b>Адрес</b></td>";
//echo "<td class='list'><b>Описание</b></td>";
echo "<td class='list'><b>Жанр</b></td>";
echo "<td class='list'><b>Коррекция звука</b></td>";
echo "<td class='list'><b>Жалобы на<br>звук/видео</b></td>\n";
echo "<td class='list'><b>&nbsp;</b></td>";
echo "</tr>";
while(@$rs->next()){
    
    $arr=$rs->getCurrentValuesAsHash();
    
    echo "<tr ";
    if ($arr['bonus_ch'] == 1){
        echo 'bgcolor="#ffffec"';
    }else{
        if ($arr['base_ch'] == 1){
            
        }else{
            if (strlen($arr['service_id'])<5){
                echo 'bgcolor="#f7f7f7"';
            }else{
                echo 'bgcolor="#ffecec"';
            }
        }
    }
    
    /*else{
        echo 'bgcolor="#ececec"';
    }*/
    echo " >";
    echo "<td class='list'>".$arr['id']."</td>";
    echo "<td class='list'>".$arr['number']."</td>";
    echo "<td class='list'>".$arr['service_id']."</td>";
    //echo "<td class='list'><a href='".get_screen_name($arr['cmd'])."' >".$arr['name']."</a></td>";
    echo "<td class='list' style='color:".get_color($arr)."' title='".get_hint($arr)."'><b>".$arr['name']."</b></td>";
    echo "<td class='list'>".$arr['cmd']."</td>";
    //echo "<td class='list'>".$arr['descr']."</td>";
    echo "<td class='list'>"._($arr['genres_name'])."</td>";
    echo "<td class='list'>".$arr['volume_correction']."</td>";
    
    echo "<td class='list' align='center'>\n";
    if (check_access(array(1))){
        echo "<a href='#' onclick='if(confirm(\"Вы действительно хотите сбросить счетчик жалоб?\")){document.location=\"claims.php?reset=1&media_id=".$arr['media_id']."&media_type=".$arr['media_type']."\"}'>";
    }
    echo "<span style='color:red;font-weight:bold'>".$arr['sound_counter']." / ".$arr['video_counter']."</span>";
    if (check_access(array(1))){
        echo "</a>";
    }
    echo "</td>\n";
    
    echo "<td class='list' nowrap><a href='?edit=1&id=".$arr['id']."#form'>edit</a>&nbsp;&nbsp;";
    //echo "<a href='?del=1&id=".$arr['id']."' >del</a>&nbsp;&nbsp;";
    echo "<a href='#' onclick='if(confirm(\"Удалить данную запись?\")){document.location=\"add_itv.php?del=1&id=".$arr['id']."&letter=".@$_GET['letter']."&search=".@$_GET['search']."\"}'>del</a>&nbsp;&nbsp;\n";
    if ($arr['status']){
        echo "<a href='?status=0&id=".$arr['id']."'><font color='Green'>on</font></a>&nbsp;&nbsp;";
    }else{
        echo "<a href='?status=1&id=".$arr['id']."'><font color='Red'>off</font></a>&nbsp;&nbsp;";
    }
    echo "<a href='add_epg.php?id=".$arr['id']."'>EPG</a>&nbsp;&nbsp;</td>";
    echo "</tr>";
}
echo "</table></center>";

if (@$_GET['edit']){
    $query = "select * from itv where id=".intval(@$_GET['id']);
    $rs=$db->executeQuery($query);
    while(@$rs->next()){
        $arr=$rs->getCurrentValuesAsHash();
        $name     = $arr['name'];
        $number   = $arr['number'];
        $cmd      = $arr['cmd'];
        $mc_cmd   = $arr['mc_cmd'];
        $tv_genre_id = $arr['tv_genre_id'];
        $descr    = $arr['descr'];
        $status   = $arr['status'];
        $status   = $arr['status'];
        $censored = $arr['censored'];
        $base_ch  = $arr['base_ch'];
        $bonus_ch = $arr['bonus_ch'];
        $cost     = $arr['cost'];
        $hd       = $arr['hd'];
        $xmltv_id = $arr['xmltv_id'];
        $service_id = $arr['service_id'];
        $volume_correction = $arr['volume_correction'];
        $use_http_tmp_link = $arr['use_http_tmp_link'];
        $wowza_tmp_link    = $arr['wowza_tmp_link'];
        $wowza_dvr = $arr['wowza_dvr'];
        $enable_tv_archive = $arr['enable_tv_archive'];
        $enable_monitoring = $arr['enable_monitoring'];
        $monitoring_url = $arr['monitoring_url'];
        $enable_wowza_load_balancing = $arr['enable_wowza_load_balancing'];

        if ($use_http_tmp_link){
            $checked_http_tmp_link = 'checked';
        }

        if ($wowza_tmp_link){
            $checked_wowza_tmp_link = 'checked';
        }

        if ($wowza_dvr){
            $checked_wowza_dvr = 'checked';
        }

        if ($enable_tv_archive){
            $checked_enable_tv_archive = 'checked';
        }

        if ($enable_monitoring){
            $checked_enable_monitoring = 'checked';
        }

        if ($enable_wowza_load_balancing){
            $checked_wowza_load_balancing = 'checked';
        }

        if ($censored){
            $checked = 'checked';
        }
        if ($base_ch){
            $checked_base = 'checked';
        }
        if ($bonus_ch){
            $checked_bonus = 'checked';
        }
        if ($hd){
            $checked_hd = 'checked';
        }
    }
}
function get_genres(){
    global $db;
    global $tv_genre_id;
    
    $query = "select * from tv_genre";
    $rs=$db->executeQuery($query);
    $option = '';
    
    while(@$rs->next()){
        $selected = '';
        $arr=$rs->getCurrentValuesAsHash();
        if ($tv_genre_id == $arr['id']){
            $selected = 'selected';
        }
        $option .= "<option value={$arr['id']} $selected>"._($arr['title'])."\n";
    }
    return $option;
}

function get_color($channel){

    if (!$channel['enable_monitoring']){
        return '#5588FF';
    }

    if (time() - strtotime($channel['monitoring_status_updated']) > 3600){
        return '#f4c430';
    }

    if ($channel['monitoring_status'] == 1){
        return 'green';
    }else{
        return 'red';
    }
}

function get_hint($channel){

    if (!$channel['enable_monitoring']){
        return 'не мониторится';
    }

    $diff = time() - strtotime($channel['monitoring_status_updated']);

    if ($diff > 3600){
        return 'больше часа назад';
    }

    if ($diff < 60){
        return 'меньше минуты назад';
    }

    return round($diff/60).' минут назад';
}

?>
<script type="text/javascript">
function save(){
    var form_ = document.getElementById('form_');
    var cmd = '';
    var name = document.getElementById('name').value;
    if (document.getElementById('cmd')){
        cmd = document.getElementById('cmd').value;
    }
    var id = document.getElementById('id').value;
    //descr = document.getElementById('descr').value
    
    var action = 'add_itv.php?name='+name+'&cmd='+cmd+'&id='+id;
    //alert(action)
    if(document.getElementById('action').value == 'edit'){
        action += '&update=1'
    }
    else{
        action += '&save=1'
    }
    
    //alert(action)
    form_.action = action;
    form_.method = 'POST';
    //document.location=action
    form_.submit()
}

function popup(src){
     window.open( src, 'win_'+src, 'width=300,height=200,toolbar=0,location=0,directories=0,menubar=0,scrollbars=0,resizable=1,status=0,fullscreen=0')
}
</script>
<br>
<table align="center" class='list'>
<tr>
    <td>
    &nbsp;
    </td>
</tr>
<tr>
    <td>
    <form id="form_" method="POST">
    <table align="center">
        <tr>
           <td align="right">
            Номер: 
           </td>
           <td>
            <input type="text" name="number" id="number" value="<? echo @$number ?>"  maxlength="3">
           </td>
        </tr>
        <tr>
           <td align="right">
            Название: 
           </td>
           <td>
            <input type="text" name="name" id="name" value="<? echo @$name ?>">
            <input type="hidden" id="id" value="<? echo @$_GET['id'] ?>">
            <input type="hidden" id="action" value="<? if(@$_GET['edit']){echo "edit";} ?>">
           </td>
        </tr>
        
        <tr>
           <td align="right" valign="top">
           Временная HTTP ссылка:
           </td>
           <td>
            <input name="use_http_tmp_link" id="use_http_tmp_link" type="checkbox" <? echo @$checked_http_tmp_link ?> onchange="this.checked ? document.getElementById('wowza_tmp_link_tr').style.display = '' : document.getElementById('wowza_tmp_link_tr').style.display = 'none'" >
            <span id="wowza_tmp_link_tr" style="display: <?echo @$checked_http_tmp_link ? '' : 'none' ?>">
                Поддержка WOWZA:
                <input name="wowza_tmp_link" id="wowza_tmp_link" type="checkbox" <? echo @$checked_wowza_tmp_link ?> >
            </span>
           </td>
        </tr>
        <tr>
           <td align="right" valign="top">
           Ограничение по возрасту:
           </td>
           <td>
            <input name="censored" id="censored" type="checkbox" <? echo @$checked ?> >
           </td>
        </tr>
        <tr>
           <td align="right" valign="top">
           HD: 
           </td>
           <td>
            <input name="hd" id="hd" type="checkbox" <? echo @$checked_hd ?> >
           </td>
        </tr>
        <tr>
           <td align="right" valign="top">
           Базовый канал: 
           </td>
           <td>
            <input name="base_ch" id="base_ch" type="checkbox" <? echo @$checked_base ?> >
           </td>
        </tr>
        <tr>
           <td align="right" valign="top">
           Бонусный канал: 
           </td>
           <td>
            <input name="bonus_ch" id="bonus_ch" type="checkbox" <? echo @$checked_bonus ?> >
           </td>
        </tr>
        <tr>
           <td align="right" valign="top">
           Цена: 
           </td>
           <td>
            <input name="cost" id="cost" type="text" value="<? echo @$cost ?>" size="5" maxlength="6">, коп
           </td>
        </tr>
        <tr>
           <td align="right" valign="top">
            Жанр: 
           </td>
           <td>
            <select name="tv_genre_id">
                <option value="0"/>-----------
                <?echo get_genres()?>
            </select>
           </td>
        </tr>

        <? if (Config::get('enable_tv_quality_filter')){ ?>
        <tr>
           <td align="right">
            URL (HQ):
           </td>
           <td>
            <input id="cmd_1" name="cmd_1" size="50" type="text" value="<? echo @$arr['cmd_1'] ?>">
           </td>
        </tr>
        <tr>
           <td align="right">
            URL (Medium):
           </td>
           <td>
            <input id="cmd_2" name="cmd_2" size="50" type="text" value="<? echo @$arr['cmd_2'] ?>">
           </td>
        </tr>
        <tr>
           <td align="right">
            URL (Low):
           </td>
           <td>
            <input id="cmd_3" name="cmd_3" size="50" type="text" value="<? echo @$arr['cmd_3'] ?>">
           </td>
        </tr>
        <?}else{?>
        <tr>
           <td align="right">
            Адрес:
           </td>
           <td>
            <input id="cmd" name="cmd" size="50" type="text" value="<? echo @$cmd ?>">
           </td>
        </tr>
        <?}?>

        <tr>
           <td align="right" valign="top">
           WOWZA load balancing:
           </td>
           <td>
            <input name="enable_wowza_load_balancing" id="enable_wowza_load_balancing" value="1" type="checkbox" <? echo @$checked_wowza_load_balancing ?> >
           </td>
        </tr>
        
        <tr>
           <td align="right">
            Адрес для записи (мультикаст):
           </td>
           <td>
            <input id="mc_cmd" name="mc_cmd" size="50" type="text" value="<? echo @$mc_cmd ?>">
           </td>
        </tr>
        <tr>
           <td align="right">
            Вести ТВ архив:
           </td>
           <td>
            <input name="enable_tv_archive" id="enable_tv_archive" type="checkbox" <? echo @$checked_enable_tv_archive ?> onchange="this.checked ? document.getElementById('wowza_dvr_tr').style.display = '' : document.getElementById('wowza_dvr_tr').style.display = 'none'" >

            <span id="wowza_dvr_tr" style="display: <?echo @$checked_enable_tv_archive ? '' : 'none' ?>">
            Wowza DVR:
            <input name="wowza_dvr" id="wowza_dvr" type="checkbox" <? echo @$checked_wowza_dvr ?> >
            </span>
           </td>
        </tr>
        <tr>
           <td align="right">
            Вести мониторинг:
           </td>
           <td>
            <input id="enable_monitoring" name="enable_monitoring" type="checkbox" value="1" <? echo @$checked_enable_monitoring ?> onchange="this.checked ? document.getElementById('monitoring_url_tr').style.display = '' : document.getElementById('monitoring_url_tr').style.display = 'none'">
           </td>
        </tr>
        <tr id="monitoring_url_tr" style="display:<? echo @$checked_enable_monitoring ? '' : 'none' ?>">
           <td align="right">
            URL канала для мониторинга:
           </td>
           <td>
            <input id="monitoring_url" name="monitoring_url" size="50" type="text" value="<? echo @$monitoring_url ?>"> * только http
           </td>
        </tr>
        <tr>
           <td align="right">
            xmltv id: 
           </td>
           <td>
            <input id="xmltv_id" name="xmltv_id" size="50" type="text" value="<? echo @$xmltv_id ?>">
           </td>
        </tr>
        <tr>
           <td align="right">
            # услуги: 
           </td>
           <td>
            <input id="service_id" name="service_id" size="50" type="text" value="<? echo @$service_id ?>">
           </td>
        </tr>
        <tr>
           <td align="right">
            Коррекция звука (-20...20): 
           </td>
           <td>
            <input id="volume_correction" name="volume_correction" size="50" type="text" value="<? echo @$volume_correction ?>">
           </td>
        </tr>
        <tr>
           <td align="right">
            Комментарий:
           </td>
           <td>
            <textarea id="descr"  name="descr" cols="39" rows="5"><? echo @$descr ?></textarea>
           </td>
        </tr>
        <tr>
           <td>
           </td>
           <td>
            <input type="button" value="Сохранить" onclick="save()">&nbsp;<input type="button" value="Новый" onclick="document.location='add_itv.php'">
           </td>
        </tr>
    </table>
    </form>
    <a name="form"></a>
    </td>
</tr>
</table>
</td>
</tr>
</table>
</body>
</html>