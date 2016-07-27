<?php

include "./common.php";

set_time_limit(0);

$apps = new AppsManager();
$apps->startAutoUpdate();

$launcher_apps = new SmartLauncherAppsManager();
$launcher_apps->startAutoUpdate();

$launcher_apps->syncApps();

$launcher_apps->updateAllAppsInfo();