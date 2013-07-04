<?php

include "./common.php";

$result = TvArchive::checkTemporaryTimeShiftToken($_GET['key']);

if (!$result){
    $result = '/404/';
}

header("X-Accel-Redirect: ".$result);