<?php

session_start();

ob_start();

include "../conf_serv.php";
include "../lib/func.php";

$error = '';

$db = new Database(DB_NAME);

moderator_access();

$error_counter = 0;

if (@$_GET['del'] == 1){
    $uid = Middleware::getUidByMac(@$_GET['mac']);
    $sql = "delete from events where uid=".$uid;
    $rs=$db->executeQuery($sql);
    header("Location: events.php?mac=".@$_GET['mac']);
    exit;
}

if (!empty($_POST['user_list_type']) && !empty($_POST['event'])){

    //var_dump($_POST);exit;
    
    if (@$_POST['need_reboot']){
        $reboot_after_ok = 1;
    }else{
        $reboot_after_ok = 0;
    }
    
    $event = new SysEvent();
    
    if (@$_POST['user_list_type'] == 'to_all'){
        $event->setUserListByMac('all');
        
        $user_list = Middleware::getAllUsersId();
        
    }elseif (@$_POST['user_list_type'] == 'to_single'){
        $event->setUserListByMac(@$_POST['mac']);
        
        $user_list = Middleware::getUidByMac(@$_POST['mac']);

        $user_list == array($user_list);
        
    }elseif (@$_POST['user_list_type'] == 'by_user_list'){
        if (@$_FILES['user_list']){
            if (is_uploaded_file($_FILES['user_list']['tmp_name'])) {
                $f_cont = file ($_FILES['user_list']['tmp_name']);
                foreach ($f_cont as $mac){
            
                    $uid = Middleware::getUidByMac($mac);
                    
                    if ($uid){
                        $user_list[] = $uid;
                    }else{
                        $error .= "mac '".$mac."' not found<br>\n";
                        $error_counter++;
                    }
                }
                
                $event->setUserListById($user_list);

                $error = count($user_list).' событий '.$_POST['event'].' отослано, '.$error_counter." ошибок<br>\n".$error;
                
            }
        }else{
            $error .= 'Отсутствует файл со списком<br>';
        }
    }
    
    if ($_POST['event'] == 'cut_off'){
        
        if (!is_array($user_list)){
            $user_list = array($user_list);
        }
        
        $sql = "update users set status=1, last_change_status=NOW() where id in (".implode(",", $user_list).")";
        $db->executeQuery($sql);
        
        $event->sendCutOff();
    }
    
    switch ($_POST['event']) {
    	case 'send_msg':
    		if (@$_POST['need_reboot']){
                $event->sendMsgAndReboot(@$_POST['msg']);
            }else{
                $event->sendMsg(@$_POST['msg']);
            }
    		break;
    	case 'reboot':
                $event->sendReboot();
    		break;
    	case 'update_channels':
                $event->sendUpdateChannels();
    		break;
    	case 'play_channel':
                $event->sendPlayChannel(@$_POST['channel']);
    		break;
    }
    
    if ($db->getLastError()){
        echo 'Ошибка при отправке события: '.$db->getLastError();
        exit;
    }
}

$mac = '';

if (!empty($_POST['mac'])){
    $mac = $_POST['mac'];
}else if(!empty($_GET['mac'])){
    $mac = $_GET['mac'];
}

$uid = Middleware::getUidByMac($mac);

$events = Event::getAllNotEndedEvents($uid);

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
<title>События</title>
</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0" width="620">
<tr>
    <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;События&nbsp;</b></font>
    </td>
</tr>
<tr>
    <td width="100%" align="left" valign="bottom">
        <a href="index.php"><< Назад</a> | <a href="events.php">Новое событие</a>
    </td>
</tr>
<tr>
    <td align="center">
    <font color="Red">
    <br>
    <br>
    <strong>
    <? echo $error?>
    </strong>
    </font>
    <br>
    <br>
    </td>
</tr>
</table>
<script>

function load_events_by_mac(){
    var mac = document.getElementById('mac').value
    document.location = '?mac='+mac
}

function enable_disable_mac(){
    if(document.getElementById('all').checked){
        document.getElementById('mac').disabled = true
    }else{
        document.getElementById('mac').disabled = false
    }
}

function check_event(){
    var event_obj = document.getElementById('event')
    var need_reboot_cbox = document.getElementById('need_reboot')
    if (event_obj.options[event_obj.selectedIndex].value == 'send_msg'){
        document.getElementById('checkbox_need_reboot').style.display = ""
        document.getElementById('msg_row').style.display = ""
    }else{
        if (need_reboot_cbox.checked){
            need_reboot_cbox.click()
        }
        document.getElementById('checkbox_need_reboot').style.display = "none"
        document.getElementById('msg_row').style.display = "none"
    }
    
    if(event_obj.options[event_obj.selectedIndex].value == 'play_channel'){
        document.getElementById('text_channel').style.display = ""
    }else{
        document.getElementById('text_channel').style.display = "none"
    }
    
    if (event_obj.options[event_obj.selectedIndex].value == ''){
        document.getElementById('submit_button').disabled = true
    }else{
        document.getElementById('submit_button').disabled = false
    }
}

function change_form(obj){
    var mac_row_obj = document.getElementById('mac_row')
    var user_list_row_obj = document.getElementById('user_list_row')
    if (obj.value == 'to_single'){
        mac_row_obj.style.display = ''
        user_list_row_obj.style.display = 'none'
    }else if (obj.value == 'to_all'){
        mac_row_obj.style.display = 'none'
        user_list_row_obj.style.display = 'none'
    }else if (obj.value == 'by_user_list'){
        mac_row_obj.style.display = 'none'
        user_list_row_obj.style.display = ''
    }
}

function fill_msg(){
    txt = 'Уважаемый абонент! Срок бесплатного тестирования наших услуг закончился. Просим Вас подойти в абонентский отдел (пр-т Ак. Глушко, 11-И, каб.8) для перезаключения договора либо возврата оборудования.'
    document.getElementById('msg').value = txt
}

</script>
<table border="0" align="center" width="620">
<form action="events.php" method="POST" enctype="multipart/form-data">
<tr>
    <td align="right" valign="top" width="100">
        Отослать:
    </td>
    <td>
        <input type="radio" name="user_list_type" id="to_single" value="to_single" onchange="change_form(this)" checked="checked"><label for="to_single">Одному</label><br/>
        <input type="radio" name="user_list_type" id="to_all" value="to_all" onchange="change_form(this)"><label for="to_all">Всем</label><br/>
        <input type="radio" name="user_list_type" id="by_user_list" value="by_user_list" onchange="change_form(this)"><label for="by_user_list">По списку</label><br/>
    </td>
</tr>
<tr id="mac_row">
    <td align="right">
        MAC:
    </td>
    <td>
        <input type="text" name="mac" id="mac" value="<? echo @$mac?>">&nbsp;<input type="button" value="Загрузить активные события" onclick="load_events_by_mac()">
    </td>
</tr>
<tr id="user_list_row" style="display:none">
    <td align="right">
        Список:
    </td>
    <td>
        <input name="user_list" type="file">
    </td>
</tr>
<tr>
    <td align="right">
        TYPE:
    </td>
    <td>        
        <select name="event" id="event" onchange="check_event()">
            <option value="">----------
            <option value="send_msg">send_msg
            <option value="reboot">reboot
            <option value="update_channels">update_channels
            <option value="play_channel">play_channel
            <option value="mount_all_storages">mount_all_storages
            <option value="cut_off">switch_off
        </select>
        <span style="display:none" id="checkbox_need_reboot"><input type="checkbox" name="need_reboot" id="need_reboot" value="1"> перезапускать по OK</span>
        <span style="display:none" id="text_channel"><input type="text" name="channel" id="channel" size="5" maxlength="3"> канал</span>
    </td>
</tr>
<tr id="msg_row" style="display:none">
    <td align="right" valign="top">
        MSG:
    </td>
    <td>
        <textarea name="msg" id="msg" rows="10" cols="50"></textarea><br/>
        <a href="#" onclick="fill_msg()" style="font-size:12px;font-weight:normal">Истек срок тестирования</a>
    </td>
</tr>
<tr>
    <td align="left"></td>
    <td>
        <input type="submit" id="submit_button" disabled="disabled" value="Сохранить">
    </td>
</tr>
</form>
</table>
<br><br>
<? if (is_array($events) && count($events) > 0){?>
<table class='list' align="center" cellpadding='3' cellspacing='0' width='620'>
<caption>Активные события для <?echo $mac?> <a href="events.php?del=1&mac=<?echo $mac?>" style="font-size:12px">очистить</a></caption>
<tr>
<td class='list'><b>Действительно до</b></td>
<td class='list'><b>Событие</b></td>
<td class='list'><b>Сообщение</b></td>
<td class='list'><b>Статус</b></td>
</tr>
<?
foreach ($events as $idx => $arr){
    echo "<tr>";
    echo "<td class='list' nowrap>".$arr['eventtime']."</td>\n";
    echo "<td class='list'>".$arr['event']."</td>\n";
    echo "<td class='list'>".$arr['msg']."</td>\n";
    echo "<td class='list'>";
    echo ($arr['sended'])? 'отправлено' : 'не отправлено';
    echo "</td>\n";
    echo "</tr>\n";
}
?>
</table>
<?
}else{
    if (!empty($_GET['mac'])){
        echo "<center>Нет активных событий для ".$_GET['mac']."</center>";
    }
}
?>