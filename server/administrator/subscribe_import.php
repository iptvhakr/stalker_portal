<?php
session_start();

ob_start();

include "./common.php";

$error = '';

Admin::checkAuth();

Admin::checkAccess(AdminAccess::ACCESS_VIEW);

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

    Admin::checkAccess(AdminAccess::ACCESS_CREATE);

    if (is_uploaded_file($_FILES['userfile']['tmp_name'])) {
        $f_cont = file ($_FILES['userfile']['tmp_name']);
        $log = '';
        $updated = 0;
        $errors = 0;
        $cut_on = 0;
        $update_fav = @intval($_POST['update_fav']);
        $update_status = @intval($_POST['update_status']);
        $service_id_map   = get_service_id_map();
        $stb_id_map       = get_stb_id_map();
        $subscription_map = get_subscription_map();
        /*$all_ch_bonus = get_all_ch_bonus();*/
        $all_payed_ch = get_all_payed_ch();
        $all_payed_ch_100 = get_all_payed_ch_100();
        $base_channels = get_base_channels();
        //var_dump($all_ch_bonus);
        /*$simple_bonus_arr = array(84, 86, 48, 58, 70, 162, 42, 43);*/
        $extended_packet = array(231, 146, 162, 151, 149, 27, 47, 29, 115, 153, 154, 156, 150, 116, 178);
        $base_channels = array();
        $bonus1 = get_bonus1();
        $bonus2 = get_bonus2();
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
                
                if (@array_key_exists($mac, $stb_id_map)){

                    $stb = Stb::getByMac($mac);
                    $status = $stb['status'];
                    
                    if ($status == 1 && $update_status){

                        Mysql::getInstance()->update('users',
                            array(
                                'status'             => 0,
                                'last_change_status' => 'NOW()'
                            ),
                            array('mac' => $mac)
                        );
                        
                        $event = new SysEvent();
                        $event->setUserListByMac($mac);
                        $event->sendCutOn();
                        
                        $cut_on++;
                    }
                    
                    $stb_id = $stb_id_map[$mac];
                    
                    $stb_id_arr[] = $stb_id;
                    
                    if (array_key_exists($ch, $service_id_map)){
                        if (!@array_key_exists($stb_id, $result)){
                            $result[$stb_id] = array();
                        }
                        $result[$stb_id][] = intval($service_id_map[$ch]);
                    }else if($ch == '00494' || $ch == '00674' || $ch == '00675' || $ch == '00725' || $ch == '00726' || $ch == '00746' || $ch == '00747' || $ch == '00754'){
                        $result[$stb_id] = merge_services(!empty($result[$stb_id]) ? $result[$stb_id] : null, get_all_payed_ch_discovery());
                        $result[$stb_id] = merge_services(!empty($result[$stb_id]) ? $result[$stb_id] : null, get_all_hd_channels());
                        if ($ch == '00674' || $ch == '00675' || $ch == '00725' || $ch == '00726' || $ch == '00746' || $ch == '00747'){
                            $add_services_on[] = $stb_id;
                        }
                    }else if($ch == '00116' || $ch == '00139' || $ch == '00203' || $ch == '00021' || $ch == '00274' || $ch == '00283' || $ch == '00350' || $ch == '00343' || $ch == '00381' || $ch == '00382' || $ch == '00389' || $ch == '00426' || $ch == '00609' || $ch == '00610'){
                        $result[$stb_id] = merge_services(!empty($result[$stb_id]) ? $result[$stb_id] : null, $all_payed_ch);
                        if ($ch == '00203' || $ch == '00021' || $ch == '00274' || $ch == '00283' || $ch == '00350' || $ch == '00343' || $ch == '00389' || $ch == '00609' || $ch == '00610'){
                            $add_services_on[] = $stb_id;
                        }
                    }else if($ch == '00100'){
                        $result[$stb_id] = merge_services(!empty($result[$stb_id]) ? $result[$stb_id] : null, $all_payed_ch_100);
                    }else if($ch == '00493'){
                        $result[$stb_id] = merge_services(!empty($result[$stb_id]) ? $result[$stb_id] : null, array(270, 271, 272, 273, 274, 275));
                        $result[$stb_id] = merge_services(!empty($result[$stb_id]) ? $result[$stb_id] : null, get_all_hd_channels());
                    }else if($ch == '00160' || $ch == '00161' || $ch == '00162' || $ch == '00169' || $ch == '00170' || $ch == '00432' || $ch == '00433'){ // additional services on
                        $add_services_on[] = $stb_id;
                        $result[$stb_id] = merge_services(!empty($result[$stb_id]) ? $result[$stb_id] : null, array());
                    }else if($ch == '00649'){
                        $result[$stb_id] = merge_services(!empty($result[$stb_id]) ? $result[$stb_id] : null, array(270, 271, 272, 273, 274, 275));
                    }else if($ch == '00630' || $ch == '00642' || $ch == '00673' || $ch == '00724' || $ch == '00745' || $ch == '00750' || $ch == '00751' || $ch == '00752'){
                        $result[$stb_id] = merge_services(!empty($result[$stb_id]) ? $result[$stb_id] : null, $extended_packet);
                        $result[$stb_id] = merge_services(!empty($result[$stb_id]) ? $result[$stb_id] : null, $bonus1);
                        $result[$stb_id] = merge_services(!empty($result[$stb_id]) ? $result[$stb_id] : null, array(245, 263));
                        if ($ch == '00673' || $ch == '00724' || $ch == '00745'){
                            $add_services_on[] = $stb_id;
                        }
                    }else{
                        if (!@array_key_exists($stb_id, $result)){
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
        Mysql::getInstance()->query($sql);
    }
    
    if (count($add_services_on) > 0){
        $add_serv_on_counter = count($add_services_on);
        $add_services_on_str = join(",",$add_services_on);
        $sql = "update users set additional_services_on=1 where id in ($add_services_on_str)";
        //echo $sql;
        Mysql::getInstance()->query($sql);
    }

    //var_dump($result); exit;

    foreach ($result as $uid => $sub){
        
        if (count($sub) == 0){
            $bonus = array();
        }else{
            $bonus = $bonus1;
        }

        $sub = array_merge($sub, $bonus2);

        $sub = array_unique($sub);
        $sub_str = base64_encode(serialize($sub));
        
        /*if (count($sub) == 18){
            $bonus = array_unique(array_merge($bonus, $all_ch_bonus));
        }*/

        //var_dump($sub); exit;

        $bonus_str = base64_encode(serialize($bonus));        
        
        if (array_key_exists($uid, $subscription_map)){
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
        
        $result = Mysql::getInstance()->query($sql)->result();

        if ($result){
            $updated++;

            if((bool) Config::get('enable_subscription') && $update_fav){
                $fav_channels = array_unique(array_merge($sub, $bonus, $base_channels));
                //$fav_channels = array();
                $data_str = base64_encode(serialize($fav_channels));

                $id = Mysql::getInstance()->from('fav_itv')->where(array('uid' => $uid))->get()->first('id');
                
                if($id){
                    $sql = "update fav_itv set fav_ch='".$data_str."', addtime=NOW() where uid='".$uid."'";
                }else{
                    $sql = "insert into fav_itv (uid, fav_ch, addtime) values ('".$uid."', '".$data_str."', NOW())";
                }
                
                Mysql::getInstance()->query($sql);
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

function get_bonus1(){

    return Mysql::getInstance()
        ->from('itv')
        ->where(array(
            'bonus_ch' => 1,
            'cost!='   => 99
        ))
        ->get()
        ->all('id');
}

function get_bonus2(){

    return Mysql::getInstance()
        ->from('itv')
        ->where(array(
            'cost'   => 99
        ))
        ->get()
        ->all('id');
}

function get_service_id_map(){

    $arr = array();

    $channels = Mysql::getInstance()->from('itv')->get();

    while($channel = $channels->next()){
        $service_id = $channel['service_id'];
        if (strlen($service_id)==5){
            $arr[$service_id] = $channel['id'];
        }elseif (strlen($service_id) == 11){
            $ids = explode(' ', $service_id);
            
            foreach ($ids as $id){
                $arr[$id] = $channel['id'];
            }
        }
    }
    return $arr;
}

function get_subscription_map(){

    $arr = array();

    $itv_subscription = Mysql::getInstance()->from('itv_subscription')->get();

    while($item = $itv_subscription->next()){
        $arr[$item['uid']] = $item['id'];
    }
    return $arr;
}

function get_stb_id_map(){

    $arr = array();

    $users = Mysql::getInstance()->from('users')->get();

    while($user = $users->next()){
        $arr[$user['mac']] = $user['id'];
    }
    return $arr;
}

function get_all_ch_bonus(){

    return Mysql::getInstance()
        ->from('itv')
        ->where(array(
            'bonus_ch' => 1,
            'base_ch'  => 0
        ))
        ->get()
        ->all('id');
}

function get_base_channels(){

    return Mysql::getInstance()
        ->from('itv')
        ->where(array(
            'base_ch'  => 1
        ))
        ->get()
        ->all('id');
}

function get_all_hd_channels(){

    return Mysql::getInstance()
        ->from('itv')
        ->where(array(
            'hd' => 1
        ))
        ->get()
        ->all('id');
}


function get_all_payed_ch(){

    return Mysql::getInstance()
        ->from('itv')
        ->where(array(
            'base_ch' => 0,
            'hd'      => 0
        ))
        ->not_in('id', array(270, 271, 272, 273, 274, 275))
        ->get()
        ->all('id');
}

function get_all_payed_ch_discovery(){

    return Mysql::getInstance()
        ->from('itv')
        ->where(array(
            'base_ch' => 0,
            'hd'      => 0
        ))
        ->get()
        ->all('id');
}

function get_all_payed_ch_100(){

    return Mysql::getInstance()
        ->from('itv')
        ->where(array(
            'base_ch' => 0,
            'hd'      => 0
        ))
        ->not_in('id', array(178, 179, 270, 271, 272, 273, 274, 275))
        ->get()
        ->all('id');
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
<title><?= _('Subscription import')?></title>
</head>
<body>
<table align="center" border="0" cellpadding="0" cellspacing="0" width="620">
<tr>
    <td align="center" valign="middle" width="100%" bgcolor="#88BBFF">
    <font size="5px" color="White"><b>&nbsp;<?= _('Subscription import')?>&nbsp;</b></font>
    </td>
</tr>
<tr>
    <td width="100%" align="left" valign="bottom">
        <a href="index.php"><< <?= _('Back')?></a>
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
    <td width="50%" align="right"><?= _('File')?>:</td>
    <td><input name="userfile" type="file"></td>
</tr>
<tr>
    <td align="right">&nbsp;</td>
    <td align="left"><input name="update_status" type="checkbox" checked value="1"> <?= _('Update status')?></td>
</tr>
<tr>
    <td align="right">&nbsp;</td>
    <td align="left"><input name="update_fav" type="checkbox" value="1"> <?= _('Update favorites')?></td>
</tr>
<tr>
    <td></td>
    <td><input type="submit" value="<?= htmlspecialchars(_('Import'), ENT_QUOTES)?>"></td>
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