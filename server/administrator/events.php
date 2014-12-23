<?php

session_start();

ob_start();

include "./common.php";

$error = '';

Admin::checkAuth();

Admin::checkAccess(AdminAccess::ACCESS_VIEW);

$error_counter = 0;

if (@$_GET['del'] == 1){

    Admin::checkAccess(AdminAccess::ACCESS_DELETE);

    $uid = Middleware::getUidByMac(@$_GET['mac']);

    Mysql::getInstance()->delete('events', array('uid' => $uid));

    header("Location: events.php?mac=".@$_GET['mac']);
    exit;
}

if (!empty($_POST['user_list_type']) && !empty($_POST['event'])){

    Admin::checkAccess(AdminAccess::ACCESS_CREATE);
    
    if (@$_POST['need_reboot']){
        $reboot_after_ok = 1;
    }else{
        $reboot_after_ok = 0;
    }
    
    $event = new SysEvent();

    $event->setTtl($_POST['ttl']);
    
    if (@$_POST['user_list_type'] == 'to_all'){

        if ($_POST['event'] == 'send_msg'){
            $event->setUserListByMac('all');
            $user_list = Middleware::getOnlineUsersId();
        }else{
            $event->setUserListByMac('online');
            $user_list = Middleware::getAllUsersId();
        }
        
    }elseif (@$_POST['user_list_type'] == 'to_single'){
        $event->setUserListByMac(@$_POST['mac']);
        
        $user_list = Middleware::getUidByMac(@$_POST['mac']);

        $user_list = array($user_list);
        
    }elseif (@$_POST['user_list_type'] == 'by_pattern'){
        
        if (@$_POST['pattern'] == 'mag100'){
            $user_list = Middleware::getUidsByPattern(array('hd' => 0));
        }else if (@$_POST['pattern'] == 'mag200'){
            $user_list = Middleware::getUidsByPattern(array('stb_type' => 'MAG200'));
        }else if (@$_POST['pattern'] == 'mag245'){
            $user_list = Middleware::getUidsByPattern(array('stb_type' => 'MAG245'));
        }else if (@$_POST['pattern'] == 'mag250'){
            $user_list = Middleware::getUidsByPattern(array('stb_type' => 'MAG250'));
        }else if (@$_POST['pattern'] == 'mag255'){
            $user_list = Middleware::getUidsByPattern(array('stb_type' => 'MAG255'));
        }else if (@$_POST['pattern'] == 'mag260'){
            $user_list = Middleware::getUidsByPattern(array('stb_type' => 'MAG260'));
        }else if (@$_POST['pattern'] == 'mag270'){
            $user_list = Middleware::getUidsByPattern(array('stb_type' => 'MAG270'));
        }else if (@$_POST['pattern'] == 'mag275'){
            $user_list = Middleware::getUidsByPattern(array('stb_type' => 'MAG275'));
        }else if (@$_POST['pattern'] == 'wr320'){
            $user_list = Middleware::getUidsByPattern(array('stb_type' => 'WR320'));
        }else if (@$_POST['pattern'] == 'aurahd0'){
            $user_list = Middleware::getUidsByPattern(array('stb_type' => 'AuraHD0'));
        }else if (@$_POST['pattern'] == 'aurahd1'){
            $user_list = Middleware::getUidsByPattern(array('stb_type' => 'AuraHD1'));
        }else if (@$_POST['pattern'] == 'aurahd9'){
            $user_list = Middleware::getUidsByPattern(array('stb_type' => 'AuraHD9'));
        }
        else{
            $user_list = array();
        }

        $error = sprintf(_('%s events %s sended, %s errors'), count($user_list), $_POST['event'], $error_counter)."<br>\n".$error;

        $event->setUserListById($user_list);
        
    }elseif (@$_POST['user_list_type'] == 'by_group'){
        
        if (intval($_POST['group_id']) > 0){
            $stb_groups = new StbGroup();
            $user_list = $stb_groups->getAllMemberUidsByGroupId($_POST['group_id']);
        }else{
            $user_list = array();
        }

        $error = sprintf(_('%s events %s sended, %s errors'), count($user_list), $_POST['event'], $error_counter)."<br>\n".$error;

        $event->setUserListById($user_list);
        
    }elseif (@$_POST['user_list_type'] == 'by_user_list'){
        if (@$_FILES['user_list']){
            if (is_uploaded_file($_FILES['user_list']['tmp_name'])) {
                $f_cont = file ($_FILES['user_list']['tmp_name']);

                if (is_array($f_cont) && isset($f_cont[0]) && substr($f_cont[0], 0, 3) == "\xef\xbb\xbf"){
                    $f_cont[0] = substr($f_cont[0], 3);
                }

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

                $error = sprintf(_('%s events %s sended, %s errors'), count($user_list), $_POST['event'], $error_counter)."<br>\n".$error;

            }
        }else{
            $error .= _('File with list is missing').'<br>';
        }
    }
    
    if ($_POST['event'] == 'cut_off'){
        
        if (!is_array($user_list)){
            $user_list = array($user_list);
        }
        
        $sql = "update users set status=1, last_change_status=NOW() where id in (".implode(",", $user_list).")";
        Mysql::getInstance()->query($sql);
        
        $event->sendCutOff();
    }
    
    switch ($_POST['event']) {
        case 'send_msg':
            if (@$_POST['need_reboot']) {
                $event->sendMsgAndReboot(@$_POST['msg']);
            } else {
                $event->sendMsg(@$_POST['msg']);
            }
            break;
        case 'reboot':
            $event->sendReboot();
            break;
        case 'reload_portal':
            $event->sendReloadPortal();
            break;
        case 'update_channels':
            $event->sendUpdateChannels();
            break;
        case 'play_channel':
            $event->sendPlayChannel(@$_POST['channel']);
            break;
        case 'update_image':
            $event->sendUpdateImage();
            break;
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

$debug = '<!--'.ob_get_contents().'-->';
ob_clean();
echo $debug;

if (!empty($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'events.php') === false){
    $_SESSION['back_url'] = $_SERVER['HTTP_REFERER'];
}elseif (empty($_SERVER['HTTP_REFERER'])){
    $_SESSION['back_url'] = 'index.php';
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
<title><?= _('Events')?></title>
</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0" width="620">
<tr>
    <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;<?= _('Events')?>&nbsp;</b></font>
    </td>
</tr>
<tr>
    <td width="100%" align="left" valign="bottom">
        <a href="<?= empty($_SESSION['back_url'])? 'index.php' : $_SESSION['back_url']?>"><< <?= _('Back')?></a> | <a href="events.php"><?= _('New event')?></a>
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
    var event_obj = document.getElementById('event');

    var need_reboot_cbox = document.getElementById('need_reboot');

    if (event_obj.options[event_obj.selectedIndex].value == 'send_msg'){
        document.getElementById('checkbox_need_reboot').style.display = "";
        document.getElementById('msg_row').style.display = "";
        document.getElementById('ttl').value = 7*24*3600;
    }else{
        if (need_reboot_cbox.checked){
            need_reboot_cbox.click()
        }
        document.getElementById('checkbox_need_reboot').style.display = "none";
        document.getElementById('msg_row').style.display = "none";
        document.getElementById('ttl').value = "<?= Config::getSafe('watchdog_timeout', 120) * 2?>";
    }
    
    if(event_obj.options[event_obj.selectedIndex].value == 'play_channel'){
        document.getElementById('text_channel').style.display = "";
    }else{
        document.getElementById('text_channel').style.display = "none";
    }

    if (event_obj.options[event_obj.selectedIndex].value == ''){
        document.getElementById('submit_button').disabled = true
    }else{
        document.getElementById('submit_button').disabled = false
    }
}

function change_form(obj){
    var mac_row_obj = document.getElementById('mac_row');
    var user_list_row_obj = document.getElementById('user_list_row');
    var pattern_row = document.getElementById('pattern_row');
    var group_row   = document.getElementById('group_row');
    if (obj.value == 'to_single'){
        mac_row_obj.style.display = '';
        pattern_row.style.display = 'none';
        user_list_row_obj.style.display = 'none';
        group_row.style.display = 'none';
    }else if (obj.value == 'to_all'){
        group_row.style.display = 'none';
        mac_row_obj.style.display = 'none';
        pattern_row.style.display = 'none';
        user_list_row_obj.style.display = 'none';
    }else if (obj.value == 'by_user_list'){
        group_row.style.display = 'none';
        mac_row_obj.style.display = 'none';
        pattern_row.style.display = 'none';
        user_list_row_obj.style.display = '';
    }else if (obj.value == 'by_pattern'){
        group_row.style.display = 'none';
        mac_row_obj.style.display = 'none';
        user_list_row_obj.style.display = 'none';
        pattern_row.style.display = '';
    }else if (obj.value == 'by_group'){
        mac_row_obj.style.display = 'none';
        user_list_row_obj.style.display = 'none';
        pattern_row.style.display = 'none';
        group_row.style.display = '';
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
        <?= _('Send')?>:
    </td>
    <td>
        <input type="radio" name="user_list_type" id="to_single" value="to_single" onchange="change_form(this)" checked="checked"><label for="to_single"><?= _('To one')?></label><br/>
        <input type="radio" name="user_list_type" id="to_all" value="to_all" onchange="change_form(this)"><label for="to_all"><?= _('To all')?></label><br/>
        <input type="radio" name="user_list_type" id="by_user_list" value="by_user_list" onchange="change_form(this)"><label for="by_user_list"><?= _('By list')?></label><br/>
        <input type="radio" name="user_list_type" id="by_pattern" value="by_pattern" onchange="change_form(this)"><label for="by_pattern"><?= _('By pattern')?></label><br/>
        <input type="radio" name="user_list_type" id="by_group" value="by_group" onchange="change_form(this)"><label for="by_group"><?= _('To group')?></label><br/>
    </td>
</tr>
<tr id="mac_row">
    <td align="right">
        MAC:
    </td>
    <td>
        <input type="text" name="mac" id="mac" value="<? echo @$mac?>">&nbsp;<input type="button" value="<?= htmlspecialchars(_('Load active events'), ENT_QUOTES)?>" onclick="load_events_by_mac()">
    </td>
</tr>
<tr id="user_list_row" style="display:none">
    <td align="right">
        <?= _('List')?>:
    </td>
    <td>
        <input name="user_list" type="file">
    </td>
</tr>
<tr id="pattern_row" style="display:none">
    <td align="right">
        <?= _('Pattern')?>:
    </td>
    <td>
        <select name="pattern">
            <option value="mag100">MAG100</option>
            <option value="mag200">MAG200</option>
            <option value="mag245">MAG245</option>
            <option value="mag250">MAG250</option>
            <option value="mag255">MAG255</option>
            <option value="mag260">MAG260</option>
            <option value="mag270">MAG270</option>
            <option value="mag275">MAG275</option>
            <option value="wr320">WR320</option>
            <option value="aurahd0">AuraHD0</option>
            <option value="aurahd1">AuraHD1</option>
            <option value="aurahd9">AuraHD9</option>
        </select>
    </td>
</tr>
<tr id="group_row" style="display:none">
    <td align="right">
        <?= _('Group')?>:
    </td>
    <td>
        <select name="group_id">
            <option value="0">--------</option>
            <?
            
            $stb_groups = new StbGroup();
            $all_groups = $stb_groups->getAll();
            
            foreach ($all_groups as $group){
                echo '<option value="'.$group['id'].'">'.$group['name'].'</option>';
            }
            
            ?>
        </select>
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
            <option value="reload_portal">reload_portal
            <option value="update_channels">update_channels
            <option value="play_channel">play_channel
            <option value="mount_all_storages">mount_all_storages
            <option value="cut_off">switch_off
            <option value="update_image">update_image
        </select>
        <span style="display:none" id="checkbox_need_reboot"><input type="checkbox" name="need_reboot" id="need_reboot" value="1"> <?= _('restart on OK')?></span>
        <span style="display:none" id="text_channel"><input type="text" name="channel" id="channel" size="5" maxlength="3"> <?= _('channels')?></span>
    </td>
</tr>
<tr>
    <td align="right">
        TTL:
    </td>
    <td>
        <input type="text" name="ttl" id="ttl" value="<?= Config::getSafe('watchdog_timeout', 120) * 2?>">, <?= _('s')?>
    </td>
</tr>
<tr id="msg_row" style="display:none">
    <td align="right" valign="top">
        MSG:
    </td>
    <td>
        <textarea name="msg" id="msg" rows="10" cols="50"></textarea><br/>
        <? if (substr($locale, 0, 2) == 'ru'){?>
        <a href="#" onclick="fill_msg()" style="font-size:12px;font-weight:normal">Истек срок тестирования</a>
        <?}?>
    </td>
</tr>
<tr>
    <td align="left"></td>
    <td>
        <input type="submit" id="submit_button" disabled="disabled" value="<?= htmlspecialchars(_('Save'), ENT_QUOTES)?>">
    </td>
</tr>
</form>
</table>
<br><br>
<? if (is_array($events) && count($events) > 0){?>
<table class='list' align="center" cellpadding='3' cellspacing='0' width='620'>
<caption><?printf(_('Active events for %s'), $mac)?> <a href="events.php?del=1&mac=<?echo $mac?>" style="font-size:12px"><?= _('clean')?></a></caption>
<tr>
<td class='list'><b><?= _('Valid up to')?></b></td>
<td class='list'><b><?= _('Event')?></b></td>
<td class='list'><b><?= _('Message')?></b></td>
<td class='list'><b><?= _('Status')?></b></td>
</tr>
<?
foreach ($events as $idx => $arr){
    echo "<tr>";
    echo "<td class='list' nowrap>".$arr['eventtime']."</td>\n";
    echo "<td class='list'>".$arr['event']."</td>\n";
    echo "<td class='list'>".$arr['msg']."</td>\n";
    echo "<td class='list'>";
    echo ($arr['sended'])? _('sended') : _('not sended');
    echo "</td>\n";
    echo "</tr>\n";
}
?>
</table>
<?
}else{
    if (!empty($_GET['mac'])){
        echo "<center>".sprintf(_('There are no active events for %s'), $_GET['mac'])."</center>";
    }
}
?>