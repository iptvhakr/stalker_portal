<?php
/**
 * @deprecated since version 4.7.3. Use update_weather_current.php and update_weather_forecast.php
 */

error_reporting(E_ALL);

include "./common.php";

$weather = new Gismeteo();
$weather->getDataFromXML();

?>