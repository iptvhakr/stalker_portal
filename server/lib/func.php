<?php

function __autoload($class_name) {
    require_once PORTAL_PATH.'/server/lib/'.strtolower($class_name).'.class.php';
}

function get_save_folder($id){
    
    $dir_name = ceil($id/FILES_IN_DIR);
    $dir_path = IMG_PATH.$dir_name;
    echo '$dir_path: '.$dir_path;
    if (!is_dir($dir_path)){
        if (!mkdir ($dir_path, 0777)){
            return -1;
        }else{
            return $dir_path;
        }
    }else{
        return $dir_path;
    }
    
}

function get_img_uri($id){
    
    $dir_name = ceil($id/FILES_IN_DIR);
    $dir_path = IMG_URI.$dir_name;
    $dir_path .= '/'.$id.'.jpg';
    return $dir_path;
}

function transliterate($st) {
   $st = strtr($st,
        "абвгдежзийклмнопрстуфыэАБВГДЕЖЗИЙКЛМНОПРСТУФЫЭ",
        "abvgdegziyklmnoprstufieABVGDEGZIYKLMNOPRSTUFIE"
   );
   $st = strtr($st, array(
        'ё'=>"yo", 'х'=>"h", 'ц'=>"ts", 'ч'=>"ch", 'ш'=>"sh",
        'щ'=>"shch", 'ъ'=>'', 'ь'=>'', 'ю'=>"yu", 'я'=>"ya",
        'Ё'=>"Yo", 'Х'=>"H", 'Ц'=>"Ts", 'Ч'=>"Ch", 'Ш'=>"Sh",
        'Щ'=>"Shch", 'Ъ'=>'', 'Ь'=>'', 'Ю'=>"Yu", 'Я'=>"Ya",
        ' '=>"_", '!'=>"", '?'=>"", ','=>"", '.'=>"", '"'=>"", 
        '\''=>"", '\\'=>"", '/'=>"", ';'=>"", ':'=>"", '«'=>"", '»'=>"", '`'=>""
   ));
   return $st;
}

function rename_incoming_dir($old_name, $new_name){
    echo 'rename_incoming_dir '.$old_name. ' -> '.$new_name;
    if ($old_name == ''){
        return create_incoming_dir($new_name);
    }else if($old_name != $new_name){
        return rename(INCOMING_DIR.$old_name, INCOMING_DIR.$new_name);
    }
}

function check_db_user_login($login, $pass){
    global $db;
    
    $query = "select * from administrators where login='$login'";
    $rs=$db->executeQuery($query);
    $db_pass = $rs->getValueByName(0, 'pass');
    if ($db_pass == md5($pass)){
        $_SESSION['uid'] = $rs->getValueByName(0, 'id');
        $_SESSION['login'] = $login;
        $_SESSION['pass']  = $db_pass;
        $_SESSION['access']  = $rs->getValueByName(0, 'access');
        return 1;
    }else{
        return 0;
    }
}

function check_session_user_login(){
    global $db;
    
    $login = @$_SESSION['login'];
    $pass  = @$_SESSION['pass'];
    if ($login && $pass){
        $query = "select * from administrators where login='$login'";
        $rs=$db->executeQuery($query);
        $db_pass = $rs->getValueByName(0, 'pass');
        if ($db_pass == $pass){
            return 1;
        }else{
            return 0;
        }
    }else{
        return 0;
    }
}

function check_moderator_login(){
    if (@$_SESSION['adm_login'] != MODERATOR_LOGIN && @$_SESSION['adm_password'] != MODERATOR_PASSWORD){        
        return 0;
    }else{
        return 1;
    }
}

function moderator_access(){
    //if(!check_moderator_login()){
    if(!check_session_user_login()){
        header("Location: login.php");
        exit();
    }
}

function check_access($num = array()){
    
    if(in_array($_SESSION['access'], $num)){
        return 1;
    }else{
        return 0;
    }
}

function get_cur_playing_type($db, $in_param = ''){
    /*$now_timestamp = time() - 120;
    $now_time = date("Y-m-d H:i:s", $now_timestamp);
    
    $query = "select mac from users where keep_alive>'$now_time'";
    $rs=$db->executeQuery($query);
    
    if($in_param == ''){
        $in_param = $_GET['in_param'];
    }
    
    $cur_play['itv'] = 0;
    $cur_play['vclub'] = 0;
    $cur_play['karaoke'] = 0;
    $cur_play['aclub'] = 0;
    $cur_play['radio'] = 0;
    
    while(@$rs->next()){
        $arr=$rs->getCurrentValuesAsHash();
        $sql = "select type from user_log where mac='{$arr['mac']}' order by time desc limit 0,1";
        $rs2=$db->executeQuery($sql);
        $type = $rs2->getValueByName(0, 'type');
        if ($type == 1){
            $cur_play['itv']++;
        }else if($type == 2){
            $cur_play['vclub']++;
        }else if($type == 3){
            $cur_play['karaoke']++;
        }else if($type == 4){
            $cur_play['aclub']++;
        }else if($type == 5){
            $cur_play['radio']++;
        }
    }
    if (in_array($in_param, $cur_play)){
        return $cur_play[$in_param];
    }else{
        return "wrong input param";
    }*/
    return get_cur_active_playing_type($db, $in_param);
}

function get_cur_active_playing_type($db, $in_param = ''){
    $now_timestamp = time() - 120;
    $now_time = date("Y-m-d H:i:s", $now_timestamp);
    
    /*$query = "select mac from users where keep_alive>'$now_time'";
    $rs=$db->executeQuery($query);*/
    
    if($in_param == ''){
        $in_param = $_GET['in_param'];
    }
    
/*    $cur_play['itv'] = 0;
    $cur_play['vclub'] = 0;
    $cur_play['karaoke'] = 0;
    $cur_play['aclub'] = 0;
    $cur_play['radio'] = 0;*/
    
    /*$from_timestamp = time() - 7200;
    $from_time = date("Y-m-d H:i:s", $from_timestamp);*/
    
    if ($in_param == 'itv'){
        $type = 1;
    }else if($in_param == 'vclub'){
        $type = 2;
    }else if($in_param == 'karaoke'){
        $type = 3;
    }else if($in_param == 'aclub'){
        $type = 4;
    }else if($in_param == 'radio'){
        $type = 5;
    }else{
        $type = 100;
    }
    
    $sql = "select count(*) as counter from users where UNIX_TIMESTAMP(keep_alive) > $now_timestamp and now_playing_type=$type";
    $rs=$db->executeQuery($sql);
    $counter = $rs->getValueByName(0, 'counter');
    /*while(@$rs->next()){
        $arr=$rs->getCurrentValuesAsHash();
        $sql = "select type from user_log where mac='{$arr['mac']}' and time>'$from_time' order by time desc limit 0,1";
        $rs2=$db->executeQuery($sql);
        $type = $rs2->getValueByName(0, 'type');
        if ($type == 1){
            $cur_play['itv']++;
        }else if($type == 2){
            $cur_play['vclub']++;
        }else if($type == 3){
            $cur_play['karaoke']++;
        }else if($type == 4){
            $cur_play['aclub']++;
        }else if($type == 5){
            $cur_play['radio']++;
        }
    }
    if (in_array($in_param, $cur_play)){
        return $cur_play[$in_param];
    }else{
        return "wrong input param";
    }*/
    return $counter;
}

function get_cur_infoportal($db){
    $now_timestamp = time() - 120;
    $now_time = date("Y-m-d H:i:s", $now_timestamp);
    $sql = "select count(*) as counter from users where UNIX_TIMESTAMP(keep_alive) > $now_timestamp and now_playing_type>=20 and now_playing_type<=29";
    $rs=$db->executeQuery($sql);
    $counter = $rs->getValueByName(0, 'counter');
    return $counter;
}

function get_last5min_play($db, $in_param = ''){
    
    if($in_param == ''){
        $in_param = $_GET['in_param'];
    }
    
    $in_param_arr = array(
                        'itv'     => 1,
                        'vclub'   => 2,
                        'karaoke' => 3,
                        'aclub'   => 4,
                        'radio'   => 4
                    );
    
    $now_timestamp = time() - 330;
    $now_time = date("Y-m-d H:i:s", $now_timestamp);
    
    if ($in_param_arr[$in_param]){
        //$sql = "select * from user_log where time>'$now_time' and type={$in_param_arr[$in_param]} and (action='play()' or action='play_not_to()') group by mac order by time desc";
        $sql = "select * from user_log where time>'$now_time' and type={$in_param_arr[$in_param]} and action='play' group by mac order by time desc";
        $rs=$db->executeQuery($sql);
        $count = $rs->getRowCount();
        return $count;
    }
}

function get_cur_users($db, $in_param = ''){
    if($in_param == ''){
        $in_param = $_GET['in_param'];
    }
    
    $now_timestamp = time() - 120;
    $now_time = date("Y-m-d H:i:s", $now_timestamp);
    
    if ($in_param == 'online'){
        $sql = "select count(*) as status from users where keep_alive>'$now_time'";
    }elseif ($in_param == 'offline'){
        $sql = "select count(*) as status from users where keep_alive<'$now_time'";
    }
    if (@$sql){
        $rs=$db->executeQuery($sql);
        $status = @$rs->getValueByName(0, 'status');
        return $status;
    }
}

function datetime2timestamp($datetime){
    preg_match("/(\d+)-(\d+)-(\d+) (\d+):(\d+):(\d+)/", $datetime, $arr);
    //return @mktime($arr[4], $arr[5], 0, $arr[2], $arr[3], $arr[1]);
    return @mktime($arr[4], $arr[5], $arr[6], $arr[2], $arr[3], $arr[1]);
}

function xmltvdatetime2datetime($datetime){
    $date  = substr($datetime, 0,4)."-".substr($datetime, 4,2)."-".substr($datetime, 6,2);
    $date .= " ".substr($datetime,8,2).":".substr($datetime,10,2).":".substr($datetime,12,2);
    return $date;
}

function get_str_lang($str){
    $lang = 0;
    $first_l = substr($str, 0, 1);
    if (preg_match("/[а-я,А-Я]/",$first_l)){
        $lang = 0;
    }else if (preg_match("/[a-z,A-Z]/",$first_l)){
        $lang = 1;
    }else if (preg_match("/[0-9]/",$first_l)){
        $lang = 2;
    }
    return $lang;
}

function get_first_mpg($path){
    if ($handle = @opendir($path)) {
        while (false !== ($file = readdir($handle))) {
            if ($file != "." && $file != ".." && preg_match("/([\S\s]+).mpg$/", $file)) { 
                closedir($handle); 
                return $file;
            } 
        }
    }
    @closedir($handle); 
    return 0;
}

function js_redirect($to, $delay){
   ?>   
    <html>
    <head>
    <meta HTTP-EQUIV="refresh" content="<?echo $delay?>; URL=<?echo $to?>">
    </head>
    <body>
    </body>
    </html>
    <?
}

function check_keep_alive($time){
    $keep_alive_ts = datetime2timestamp($time);
    $now_ts = time();
    $dif_ts = $now_ts - $keep_alive_ts;
    if ($dif_ts > 2*60){
        return 0;
    }else{
        return 1;
    }
}

function check_keep_alive_txt($time){
    if (check_keep_alive($time)){
        return '<font color="Green">online</font>';
    }else{
        return '<font color="Red">offline</font>';
    }
}

function get_sub_channels($id = 0){
    $db = Database::getInstance(DB_NAME);
    
    if ($id == 0){
        $id = intval(@$_GET['id']);
    }
    
    $sql = "select * from itv_subscription where uid=$id";
    $rs = $db->executeQuery($sql);
    $sub_ch = unserialize(base64_decode($rs->getValueByName(0, 'sub_ch')));
    if (!is_array($sub_ch)){
        return array();
    }else{
        return $sub_ch;
    }
}

function get_bonus_channels($id = 0){
    $db = Database::getInstance(DB_NAME);
    
    if ($id == 0){
        $id = intval(@$_GET['id']);
    }
    
    $sql = "select * from itv_subscription where uid=$id";
    $rs = $db->executeQuery($sql);
    $bonus_ch = unserialize(base64_decode($rs->getValueByName(0, 'bonus_ch')));
    if (!is_array($bonus_ch)){
        return array();
    }else{
        return $bonus_ch;
    }
}

function get_base_ch(){
    $db = Database::getInstance(DB_NAME);
    
    $sql = "select * from itv where base_ch=1";
    $rs = $db->executeQuery($sql);
    $bonus_ch = $rs->getValuesByName('id');
    return $bonus_ch;
}

function get_all_subscription_and_base_ch($uid){
    //echo "get_all_subscription_and_base_ch";
    $sub_ch   = get_sub_channels($uid);
    $bonus_ch = get_bonus_channels($uid);
    $base_ch = get_base_ch();
    
    $total_subscription = array_merge($sub_ch, $bonus_ch, $base_ch);
    return $total_subscription;
}

function kop2grn($kops){
    $grn = floor($kops/100);
    $kop = $kops - $grn*100;
    if ($kop < 10){
        $kop = '0'.$kop;
    }
    return $grn.'.'.$kop;
}

function get_operator_id_by_stb_ip($ip){
    $db = Database::getInstance(DB_NAME);
    
    $long_ip = long2ip($ip);
    $sql = "select * from operators_ip 
                  where
                   long_ip_from<$ip
               and long_ip_to>$ip";
    
    $rs   = $db->executeQuery($sql);
    $rows = $rs->getRowCount();
    
    if ($rows == 1){
        $operator_id = $rs->getValuesByName('operator_id');
    }else{
        $operator_id = 1;
    }
    return $operator_id;
}

function get_ip_range($ip_n_mask){
    $mask = 0xFFFFFFFF;
    
    $ip = explode("/", $ip_n_mask);
    
    for ($j = 0; $j < 32 - $ip[1]; $j++){
        $mask = $mask << 1;
    }
    
    $long_ip = ip2long($ip[0]);
    
    $ip_range = array();
    $ip_range['from'] = long2ip($lip&$mask);
    $ip_range['to']   = long2ip(($lip&$mask)+(~$mask));
    return $ip_range;
}

/*
function save_series($arr, $path){
    $db = Database::getInstance(DB_NAME);
    
    sort($arr);
    $query = "update video set series='".serialize($arr)."' where path='$path'";
    $rs=$db->executeQuery($query);
}
*/

function set_video_status($id, $val){
    $db = Database::getInstance(DB_NAME);
    $query = "update video set status=$val where id=$id";
    $rs=$db->executeQuery($query);
}

function set_karaoke_status($id, $val){
    $db = Database::getInstance(DB_NAME);
    $query = "update karaoke set status=$val where id=$id";
    $rs=$db->executeQuery($query);
}

function get_storage_use($db, $in_param = ''){
    if($in_param == ''){
        $in_param = $_GET['in_param'];
    }
    
    $now_timestamp = time() - 120;
    $now_time = date("Y-m-d H:i:s", $now_timestamp);
    
    $sql = "select count(*) as counter from users where keep_alive>'$now_time' and storage_name='$in_param' and now_playing_type=2";

    $rs=$db->executeQuery($sql);
    $status = @$rs->getValueByName(0, 'counter');
    return $status;
}

function get_anec_rating($id){
    $db = Database::getInstance(DB_NAME);
    $sql = "select count(*) as count from anec_rating where anec_id=$id";
    $rs1 = $db->executeQuery($sql);
    $rating = @intval($rs1->getValueByName(0, 'count'));
    return $rating;
}

function get_anec_voted($id, $stb_id){
    $db = Database::getInstance(DB_NAME);
    $sql = "select count(*) as count from anec_rating where uid=$stb_id and anec_id=$id";
    $rs1 = $db->executeQuery($sql);
    if (@intval($rs1->getValueByName(0, 'count')) == 1){
        return 1;
    }else{
        return 0;
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
?>