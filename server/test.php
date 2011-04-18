<?php
error_reporting(E_ALL);
putenv("TZ=Europe/Zaporozhye");

$start_time = microtime(1);

require_once "common.php";

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

/*var_dump($db->select(array('itv.*','tv_genre.title as genres_name'))
->from(array('itv'))
->join('tv_genre', 'itv.tv_genre_id', 'tv_genre.id', 'INNER')
->where(array('status' => 1,'base_ch' => 1))
->get()
->first_row());*/

//var_dump(
//$db->where(array('id<=' => 5))->orderby('login', 'desc')->get('administrators')->all()
//);

//$weather = new Weatherco();
//var_dump($weather->getCurrent());

/*var_dump(
$db->update('administrators', array('name' => 'name'),array('id' => 1))
);*/

$stb = Stb::getInstance();

var_dump($stb->getProfile());

echo "</pre>";
/**/
$db = Mysql::getInstance();
$end_time = microtime(1);
$load_time = $end_time - $start_time;
echo "<br>\ngenerated in: ".round($load_time, 4)."s; queries: ".$db->get_num_queries()."; cache hits:".$db->get_cache_hits()." ".$debug->getErrorStr();

?>