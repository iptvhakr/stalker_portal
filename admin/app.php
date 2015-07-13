<?php

use Symfony\Component\Translation\Loader\PoFileLoader;
use Neutron\Silex\Provider\ImagineServiceProvider;

require_once __DIR__ . '/vendor/autoload.php';
define('PROJECT_PATH', realpath(dirname(__FILE__) . '/../server/'));
require_once PROJECT_PATH . '/../storage/config.php';

$_SERVER['TARGET'] = 'ADM';

$locales = array();

$allowed_locales = \Config::get("allowed_locales");

foreach ($allowed_locales as $lang => $locale){
    $locales[substr($locale, 0, 2)] = $locale;
}

$accept_language = !empty($_SERVER["HTTP_ACCEPT_LANGUAGE"]) ? $_SERVER["HTTP_ACCEPT_LANGUAGE"] : null;

if (!empty($_COOKIE['language']) && (array_key_exists($_COOKIE['language'], $locales) || in_array($_COOKIE['language'], $locales))){
    $language = substr($_COOKIE['language'], 0, 2);
}else if ($accept_language && array_key_exists(substr($accept_language, 0, 2), $locales)){
    $language = substr($accept_language, 0, 2);
}else{
    $language = key($locales);
}
$locale = $locales[$language];

setcookie("debug_key", "", time() - 3600, "/");

setlocale(LC_MESSAGES, $locale);
setlocale(LC_TIME, $locale);
putenv('LC_MESSAGES='.$locale);
bindtextdomain('stb', PROJECT_PATH.'/locale');
textdomain('stb');
bind_textdomain_codeset('stb', 'UTF-8');

$app = new Silex\Application();

$app['debug'] = true;

$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\ValidatorServiceProvider());
$app->register(new Silex\Provider\FormServiceProvider());
$app->register(new \nymo\Silex\Provider\BreadCrumbServiceProvider());
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\TranslationServiceProvider(), array(
    'locale' => $language,
    'locale_fallbacks' => array($language),
));
$app['allowed_locales'] = $allowed_locales;

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.options' => array(
        'cache' => isset($app['twig.options.cache']) ? $app['twig.options.cache'] : false,
        'strict_variables' => true
    ),
    'twig.path' => __DIR__ . '/resources/views',
));

$app->register(new ImagineServiceProvider());

$app["language"] = $language;
$app['lang']=$lang=array($language);
$app["locale"] = $locale;
$app['translator'] = $app->share($app->extend('translator', function($translator, $app){
            $lang = (!empty($app["language"])? $app["language"]: "ru");
            $translator->addLoader('po', new PoFileLoader());
            $translator->addResource('po', __DIR__."/../server/locale/$lang/LC_MESSAGES/stb.po", $lang);
            $translator->setLocale($lang);
            return $translator;
        }));
$app["breadcrumbs.separator"] = "";        
$app['twig'] = $app->share(
        $app->extend(
                'twig', function ($twig, $app) {
            $twig->addExtension(new \nymo\Twig\Extension\BreadCrumbExtension($app));
            return $twig;
        }
        )
);

return require_once 'controllers.php';
