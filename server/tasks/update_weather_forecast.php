<?php

error_reporting(E_ALL);

set_time_limit(0);

sleep(rand(300, 600));

include "./common.php";

$weather = new Weather();
$weather->updateFullForecast();
