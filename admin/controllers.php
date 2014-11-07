<?php

$app->get('/', function() use ($app) {
    return $app->redirect('tv-channels');
})->bind('homepage');


$app->get('/tv-channels', 'Controller\TvChannelsController::index')
    ->bind('tv-channels');

return $app;