<?php

use Symfony\Component\Translation\Loader\PoFileLoader;
use Neutron\Silex\Provider\ImagineServiceProvider;

require_once __DIR__ . '/vendor/autoload.php';
define('PROJECT_PATH', realpath(dirname(__FILE__) . '/../server/'));

require_once PROJECT_PATH . '/../storage/config.php';
require_once PROJECT_PATH . '/../server/lib/core/config.class.php';
require_once PROJECT_PATH . '/../server/lib/core/mysql.class.php';
require_once PROJECT_PATH . '/../server/lib/core/databaseresult.class.php';
require_once PROJECT_PATH . '/../server/lib/core/mysqlresult.class.php';
require_once PROJECT_PATH . '/../server/lib/core/middleware.class.php';
require_once PROJECT_PATH . '/../server/lib/core/stb.class.php';
require_once PROJECT_PATH . '/../server/lib/core/cacheresult.class.php';
require_once PROJECT_PATH . '/../server/lib/core/cache.class.php';
require_once PROJECT_PATH . '/../server/lib/core/licensemanager.class.php';
require_once PROJECT_PATH . '/../server/lib/oauth/authaccesshandler.class.php';

use Stalker\Lib\Core\Config;

$_SERVER['TARGET'] = 'ADM';

$locales = array();

$allowed_locales = Config::get("allowed_locales");

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
$app['debug'] = Config::getSafe('admin_panel_debug', FALSE);

if (Config::getSafe('admin_panel_debug_log', FALSE)) {
    $log_date = new \DateTime();
    $log_dir = __DIR__ . "/logs";
    if (!is_dir($log_dir)) {
        mkdir($log_dir);
    }
    if (is_dir($log_dir)) {
        $log_file = "$log_dir/development_" . $log_date->format('Y-m-d') . ".log";
        if (!is_file($log_file) && ($log_file_h = fopen($log_file, "a+")) !== FALSE) {
            fclose($log_file_h);
        }
        if (is_file($log_file)) {
            $app->register(new Silex\Provider\MonologServiceProvider(), array(
                'monolog.logfile' => $log_file
            ));

            $app['monolog']->addInfo(str_pad('', 80, '-') . PHP_EOL);
            $app['monolog']->addInfo(sprintf("Script begin timestamp - '%s'", $start_script_time) . PHP_EOL);
        }
    }
}

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

$app['twig.options.cache'] = __DIR__ . '/resources/cache/twig';

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.options' => array(
        'cache' => isset($app['twig.options.cache']) && is_dir($app['twig.options.cache']) && is_writable($app['twig.options.cache']) ? $app['twig.options.cache'] : false,
        'strict_variables' => true,
        'auto_reload' => true
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
$app['twig'] = $app->share( $app->extend( 'twig', function ($twig, $app) {
        $twig->addExtension(new \nymo\Twig\Extension\BreadCrumbExtension($app));
        $twig->addExtension(new Twig_Extension_Optimizer());
        return $twig;
    })
);

return require_once 'controllers.php';
