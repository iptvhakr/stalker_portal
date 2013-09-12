<?php

error_reporting(E_ALL);

include "./common.php";

$handlers = Config::getSafe('exchange_rate_classes', array('Course', 'CourseCbr'));

foreach ($handlers as $handler){
    $course = new $handler;
    $course->getDataFromURI();
}
