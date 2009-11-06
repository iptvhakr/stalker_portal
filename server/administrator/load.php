<?php
//session_start();
// Подключаем библиотеку поддержки.
require_once "./lib/config.php";
require_once "./lib/subsys/php.php";
require_once "./lib/data.php";
require_once "../lib/func.php";
require_once "../conf_serv.php";

$JsHttpRequest =& new Subsys_JsHttpRequest_Php("utf-8");

// Формируем результат прямо в виде PHP-массива!
$_RESULT = get_data(); 

// Демонстрация отладочных сообщений.
echo "<b>REQUEST_URI:</b> ".$_SERVER['REQUEST_URI']."<br>";
//echo "<b>Loader used:</b> ".$JsHttpRequest->LOADER;
?>