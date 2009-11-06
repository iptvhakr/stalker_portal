<?php
error_reporting(E_ALL);
putenv("TZ=Europe/Zaporozhye");

$start_time = microtime(1);

require_once "./lib/config.php";
require_once "./lib/subsys/php.php";
require_once "./lib/func.php";
require_once "./conf_serv.php";
set_error_handler(array($debug = Debug::getInstance(), 'parsePHPError'));
/* TEST */
echo "<pre>";
//$weather = new Weather();
//var_dump($weather->getData());
//$epg = new Epg();
//$epg->updateEpg();
//$horoscope = new Horoscope();
//var_dump($horoscope->getData());
//$cur_weather = new Curweather();
//var_dump($cur_weather->getData());
//$weather = new Gismeteo();
//var_dump($weather->getData());
//$stb = new Stb();
//var_dump($stb->sendEvent('all', 'user_event', 'send_msg', 'test'));
//var_dump($stb->sendEvent('uid', 597, 'sys_event', 'update_subscription'));
//$course = new Course();
//var_dump($course->getData());
//$event = new SysEvent();
//$event->setUserListById(1500);
//$event->sendMsg('test');


echo "</pre>";
/**/
$end_time = microtime(1);
$load_time = $end_time - $start_time;
$db = Database::getInstance(DB_NAME);
echo "<br>\ngenerated in: ".round($load_time, 2)."s; query counter: $db->query_counter; ".$debug->getErrorStr();

?>