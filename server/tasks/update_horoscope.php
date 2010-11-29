<?php
/*
    
*/
error_reporting(E_ALL);

include "../conf_serv.php";
include "../common.php";

$horoscope = new Horoscope();
$horoscope->getDataFromRSS();

?>