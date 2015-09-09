<?php

include "./common.php";

set_time_limit(0);

$apps = new AppsManager();
$apps->startAutoUpdate();