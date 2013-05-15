<?php
/*
    
*/
error_reporting(E_ALL);

include "./common.php";

$horoscope = new Horoscope();
$horoscope->getDataFromRSS();

?>