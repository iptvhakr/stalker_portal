<?php

if (php_sapi_name() != "cli") {
    exit;
}

include "../common.php";

$apps_manager = new SmartLauncherAppsManager();
$apps_manager->initApps();