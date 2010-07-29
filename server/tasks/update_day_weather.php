<?php
/*
    
*/
error_reporting(E_ALL);

include "../common.php";
include "../conf_serv.php";

$weather = new Gismeteo();
$weather->getDataFromXML();

?>