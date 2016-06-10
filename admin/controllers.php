<?php

namespace Controller;
use \Silex\Application;

$app['saveFiles'] = function() use ($app) {
    return new \Lib\Save($app);
};

$app->get('/{controller}', function($controller, $namespace = __NAMESPACE__) use ($app) {
    $action = 'index';
    $controllerName = "\\$namespace\\" . implode('', array_map('ucfirst', explode('-', strtolower($controller)))) . "Controller";
    if (class_exists($controllerName)) {
        $controller = new $controllerName($app);
        if ($controller instanceof $controllerName) {
            if (is_callable(array($controller, $action))) {
                return $controller->$action();
            }
        }
    }
    $app->abort(404, sprintf('No route found for: %s:%s', $controllerName, $action));
})->value('controller', 'index');

$app->get('/{controller}/{action}', function($controller, $action, $namespace = __NAMESPACE__) use ($app) {
    $action = (!empty($action)) ? str_replace('-', '_', $action) : 'index';
    $controllerName = "\\$namespace\\" . implode('', array_map('ucfirst', explode('-', strtolower($controller)))) . "Controller";
    if (class_exists($controllerName)) {
        $controller = new $controllerName($app);
        if ($controller instanceof $controllerName) {
            if (is_callable(array($controller, $action))) {
                return $controller->$action();
            }
        }
    }
    $app->abort(404, sprintf('No route found for: %s:%s', $controllerName, $action));
})->value('controller', 'index')->value('action', 'index');

$app->post('/{controller}', function($controller, $namespace = __NAMESPACE__) use ($app) {
    $action = 'index';
    $controllerName = "\\$namespace\\" . implode('', array_map('ucfirst', explode('-', strtolower($controller)))) . "Controller";
    if (class_exists($controllerName)) {
        $controller = new $controllerName($app);
        if ($controller instanceof $controllerName) {
            if (is_callable(array($controller, $action))) {
                return $controller->$action();
            }
        }
    }
    $app->abort(404, sprintf('No route found for: %s:%s', $controllerName, $action));
})->value('controller', 'index');

$app->post('/{controller}/{action}', function($controller, $action = '', $namespace = __NAMESPACE__) use ($app) {
    $action = (!empty($action)) ? str_replace('-', '_', $action) : 'index';
    $controllerName = "\\$namespace\\" . implode('', array_map('ucfirst', explode('-', strtolower($controller)))) . "Controller";
    if (class_exists($controllerName)) {
        $controller = new $controllerName($app);
        if ($controller instanceof $controllerName) {
            if (is_callable(array($controller, $action))) {
                return $controller->$action();
            }
        }
    }
    $app->abort(404, sprintf('No route found for: %s:%s', $controllerName, $action));
})->value('controller', 'index')->value('action', 'index');

return $app;
