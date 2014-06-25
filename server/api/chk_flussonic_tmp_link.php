<?php

include "./common.php";

$uid = Itv::checkTemporaryLink($_GET['token']);

if (!$uid){
    header($_SERVER["SERVER_PROTOCOL"]." 403 Forbidden");
}else{
    header("X-AuthDuration: 36000");
    header("X-Unique: true");
    header("X-UserId: ".$uid);
    header($_SERVER["SERVER_PROTOCOL"]." 200 OK");
}