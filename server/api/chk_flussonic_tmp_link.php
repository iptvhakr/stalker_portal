<?php

include "./common.php";

$result = Itv::checkTemporaryLink($_GET['token']);

if (!$result){
    header($_SERVER["SERVER_PROTOCOL"]." 403 Forbidden");
}else{
    header($_SERVER["SERVER_PROTOCOL"]." 200 OK");
}

