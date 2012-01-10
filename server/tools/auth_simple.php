<?php

include "../common.php";

if (empty($_REQUEST['login']) || empty($_REQUEST['password'])){
    echo '{"status":"ERROR","results":false,"error":"Login and password required"}';
    exit;
}

$login    = $_REQUEST['login'];
$password = $_REQUEST['password'];

$user = Mysql::getInstance()->from('users')->where(array('login' => $login, 'password' => $password, 'mac' => ''))->get()->first();

if (empty($user)){
    echo error("User not exist or login-password mismatch");
}else{
    echo '{"status":"OK","results":true}';
}

function error($msg = ''){
    return '{"status":"OK","results":false,"error":"'.$msg.'"}';
}

?>