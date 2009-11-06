<?php
/*
    
*/
error_reporting(E_ALL);

include "../conf_serv.php";
include "../lib/func.php";

$cur_weather = new Curweather();
$cur_weather->getDataFromGAPI();

?>