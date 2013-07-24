<?php

include "./common.php";

$result = TvArchive::checkTemporaryTimeShiftToken($_GET['key']);

if (!$result){
    $result = '/404/';
}

$result = preg_replace ("/([^\/]+)$/", $_GET['file'], $result);

header("X-Accel-Redirect: ".$result);