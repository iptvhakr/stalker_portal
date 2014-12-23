<?php

require_once __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Translation\Loader\YamlFileLoader;

define('PROJECT_PATH', realpath(dirname(__FILE__) . '/../server/'));
$app = new Silex\Application();

$app['debug'] = true;

$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\ValidatorServiceProvider());
$app->register(new Silex\Provider\FormServiceProvider());
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\TranslationServiceProvider(), array(
    'locale' => 'ru',
    'locale_fallbacks' => array('ru'),
));

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.options' => array(
        'cache' => isset($app['twig.options.cache']) ? $app['twig.options.cache'] : false,
        'strict_variables' => true
    ),
    'twig.path' => __DIR__ . '/resources/views',
));

$app['translator'] = $app->share($app->extend('translator', function($translator, $app) {
            $translator->addLoader('yaml', new YamlFileLoader());

            //$translator->addResource('yaml', __DIR__.'/resources/locales/ru.yml', 'ru');

            return $translator;
        }));

return require_once 'controllers.php';
