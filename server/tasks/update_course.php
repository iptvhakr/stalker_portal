<?php
/*
    
*/
error_reporting(E_ALL);

include "./common.php";

$course = new Course();
$course->getDataFromURI();

$course = new CourseCbr();
$course->getDataFromURI();

?>