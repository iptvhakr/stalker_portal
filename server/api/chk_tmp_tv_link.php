<?php

include "./common.php";

$result = Itv::checkTemporaryLink($_GET['key']);

if (!$result){
    $result = '/404/';
}

header("X-Accel-Redirect: ".$result);

?>