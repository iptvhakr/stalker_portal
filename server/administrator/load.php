<?php

require_once "./lib/config.php";
require_once "./lib/subsys/php.php";
require_once "./lib/data.php";
include "./common.php";

Admin::checkAuth();

$JsHttpRequest = new Subsys_JsHttpRequest_Php("utf-8");

$_RESULT = get_data(); 

echo "<b>REQUEST_URI:</b> ".$_SERVER['REQUEST_URI']."<br>";
