<?php
/*
    
*/
error_reporting(E_ALL);

include "../common.php";
include "../conf_serv.php";

$course = new Course();
$course->getDataFromURI();

?>