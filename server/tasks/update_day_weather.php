<?php
/**
 * @deprecated since version 4.7.3. Use update_weatherco_fullcurrent.php and update_weatherco_fullforecast.php
 */

error_reporting(E_ALL);

include "./common.php";

$weather = new Gismeteo();
$weather->getDataFromXML();

?>