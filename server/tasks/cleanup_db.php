<?php

include "./common.php";

$date_format = 'Y-m-d H:i:s';


$from_id = Mysql::getInstance()
    ->select('max(id) as max_id')
    ->from('events')
    ->where(array('eventtime<' => 'now()'))
    ->get()
    ->first('max_id');

if ($from_id){
    Mysql::getInstance()->delete('events', array('id<' => $from_id));
}


$from_id = Mysql::getInstance()
    ->select('max(id) as max_id')
    ->from('vclub_not_ended')
    ->where(array('added<' => date($date_format, time()-1209600)))
    ->get()
    ->first('max_id');

if ($from_id){
    Mysql::getInstance()->delete('vclub_not_ended', array('id<' => $from_id));
}


$from_id = Mysql::getInstance()
    ->select('max(id) as max_id')
    ->from('tv_reminder')
    ->where(array('fire_time<' => 'now()'))
    ->get()
    ->first('max_id');

if ($from_id){
    Mysql::getInstance()->delete('tv_reminder', array('id<' => $from_id));
}


$from_id = Mysql::getInstance()
    ->select('max(id) as max_id')
    ->from('storages_failure')
    ->where(array('added<' => date($date_format, time()-604800)))
    ->get()
    ->first('max_id');

if ($from_id){
    Mysql::getInstance()->delete('storages_failure', array('id<' => $from_id));
}


$from_id = Mysql::getInstance()
    ->select('max(id) as max_id')
    ->from('played_video')
    ->where(array('playtime<' => date($date_format, time()-2764800)))
    ->get()
    ->first('max_id');

if ($from_id){
    Mysql::getInstance()->delete('played_video', array('id<' => $from_id));
}


$from_id = Mysql::getInstance()
    ->select('max(id) as max_id')
    ->from('played_itv')
    ->where(array('playtime<' => date($date_format, time()-2764800)))
    ->get()
    ->first('max_id');

if ($from_id){
    Mysql::getInstance()->delete('played_itv', array('id<' => $from_id));
}


$from_id = Mysql::getInstance()
    ->select('max(id) as max_id')
    ->from('played_tv_archive')
    ->where(array('playtime<' => date($date_format, time()-2764800)))
    ->get()
    ->first('max_id');

if ($from_id){
    Mysql::getInstance()->delete('played_tv_archive', array('id<' => $from_id));
}


$from_id = Mysql::getInstance()
    ->select('max(id) as max_id')
    ->from('media_claims_log')
    ->where(array('added<' => date($date_format, time()-2592000)))
    ->get()
    ->first('max_id');

if ($from_id){
    Mysql::getInstance()->delete('media_claims_log', array('id<' => $from_id));
}


if (Config::getSafe('use_optimize_table', true)){

    Mysql::getInstance()->query('ALTER TABLE `events` DROP INDEX `eventtime`');
    Mysql::getInstance()->query('ALTER TABLE `events` DROP INDEX `uid`');
    Mysql::getInstance()->query('ALTER TABLE `events` DROP INDEX `ended`');
    Mysql::getInstance()->query('OPTIMIZE TABLE `events`');
    Mysql::getInstance()->query('ALTER TABLE `events` ADD INDEX `eventtime` (`eventtime`)');
    Mysql::getInstance()->query('ALTER TABLE `events` ADD INDEX `uid` (`uid`)');
    Mysql::getInstance()->query('ALTER TABLE `events` ADD INDEX `ended` (`ended`)');

    Mysql::getInstance()->query('ALTER TABLE `vclub_not_ended` DROP INDEX `uid`');
    Mysql::getInstance()->query('OPTIMIZE TABLE `vclub_not_ended`');
    Mysql::getInstance()->query('ALTER TABLE `vclub_not_ended` ADD INDEX `uid` (`uid`, `video_id`)');

    Mysql::getInstance()->query('ALTER TABLE `tv_reminder` DROP INDEX `tv_program_id`');
    Mysql::getInstance()->query('ALTER TABLE `tv_reminder` DROP INDEX `tv_program_real_id`');
    Mysql::getInstance()->query('ALTER TABLE `tv_reminder` DROP INDEX `ch_id_real_id`');
    Mysql::getInstance()->query('ALTER TABLE `tv_reminder` DROP INDEX `mac_time`');
    Mysql::getInstance()->query('OPTIMIZE TABLE `tv_reminder`');
    Mysql::getInstance()->query('ALTER TABLE `tv_reminder` ADD INDEX `tv_program_id` (`tv_program_id`)');
    Mysql::getInstance()->query('ALTER TABLE `tv_reminder` ADD INDEX `tv_program_real_id` (`tv_program_real_id`)');
    Mysql::getInstance()->query('ALTER TABLE `tv_reminder` ADD INDEX `ch_id_real_id` (`tv_program_real_id`, `ch_id`)');
    Mysql::getInstance()->query('ALTER TABLE `tv_reminder` ADD INDEX `mac_time` (`mac`, `fire_time`)');

    Mysql::getInstance()->query('ALTER TABLE `storages_failure` DROP INDEX `storage`');
    Mysql::getInstance()->query('OPTIMIZE TABLE `storages_failure`');
    Mysql::getInstance()->query('ALTER TABLE `storages_failure` ADD INDEX `storage` (`storage_id`, `added`)');

    Mysql::getInstance()->query('ALTER TABLE `played_video` DROP INDEX `video_id_playtime`');
    Mysql::getInstance()->query('OPTIMIZE TABLE `played_video`');
    Mysql::getInstance()->query('ALTER TABLE `played_video` ADD INDEX `video_id_playtime` (`video_id`, `playtime`)');

    Mysql::getInstance()->query('OPTIMIZE TABLE `played_itv`');

    Mysql::getInstance()->query('OPTIMIZE TABLE `played_tv_archive`');

    Mysql::getInstance()->query('ALTER TABLE `media_claims_log` DROP INDEX `added`');
    Mysql::getInstance()->query('OPTIMIZE TABLE `media_claims_log`');
    Mysql::getInstance()->query('ALTER TABLE `media_claims_log` ADD INDEX `added` (`added`)');
}