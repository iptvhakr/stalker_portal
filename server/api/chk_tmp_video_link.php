<?php

include "./common.php";

$result = Master::checkTemporaryLink($_GET['key']);

if (!$result){
    $result = '/404/';
}

header("X-Accel-Redirect: ".$result);

?>