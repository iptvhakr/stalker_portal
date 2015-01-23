<?php

function get_save_folder($id){
    
    $dir_name = ceil($id/100);
    $dir_path = realpath(PROJECT_PATH.'/../'.Config::getSafe('screenshots_path', 'screenshots/')).'/'.$dir_name;
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

/**
 * @deprecated
 */
function check_db_user_login($login, $pass){

    $user = Mysql::getInstance()
        ->from('administrators')
        ->where(array(
            'login' => $login
        ))
        ->get()
        ->first();

    if ($user['pass'] == md5($pass)){
        $_SESSION['uid'] = $user['id'];
        $_SESSION['login'] = $login;
        $_SESSION['pass']  = $user['pass'];
        $_SESSION['access']  = $user['access'];
        return 1;
    }else{
        return 0;
    }
}

/**
 * @deprecated
 */
function check_session_user_login(){

    if (empty($_SESSION['login']) || empty($_SESSION['pass'])){
        return 0;
    }

    $user = Mysql::getInstance()
        ->from('administrators')
        ->where(array(
            'login' => $_SESSION['login']
        ))
        ->get()
        ->first();

    if ($user['pass'] == $_SESSION['pass']){
        return 1;
    }else{
        return 0;
    }

}

/**
 * @deprecated
 */
function moderator_access(){
    if(!check_session_user_login()){
        header("Location: login.php");
        exit();
    }
}

/**
 * @deprecated
 */
function check_access($num = array()){
    $num[] = 0;
    if(in_array($_SESSION['access'], $num)){
        return 1;
    }else{
        return 0;
    }
}

function get_cur_playing_type($in_param = ''){
    return get_cur_active_playing_type($in_param);
}

function get_cur_active_playing_type($in_param = ''){
    $now_timestamp = time() - Config::get('watchdog_timeout')*2;
    
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

    return Mysql::getInstance()
        ->from('users')
        ->count()
        ->where(array(
            'UNIX_TIMESTAMP(keep_alive)>' => $now_timestamp,
            'now_playing_type'            => $type
        ))
        ->get()
        ->counter();
}

function get_cur_infoportal(){

    return Mysql::getInstance()
        ->from('users')
        ->count()
        ->where(array(
            'UNIX_TIMESTAMP(keep_alive)>' => time() - Config::get('watchdog_timeout')*2,
            'now_playing_type>='          => 20,
            'now_playing_type<='          => 29
        ))
        ->get()
        ->counter();
}

function get_last5min_play($in_param = ''){
    
    if($in_param == ''){
        $in_param = $_GET['in_param'];
    }
    
    $in_param_arr = array(
                        'itv'     => 1,
                        'vclub'   => 2,
                        'karaoke' => 3,
                        'aclub'   => 4,
                        'radio'   => 5
                    );
    
    $now_timestamp = time() - 330;
    $now_time = date("Y-m-d H:i:s", $now_timestamp);

    if (!array_key_exists($in_param, $in_param_arr)){
        return 0;
    }

    return Mysql::getInstance()
        ->from('user_log')
        ->count()
        ->where(array(
            'time>'  => $now_time,
            'type'   => $in_param_arr[$in_param],
            'action' => 'play'
        ))
        ->groupby('mac')
        ->get()
        ->counter();
}

function get_cur_users($in_param = ''){
    if($in_param == ''){
        $in_param = $_GET['in_param'];
    }
    
    $now_timestamp = time() - Config::get('watchdog_timeout')*2;
    $now_time = date("Y-m-d H:i:s", $now_timestamp);


    if ($in_param == 'online'){

        return Mysql::getInstance()
            ->from('users')
            ->count()
            ->where(array(
                'keep_alive>' => $now_time
            ))
            ->get()
            ->counter();

    }elseif ($in_param == 'offline'){

        return Mysql::getInstance()
            ->from('users')
            ->count()
            ->where(array(
                'keep_alive<' => $now_time
            ))
            ->get()
            ->counter();
    }else{
        return 0;
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

    if ($id == 0){
        $id = intval(@$_GET['id']);
    }

    $sub_ch = Mysql::getInstance()
        ->from('itv_subscription')
        ->where(array(
            'uid' => $id
        ))
        ->get()
        ->first('sub_ch');

    $sub_ch = unserialize(base64_decode($sub_ch));

    if (!is_array($sub_ch)){
        return array();
    }else{
        return $sub_ch;
    }
}

function get_bonus_channels($id = 0){

    if ($id == 0){
        $id = intval(@$_GET['id']);
    }

    $bonus_ch = Mysql::getInstance()
        ->from('itv_subscription')
        ->where(array(
            'uid' => $id
        ))
        ->get()
        ->first('bonus_ch');

    $bonus_ch = unserialize(base64_decode($bonus_ch));

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

function set_karaoke_status($id, $val){
    return Mysql::getInstance()->update('karaoke', array('status' => $val), array('id' => $id));
}

function get_storage_use($in_param = ''){
    if($in_param == ''){
        $in_param = $_GET['in_param'];
    }

    $now_timestamp = time() - Config::get('watchdog_timeout')*2;
    $now_time = date("Y-m-d H:i:s", $now_timestamp);

    return Mysql::getInstance()
        ->from('users')
        ->count()
        ->where(array(
            'keep_alive>'      => $now_time,
            'storage_name'     => $in_param,
            'now_playing_type' => 2
        ))
        ->get()
        ->counter();
}
