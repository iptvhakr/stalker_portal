<?php

include "../common.php";

if (empty($_REQUEST['login']) || empty($_REQUEST['password'])){
    echo '{"status":"ERROR","results":false,"error":"Login and password required"}';
    exit;
}

$login    = $_REQUEST['login'];
$password = $_REQUEST['password'];

$possible_user = Mysql::getInstance()->from('users')->where(array('login' => $login, 'mac' => ''))->get()->first();

if ((strlen($possible_user['password']) == 32 && md5(md5($password).$possible_user['id']) == $possible_user['password'])
    || (strlen($possible_user['password']) < 32 && $password == $possible_user['password'])){
    $user = $possible_user;
}

if (empty($user)){
    echo error("User not exist or login-password mismatch");
}else{
    echo '{"status":"OK","results":true}';
}

function error($msg = ''){
    return '{"status":"OK","results":false,"error":"'.$msg.'"}';
}

?>