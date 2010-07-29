<?php
/*
    
*/
error_reporting(E_ALL);

include "../common.php";
include "../conf_serv.php";

$cur_weather = new Curweather();
$cur_weather->getDataFromGAPI();

?>