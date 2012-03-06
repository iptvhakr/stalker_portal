<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

define ('FATAL',E_USER_ERROR);
define ('ERROR',E_USER_WARNING);
define ('WARNING',E_USER_NOTICE);

if (!defined("PATH_SEPARATOR")){
    define("PATH_SEPARATOR", getenv("COMSPEC")? ";" : ":");
}

define('PROJECT_PATH', dirname(__FILE__));

ini_set("include_path", ini_get("include_path").PATH_SEPARATOR.PROJECT_PATH);

/*function __autoload($class_name) {

    $class = PROJECT_PATH.'/lib/'.strtolower($class_name).'.class.php';

    if (!file_exists($class)){
        throw new Exception('Class file for "'.$class_name.'" not found');
    }
    
    require $class;
}*/

require "lib/splclassloader.class.php";

$classLoader = new SplClassLoader(null, PROJECT_PATH.DIRECTORY_SEPARATOR.'lib');
$classLoader->setFileExtension('.class.php');
$classLoader->setExcludeNs('Stalker\Lib');
$classLoader->register();

if (Config::exist('default_timezone')){
    date_default_timezone_set(Config::get('default_timezone'));
}

if (Config::exist('http_proxy')){

    $default_context = array(
        'http' => array(
            'proxy'           => Config::get('http_proxy'),
            'request_fulluri' => true
        )
    );

    if (Config::exist('http_proxy_login') && Config::exist('http_proxy_password')){
        $default_context['http']['header'] = "Proxy-Authorization: Basic ".base64_encode(Config::get('http_proxy_login').":".Config::get('http_proxy_password'))."\r\n";
    }

    stream_context_set_default($default_context);
    libxml_set_streams_context(stream_context_create($default_context));
}

?>