<?php

error_reporting(E_ALL);

include "./common.php";

use Stalker\Lib\Core\Config;

$handlers = Config::getSafe('exchange_rate_classes', array('Course', 'CourseCbr'));

foreach ($handlers as $handler){
    $course = new $handler;
    $course->getDataFromURI();
}
