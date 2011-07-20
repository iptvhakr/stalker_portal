<?php
session_start();

ob_start();

include "../common.php";
include "../lib/func.php";

if (!check_access(array(3))){
    exit;
}

$error = '';

$db = new Database();

moderator_access();

echo '<pre>';
//print_r($_FILES);
//print_r($_SESSION);
//print_r($_POST);
echo '</pre>';

$result = array();
$add_services_on = array();
$add_serv_on_counter = 0;
$add_serv_off_counter = 0;

if (@$_FILES['userfile']){
    if (is_uploaded_file($_FILES['userfile']['tmp_name'])) {
        $f_cont = file ($_FILES['userfile']['tmp_name']);
        $stb = Stb::getInstance();
        $log = '';
        $updated = 0;
        $errors = 0;
        $cut_on = 0;
        $update_fav = @intval($_POST['update_fav']);
        $update_status = @intval($_POST['update_status']);
        $service_id_map   = get_service_id_map();
        $stb_id_map       = get_stb_id_map();
        $subscription_map = get_subscription_map();
        $all_ch_bonus = get_all_ch_bonus();
        $all_payed_ch = get_all_payed_ch();
        $all_payed_ch_100 = get_all_payed_ch_100();
        $base_channels = get_base_channels();
        //var_dump($all_ch_bonus);
        $simple_bonus_arr = array(84, 86, 48, 58, 70, 162, 42, 43);
        $stb_id_arr = array();

        foreach ($f_cont as $cont_str){
            list($ls, $macs, $ch) = explode(",", $cont_str);
            $macs_arr = explode(";", $macs);
            $ch = trim($ch);
            $ls = trim($ls);
            
            foreach ($macs_arr as $mac){
                if (preg_match("/[а-я,А-Я]/",$mac)){
                    _log('mac "'.$mac.'", ЛС '.$ls.' содержит русские буквы ');
                }

                if(strpos($mac, 'ts') !== false){
                    $mac = str_replace('ts', '', $mac);
                    $ch  = '00203';
                }
                
                $mac = Middleware::normalizeMac($mac);
                
                if (@key_exists($mac, $stb_id_map)){
                    
                    $sql = "select * from users where mac='$mac'";
                    $rs = $db->executeQuery($sql);
                    $status = $rs->getValueByName(0, 'status');
                    
                    if ($status == 1 && $update_status){
                        $sql = "update users set status=0, last_change_status=NOW() where mac='$mac'";
                        $db->executeQuery($sql);
                        
                        $event = new SysEvent();
                        $event->setUserListByMac($mac);
                        $event->sendCutOn();
                        
                        $cut_on++;
                    }
                    
                    $stb_id = $stb_id_map[$mac];
                    
                    $stb_id_arr[] = $stb_id;
                    
                    if (key_exists($ch, $service_id_map)){
                        if (!@key_exists($stb_id, $result)){
                            $result[$stb_id] = array();
                        }
                        $result[$stb_id][] = intval($service_id_map[$ch]);
                    }else if($ch == '00494'){
                        $result[$stb_id] = merge_services($result[$stb_id], get_all_payed_ch_discovery());
                    }else if($ch == '00116' || $ch == '00139' || $ch == '00203' || $ch == '00021' || $ch == '00274' || $ch == '00283' || $ch == '00350' || $ch == '00343' || $ch == '00381' || $ch == '00382' || $ch == '00389' || $ch == '00426' || $ch == '00466'){
                        $result[$stb_id] = merge_services($result[$stb_id], $all_payed_ch);
                        if ($ch == '00203' || $ch == '00021' || $ch == '00274' || $ch == '00283' || $ch == '00350' || $ch == '00343' || $ch == '00389' || $ch == '00466'){
                            $add_services_on[] = $stb_id;
                        }
                    }else if($ch == '00100'){
                        $result[$stb_id] = merge_services($result[$stb_id], $all_payed_ch_100);
                    }else if($ch == '00493'){
                        $result[$stb_id] = merge_services($result[$stb_id], array(270, 271, 272, 273, 274, 275));
                    }else if($ch == '00160' || $ch == '00161' || $ch == '00162' || $ch == '00169' || $ch == '00170' || $ch == '00432' || $ch == '00433'){ // additional services on
                        $add_services_on[] = $stb_id;
                    }else{
                        if (!@key_exists($stb_id, $result)){
                            $result[$stb_id] = array();
                        }
                        _log('услуга "'.$ch.'" не найдена');
                    }
                    
                }else{
                    _log('mac "'.$mac.'", ЛС '.$ls.' не найден');
                    $errors++;
                }
            }
        }
    }
    
    $stb_id_arr = array_unique($stb_id_arr);
    
    if (count($stb_id_arr) > 0){
        $add_serv_off_counter = count($stb_id_arr);
        $stb_id_str = join(",",$stb_id_arr);
        $sql = "update users set additional_services_on=0 where id in ($stb_id_str)";
        //echo $sql;
        $db->executeQuery($sql);
    }
    
    if (count($add_services_on) > 0){
        $add_serv_on_counter = count($add_services_on);
        $add_services_on_str = join(",",$add_services_on);
        $sql = "update users set additional_services_on=1 where id in ($add_services_on_str)";
        //echo $sql;
        $db->executeQuery($sql);
    }
    
    //var_dump($result); exit;
    foreach ($result as $uid => $sub){
        
        if (count($sub) == 0){
            $bonus = array();
        }else{
            $bonus = $simple_bonus_arr;
        }
        
        $sub = array_unique($sub);
        $sub_str = base64_encode(serialize($sub));
        
        if (count($sub) == 18){
            $bonus = array_unique(array_merge($bonus, $all_ch_bonus));
        }
        
        $bonus_str = base64_encode(serialize($bonus));        
        
        if (key_exists($uid, $subscription_map)){
            $sql = "update itv_subscription set sub_ch='$sub_str', bonus_ch='$bonus_str', addtime=NOW() where uid=$uid";
        }else{
            $sql = "insert into itv_subscription (uid, sub_ch, bonus_ch, addtime) value ($uid, '$sub_str', '$bonus_str', NOW())";
        }
                
        $event = new SysEvent();
        $event->setUserListById($uid);
        $event->sendUpdateSubscription();
        
        $event = new SysEvent();
        $event->setUserListById($uid);
        $event->sendMsg('Каналы обновлены согласно подписке.');
        
        $rs = $db->executeQuery($sql);
        if (!$db->getLastError()){
            $updated++;

            if((bool) Config::get('enable_subscription') && $update_fav){
                $fav_channels = array_unique(array_merge($sub, $bonus, $base_channels));
                //$fav_channels = array();
                $data_str = base64_encode(serialize($fav_channels));
            
                $sql = "select * from fav_itv where uid='".$uid."'";
                $rs = $db->executeQuery($sql);
                $id = intval($rs->getValueByName(0, 'id'));
                
                if($id){
                    $sql = "update fav_itv set fav_ch='".$data_str."', addtime=NOW() where uid='".$uid."'";
                }else{
                    $sql = "insert into fav_itv (uid, fav_ch, addtime) values ('".$uid."', '".$data_str."', NOW())";
                }
                
                $rs = $db->executeQuery($sql);
            }
        }
    }
}

/*function normalize_mac($mac){
    $pattern = array('А', 'В', 'С', 'Е'); // ru
    $replace = array('A', 'B', 'C', 'E'); // en
    
    $mac = str_replace($pattern, $replace, trim($mac));
    
    if (strlen($mac)==12){
        $mac = substr($mac, 0,2).":".substr($mac, 2,2).":".substr($mac, 4,2).":".substr($mac, 6,2).":".substr($mac, 8,2).":".substr($mac, 10,2);
    }
    return $mac;
}*/

function merge_services($list1, $list2){

    if (empty($list1)){
        $list1 = array();
    }

    if (empty($list2)){
        $list2 = array();
    }

    return array_merge($list1, $list2);
}

function get_service_id_map(){
    $db = Database::getInstance();
    $arr = array();
    $sql = "select * from itv";
    $rs = $db->executeQuery($sql);
    while(@$rs->next()){
        $service_id = $rs->getCurrentValueByName('service_id');
        if (strlen($service_id)==5){
            $arr[$service_id]=$rs->getCurrentValueByName('id');
        }elseif (strlen($service_id) == 11){
            $ids = explode(' ', $service_id);
            
            foreach ($ids as $id){
                $arr[$id]=$rs->getCurrentValueByName('id');
            }
        }
    }
    return $arr;
}

function get_subscription_map(){
    $db = Database::getInstance();
    $arr = array();
    $sql = "select * from itv_subscription";
    $rs = $db->executeQuery($sql);
    while(@$rs->next()){
        $arr[$rs->getCurrentValueByName('uid')]=$rs->getCurrentValueByName('id');
    }
    return $arr;
}

function get_stb_id_map(){
    $db = Database::getInstance();
    $arr = array();
    $sql = "select * from users";
    $rs = $db->executeQuery($sql);
    while(@$rs->next()){
        $arr[$rs->getCurrentValueByName('mac')]=$rs->getCurrentValueByName('id');
    }
    return $arr;
}

function get_all_ch_bonus(){
    $db = Database::getInstance();
    $arr = array();
    $sql = "select * from itv where bonus_ch=1 and base_ch=0";
    $rs = $db->executeQuery($sql);
    while(@$rs->next()){
        $arr[] = intval($rs->getCurrentValueByName('id'));
    }
    return $arr;
}

function get_base_channels(){
    $db = Database::getInstance();
    $arr = array();
    $sql = "select * from itv where base_ch=1";
    $rs = $db->executeQuery($sql);
    while(@$rs->next()){
        $arr[] = intval($rs->getCurrentValueByName('id'));
    }
    return $arr;
}

function get_all_payed_ch(){
    $db = Database::getInstance();
    $arr = array();
    $sql = "select * from itv where base_ch=0 and id not in(270, 271, 272, 273, 274, 275)";
    $rs = $db->executeQuery($sql);
    while(@$rs->next()){
        $arr[] = intval($rs->getCurrentValueByName('id'));
    }
    return $arr;
}

function get_all_payed_ch_discovery(){
    $db = Database::getInstance();
    $arr = array();
    $sql = "select * from itv where base_ch=0";
    $rs = $db->executeQuery($sql);
    while(@$rs->next()){
        $arr[] = intval($rs->getCurrentValueByName('id'));
    }
    return $arr;
}

function get_all_payed_ch_100(){
    $db = Database::getInstance();
    $arr = array();
    $sql = "select * from itv where base_ch=0 and id not in(178, 179, 270, 271, 272, 273, 274, 275)";
    $rs = $db->executeQuery($sql);
    while(@$rs->next()){
        $arr[] = intval($rs->getCurrentValueByName('id'));
    }
    return $arr;
}

function _log($str){
    global $log;
    $log .= $str."<br>\n";
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
select {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-weight: bold;
	width: 200px;
	border: thin 1;
}
select.all {
	height: 500px;
}
select.sub {
	height: 350px;
}
select.bonus {
	height: 100px;
}
</style>

<?
$id = intval(@$_GET['id']);

?>
<title>Импорт подписки на каналы</title>
</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0" width="620">
<tr>
    <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;Импорт подписки на каналы&nbsp;</b></font>
    </td>
</tr>
<tr>
    <td width="100%" align="left" valign="bottom">
        <a href="index.php"><< Назад</a> 
    </td>
</tr>
<tr>
    <td align="center">
    <br>
    <br>
    </td>
</tr>
<tr>
<td>
<? if(!$_FILES){ ?>
<form enctype="multipart/form-data" method="POST">
<table class="list" align="center" border="0" cellpadding="0" cellspacing="0" width="300">
<tr>
    <td width="50%" align="right">Файл:</td>
    <td><input name="userfile" type="file"></td>
</tr>
<tr>
    <td align="right">&nbsp;</td>
    <td align="left"><input name="update_status" type="checkbox" checked value="1"> Обновлять статус</td>
</tr>
<tr>
    <td align="right">&nbsp;</td>
    <td align="left"><input name="update_fav" type="checkbox" value="1"> Обновлять избранное</td>
</tr>
<tr>
    <td></td>
    <td><input type="submit" value="Импорт"></td>
</tr>
<table>
</form>
<?}else{
    echo "<table align='center' width='350'>";
    echo "<tr>";
    echo "<td>";
    echo "<b>Обновлено $updated подписок,<br> включено $cut_on приставок,<br> отключено доп сервисов у $add_serv_off_counter приставок,<br> включено доп сервисов у $add_serv_on_counter,<br> всего $errors ошибок</b><br><br>\n";
    echo $log;
    echo "</td>";
    echo "</tr>";
}?>
</td>
</tr>
</table>
</body>
</html>