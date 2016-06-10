<?php
$start_script_time = microtime(TRUE);
require_once __DIR__.'/../../admin/app.php';

$app->run();

if ($app->offsetExists('monolog') && !empty($start_script_time)) {
    $end_script_time = microtime(TRUE);
    $app['monolog']->addInfo(sprintf("Script end timestamp - '%s'", $end_script_time) . PHP_EOL);
    $app['monolog']->addInfo(sprintf("Script execution - '%s'", number_format($end_script_time - $start_script_time, 3, '.', ' ')) . PHP_EOL);
    $app['monolog']->addInfo(str_pad('', 80, '-') . PHP_EOL);
}
