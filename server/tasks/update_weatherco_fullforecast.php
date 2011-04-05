<?php
/*
    
*/
error_reporting(E_ALL);

set_time_limit(0);

include "../conf_serv.php";
include "../common.php";

$weather = new Weatherco();
$weather->updateFullForecast();

?>