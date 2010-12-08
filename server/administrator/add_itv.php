<?php
session_start();

ob_start();

include "../conf_serv.php";
include "../common.php";
include "../lib/func.php";

$error = '';

$db = new Database(DB_NAME);

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
    
            $query = "insert into itv (
                                        name,
                                        number,
                                        censored,
                                        base_ch,
                                        bonus_ch,
                                        hd,
                                        cost,
                                        cmd, 
                                        descr, 
                                        tv_genre_id, 
                                        status,
                                        xmltv_id,
                                        service_id,
                                        volume_correction
                                        ) 
                                values ('".@$_POST['name']."', 
                                        '".@$_POST['number']."', 
                                        '".$censored."',
                                        '".$base_ch."',
                                        '".$bonus_ch."',
                                        '".$hd."',
                                        '".@$_POST['cost']."',
                                        '".@$_GET['cmd']."', 
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
            header("Location: add_itv.php");
            exit;
        }
        else{
            $error = 'Ошибка: необходимо заполнить все поля';
        }
    }
    
    if (@$_GET['update'] && !$error){
        
        if(@$_GET['cmd'] && @$_GET['name']){
            
            $query = "update itv 
                                set name='".$_POST['name']."', 
                                cmd='".$_GET['cmd']."', 
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
            //echo $query;
            $rs=$db->executeQuery($query);
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
    echo "<td class='list' style='color:#5588FF'><b>".$arr['name']."</b></td>";
    echo "<td class='list'>".$arr['cmd']."</td>";
    //echo "<td class='list'>".$arr['descr']."</td>";
    echo "<td class='list'>".$arr['genres_name']."</td>";
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
        $option .= "<option value={$arr['id']} $selected>{$arr['title']}\n";
    }
    return $option;
}
?>
<script>
function save(){
    form_ = document.getElementById('form_')
    
    name = document.getElementById('name').value
    cmd = document.getElementById('cmd').value
    id = document.getElementById('id').value
    //descr = document.getElementById('descr').value
    
    action = 'add_itv.php?name='+name+'&cmd='+cmd+'&id='+id
    //alert(action)
    if(document.getElementById('action').value == 'edit'){
        action += '&update=1'
    }
    else{
        action += '&save=1'
    }
    
    //alert(action)
    form_.action = action
    form_.method = 'POST'
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
        <tr>
           <td align="right">
            Адрес: 
           </td>
           <td>
            <input id="cmd" name="cmd" size="50" type="text" value="<? echo @$cmd ?>">
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
            <input id="service_id" name="volume_correction" size="50" type="text" value="<? echo @$volume_correction ?>">
           </td>
        </tr>
        <!--<tr>
           <td align="right">
            Описание: 
           </td>
           <td>-->
            <input id="descr"  name="descr" type="hidden" value="<? //echo @$descr ?>">
           <!--</td>
        </tr>-->
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