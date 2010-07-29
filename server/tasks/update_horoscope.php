<?php
/*
    
*/
error_reporting(E_ALL);

include "../common.php";
include "../conf_serv.php";

$horoscope = new Horoscope();
$horoscope->getDataFromRSS();

?>