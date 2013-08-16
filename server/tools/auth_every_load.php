<?php

include "../common.php";

if (empty($_REQUEST['login']) || empty($_REQUEST['password']) || empty($_REQUEST['mac'])){
    echo '{"status":"ERROR","results":false,"error":"Login, password and mac required"}';
    exit;
}

sleep(1); // anti brute-force delay

$login    = $_REQUEST['login'];
$password = $_REQUEST['password'];
$mac      = $_REQUEST['mac'];

$possible_user = Mysql::getInstance()->from('users')->where(array('login' => $login))->get()->first();

if (md5(md5($password).$possible_user['id']) === $possible_user['password']){
    $user = $possible_user;
}

if (empty($user)){
    echo error("User not exist or login-password mismatch");
}else{

    Mysql::getInstance()->update('users',
        array(
            'mac'          => '',
            'device_id'    => '',
            'access_token' => '',
        ),
        array('mac' => $mac)
    );

    Mysql::getInstance()->update('users',
        array('mac' => $mac),
        array('id' => $user['id'])
    );

    echo '{"status":"OK","results":true}';
}

function error($msg = ''){
    return '{"status":"OK","results":false,"error":"'.$msg.'"}';
}