<?php
/*
    
*/
error_reporting(E_ALL);

include "../conf_serv.php";
include "../lib/func.php";

$weather = new Gismeteo();
$weather->getDataFromXML();

?>