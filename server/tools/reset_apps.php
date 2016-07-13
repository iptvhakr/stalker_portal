<?php

if (php_sapi_name() != "cli") {
    exit;
}

include "../common.php";

$apps_manager = new SmartLauncherAppsManager();
try{
    $apps_manager->resetApps();
}catch (SmartLauncherAppsManagerConflictException $e){
    echo $e->getMessage()."\n";
    $conflicts = $e->getConflicts();
    foreach ($conflicts as $conflict){
        echo "\tApplication: ".$conflict['target'].":\n";
        echo "\t\tDependency: ".$conflict['alias']."\n";
        echo "\t\tExpression: ".$conflict['current_version']."\n";
    }
}
