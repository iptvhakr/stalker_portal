<?php
/*
    
*/
error_reporting(E_ALL);

include "../conf_serv.php";
include "../lib/func.php";

$course = new Course();
$course->getDataFromURI();

?>