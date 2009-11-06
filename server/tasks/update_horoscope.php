<?php
/*
    
*/
error_reporting(E_ALL);

include "../conf_serv.php";
include "../lib/func.php";

$horoscope = new Horoscope();
$horoscope->getDataFromRSS();

?>