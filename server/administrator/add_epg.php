<?php

session_start();

ob_start();

include "../conf_serv.php";
include "../lib/func.php";

error_reporting(E_ALL);
$error = '';

$db = new Database(DB_NAME);

moderator_access();

if (!$error){
    
    if (@$_GET['save'] && $_GET['yy'] && $_GET['mm'] && $_GET['dd'] && @$_GET['id']){
        
        $epg = $_POST['epg'];

        $yy = $_GET['yy'];
        
        if($_GET['mm']<10){
            $mm = '0'.$_GET['mm'];
        }else{
            $mm = $_GET['mm'];
        }
        
        if($_GET['dd']<10){
            $dd = '0'.$_GET['dd'];
        }else{
            $dd = $_GET['dd'];
        }
        
        $time_from = $yy.'-'.$mm.'-'.$dd.' 00:00:00';
        $time_to = $yy.'-'.$mm.'-'.$dd.' 24:00:00';
        
        $query = "delete from epg where ch_id=".$_GET['id']." and time > '".$time_from."' and time < '".$time_to."'";
        $rs=$db->executeQuery($query);
        
        
        $tmp_epg = preg_split("/\n/", stripslashes(trim($epg)));
        
        for ($i=0; $i<count($tmp_epg); $i++){
            $epg_line = trim($tmp_epg[$i]);
            preg_match("/(\d+):(\d+)[\s\t]*([\S\s]+)/", $epg_line, $tmp_line);
            if (@$tmp_line[1] && $tmp_line[2] && $tmp_line[3]){
                //$time = mktime($tmp_line[1],$tmp_line[2],0,$_GET['mm'],$_GET['dd'],$_GET['yy']);
                $time = $yy.'-'.$mm.'-'.$dd.' '.$tmp_line[1].':'.$tmp_line[2].':00';
                //echo $time;
                $name = addslashes($tmp_line[3]);
                //$query = "insert into epg (ch_id,time,name,descr) values ('".$_GET['id']."', '".$time."', '".$name."', '') ch_id=".$_GET['id'];
                $query = "insert into epg (ch_id,time,name) values ('".$_GET['id']."', '".$time."', '".$name."')";
                //echo $query;
                $rs=$db->executeQuery($query);
            }
        }
        header("Location: add_epg.php?id=".$_GET['id']."&mm=".$_GET['mm']."&dd=".$_GET['dd']."&yy=".$_GET['yy']."&saved=1");
        exit;
    }
}

function construct_oprion($id = 0){
    global $db;
    $opt = '';
    $query = "select * from itv";
    $rs=$db->executeQuery($query);
    while(@$rs->next()){
        $arr=$rs->getCurrentValuesAsHash();
        if ($id && $id == $arr['id']){
            $opt .= "<option value='".$arr['id']."' selected>".$arr['name']."</option>\n";
        }else{
            $opt .= "<option value='".$arr['id']."'>".$arr['name']."</option>\n";
        }
    }
    return $opt;
}

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
         1 => 'Январь',
         2 => 'Февраль',
         3 => 'Март',
         4 => 'Апрель',
         5 => 'Май',
         6 => 'Июнь',
         7 => 'Июль',
         8 => 'Август',
         9 => 'Сентябрь',
        10 => 'Октябрь',
        11 => 'Ноябрь',
        12 => 'Декабрь',
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
    
    for ($i=1;$i<=31;$i++){
        if ($i == $day) {
            $dd .= "<option value='".$i."' selected>".$i."</option>";	
        }else{
            $dd .= "<option value='".$i."'>".$i."</option>";
        }
    }
    return $dd;
}

function load_epg($id = 0){
    global $db;
    $epg = '';
    if(!$id)return;
    if (@$_GET['yy'] && @$_GET['mm'] && @$_GET['dd']){
        $time = mktime (0,0,0,$_GET['mm'],$_GET['dd'],$_GET['yy']);
    }else{
        $time = time();
    }
    
    $year = date("Y",$time);
    $month = date("m",$time);
    $day = date("d",$time);

    $time_from = $year.'-'.$month.'-'.$day.' 00:00:00';
    $time_to = $year.'-'.$month.'-'.$day.' 24:00:00';

    $sql = "select * from epg where ch_id=".$id." and time > '".$time_from."' and time < '".$time_to."' order by time";
    //echo $sql;
    $rs=$db->executeQuery($sql);
    $epg = '';
    while(@$rs->next()){
        $arr=$rs->getCurrentValuesAsHash();
        $epg .= time_mysql2epg($arr['time'])." ".$arr['name']."\n";
    }
    return $epg;
}

function time_mysql2epg($datetime){
    preg_match("/(\d+):(\d+)/", $datetime, $arr);
    return $arr[0];
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
    <font size="5px" color="White"><b>&nbsp;Редактирование EPG&nbsp;</b></font>
    </td>
</tr>
<tr>
    <td width="100%" align="left" valign="bottom">
        <a href="add_itv.php"><< Назад</a>
    </td>
</tr>
<tr>
    <td align="center">
    <font color="Red">
    <strong>
    <? if (@$_GET['saved']){echo "Сохранение прошло успешно";}?>
    </strong>
    </font>
    <br>
    <br>
    </td>
</tr>
<tr>
<td>
<?
$query = "select * from itv";

$rs=$db->executeQuery($query);

if (@$_GET['edit']){
    $query = "select * from itv where id=".intval(@$_GET['id']);
    $rs=$db->executeQuery($query);
    while(@$rs->next()){
        $arr=$rs->getCurrentValuesAsHash();
        $name = $arr['name'];
        $cmd = $arr['cmd'];
        $descr = $arr['descr'];
        $status = $arr['status'];
    }
}

?>
<script>
function save(){
    form = document.getElementById('form')
    
    name = document.getElementById('name').value
    cmd = document.getElementById('cmd').value
    id = document.getElementById('id').value
    descr = document.getElementById('descr').value
    
    action = 'add_itv.php?name='+name+'&cmd='+cmd+'&id='+id+'&descr='+descr
    //alert(action)
    if(document.getElementById('action').value == 'edit'){
        action += '&update=1'
    }
    else{
        action += '&save=1'
    }
    
    //alert(action)
    form.action = action
    document.location=action
    //form.submit()
}
function load_epg(){
    form = document.getElementById('form')
    id = document.getElementById('id').options[document.getElementById('id').selectedIndex].value
    yy = document.getElementById('yy').options[document.getElementById('yy').selectedIndex].value
    mm = document.getElementById('mm').options[document.getElementById('mm').selectedIndex].value
    dd = document.getElementById('dd').options[document.getElementById('dd').selectedIndex].value
    //alert('id:'+id+' yy:'+yy+' mm:'+mm+' dd:'+dd)
    action = 'add_epg.php?id='+id+'&yy='+yy+'&mm='+mm+'&dd='+dd
    document.location=action
}
function save_epg(){
    form = document.getElementById('form')
    id   = document.getElementById('id').options[document.getElementById('id').selectedIndex].value
    yy   = document.getElementById('yy').options[document.getElementById('yy').selectedIndex].value
    mm   = document.getElementById('mm').options[document.getElementById('mm').selectedIndex].value
    dd   = document.getElementById('dd').options[document.getElementById('dd').selectedIndex].value
    epg  = document.getElementById('epg').value
    
    action = 'add_epg.php?id='+id+'&yy='+yy+'&mm='+mm+'&dd='+dd+'&save=1'
    //alert(action)
    //document.location=action
    form.action = action
    form.submit()
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
    <form id="form" method="POST">
    <table align="center">
        <tr>
           <td align="right">
            Канал: 
           </td>
           <td>
            <!--input type="text" name="name" id="name" value="<?// echo @$name ?>"-->
            <select name="id" id="id">
                <? echo construct_oprion(@$_GET['id'])?>
            </select>
            
            <!--input type="hidden" id="id" value="<?// echo @$_GET['id'] ?>"-->
            <input type="hidden" id="action" value="<? if(@$_GET['edit']){echo "edit";} ?>">
           </td>
        </tr>
        <tr>
           <td align="right">
            Дата: 
           </td>
           <td>
           <select name="yy" id="yy">
                <? echo construct_YY()?>
           </select>
           <select name="mm" id="mm">
                <? echo construct_MM()?>
           </select>
           <select name="dd" id="dd">
                <? echo construct_DD()?>
           </select>
            <!--input id="cmd" type="text" value="<?// echo @$cmd ?>"-->&nbsp;
            <input type="button" value="Загрузить EPG" onclick="load_epg()">
           </td>
        </tr>
        <tr>
           <td align="right" valign="top">
            EPG: 
           </td>
           <td>
            <!--input id="descr" type="text" value="<?//     echo @$descr ?>"-->
            <textarea name="epg" id="epg" cols="70" rows="20"><? echo load_epg(@$_GET['id'])?></textarea>
           </td>
        </tr>
        <tr>
           <td>
           </td>
           <td>
            <input type="submit" value="Сохранить" onclick="save_epg()">&nbsp;<input type="button" value="Новый" onclick="document.location='add_epg.php'">
<!--            <input type="submit" value="Сохранить">&nbsp;<input type="button" value="Новый" onclick="document.location='add_itv.php'">-->
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