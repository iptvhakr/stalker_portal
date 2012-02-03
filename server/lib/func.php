<?php

function get_save_folder($id){
    
    $dir_name = ceil($id/100);
    $dir_path = realpath(PROJECT_PATH.'/../screenshots/').'/'.$dir_name;
    //echo '$dir_path: '.$dir_path;
    if (!is_dir($dir_path)){
        umask(0);
        if (!mkdir ($dir_path, 0777)){
            return -1;
        }else{
            return $dir_path;
        }
    }else{
        return $dir_path;
    }
    
}

function transliterate($st) {

   $st = trim($st);
   
   $st = strtr($st, array(
        'а' => 'a',
        'б' => 'b',
        'в' => 'v',
        'г' => 'g',
        'д' => 'd',
        'е' => 'e',
        'ж' => 'g',
        'з' => 'z',
        'и' => 'i',
        'й' => 'y',
        'к' => 'k',
        'л' => 'l',
        'м' => 'm',
        'н' => 'n',
        'о' => 'o',
        'п' => 'p',
        'р' => 'r',
        'с' => 's',
        'т' => 't',
        'у' => 'u',
        'ф' => 'f',
        'ы' => 'i',
        'э' => 'e',
        'А' => 'A',
        'Б' => 'B',
        'В' => 'V',
        'Г' => 'G',
        'Д' => 'D',
        'Е' => 'E',
        'Ж' => 'G',
        'З' => 'Z',
        'И' => 'I',
        'Й' => 'Y',
        'К' => 'K',
        'Л' => 'L',
        'М' => 'M',
        'Н' => 'N',
        'О' => 'O',
        'П' => 'P',
        'Р' => 'R',
        'С' => 'S',
        'Т' => 'T',
        'У' => 'U',
        'Ф' => 'F',
        'Ы' => 'I',
        'Э' => 'E',
        'ё'=>"yo", 'х'=>"h", 'ц'=>"ts", 'ч'=>"ch", 'ш'=>"sh",
        'щ'=>"shch", 'ъ'=>'', 'ь'=>'', 'ю'=>"yu", 'я'=>"ya",
        'Ё'=>"Yo", 'Х'=>"H", 'Ц'=>"Ts", 'Ч'=>"Ch", 'Ш'=>"Sh",
        'Щ'=>"Shch", 'Ъ'=>'', 'Ь'=>'', 'Ю'=>"Yu", 'Я'=>"Ya",
        ' '=>"_", '!'=>"", '?'=>"", ','=>"", '.'=>"", '"'=>"", 
        '\''=>"", '\\'=>"", '/'=>"", ';'=>"", ':'=>"", '«'=>"", '»'=>"", '`'=>"", '-' => "-", '—' => "-"
   ));

   $st = preg_replace("/[^a-z0-9_-]/i", "", $st);

   return $st;
}

function check_db_user_login($login, $pass){
    $db = new Database();
    
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
    $db = new Database();
    
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

function moderator_access(){
    if(!check_session_user_login()){
        header("Location: login.php");
        exit();
    }
}

function check_access($num = array()){
    $num[] = 0;
    if(in_array($_SESSION['access'], $num)){
        return 1;
    }else{
        return 0;
    }
}

function get_cur_playing_type($db, $in_param = ''){
    return get_cur_active_playing_type($db, $in_param);
}

function get_cur_active_playing_type($db, $in_param = ''){
    $now_timestamp = time() - Config::get('watchdog_timeout')*2;
    $now_time = date("Y-m-d H:i:s", $now_timestamp);
    
    if($in_param == ''){
        $in_param = $_GET['in_param'];
    }

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

    return $counter;
}

function get_cur_infoportal($db){
    $now_timestamp = time() - Config::get('watchdog_timeout')*2;
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
    
    $now_timestamp = time() - Config::get('watchdog_timeout')*2;
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

function js_redirect($to, $msg = '', $delay = 2){
   ?>   
    <html>
    <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta http-equiv="refresh" content="<?echo $delay?>; URL=<?echo $to?>">
    </head>
    <body>
    <? echo $msg ?>
    </body>
    </html>
    <?
}

function check_keep_alive($time){
    $keep_alive_ts = datetime2timestamp($time);
    $now_ts = time();
    $dif_ts = $now_ts - $keep_alive_ts;
    if ($dif_ts > Config::get('watchdog_timeout')*2){
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
    $db = Database::getInstance();
    
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
    $db = Database::getInstance();
    
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

function kop2grn($kops){
    $grn = floor($kops/100);
    $kop = $kops - $grn*100;
    if ($kop < 10){
        $kop = '0'.$kop;
    }
    return $grn.'.'.$kop;
}

function set_video_status($id, $val){
    $db = Database::getInstance();
    $query = "update video set status=$val where id=$id";
    $rs=$db->executeQuery($query);
}

function set_karaoke_status($id, $val){
    $db = Database::getInstance();
    $query = "update karaoke set status=$val where id=$id";
    $rs=$db->executeQuery($query);
}

function get_storage_use($db, $in_param = ''){
    if($in_param == ''){
        $in_param = $_GET['in_param'];
    }
    
    $now_timestamp = time() - Config::get('watchdog_timeout')*2;
    $now_time = date("Y-m-d H:i:s", $now_timestamp);
    
    $sql = "select count(*) as counter from users where keep_alive>'$now_time' and storage_name='$in_param' and now_playing_type=2";

    $rs=$db->executeQuery($sql);
    $status = @$rs->getValueByName(0, 'counter');
    return $status;
}

?>