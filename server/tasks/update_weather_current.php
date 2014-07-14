<?php

error_reporting(E_ALL);

set_time_limit(0);

sleep(rand(0, 300));

include "./common.php";

$weather = new Weather();
$weather->updateFullCurrent();
