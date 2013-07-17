<?php

include "./common.php";

Mysql::getInstance()->query('DELETE FROM `user_log` WHERE `time`<FROM_UNIXTIME(UNIX_TIMESTAMP(NOW())-86400)');
Mysql::getInstance()->query('OPTIMIZE TABLE `user_log`');

Mysql::getInstance()->query('DELETE FROM `events` WHERE `eventtime`<now()');
Mysql::getInstance()->query('OPTIMIZE TABLE `events`');

Mysql::getInstance()->query('DELETE FROM `vclub_not_ended` WHERE `added`<FROM_UNIXTIME(UNIX_TIMESTAMP(NOW())-1209600)');
Mysql::getInstance()->query('OPTIMIZE TABLE `vclub_not_ended`');

Mysql::getInstance()->query('DELETE FROM `tv_reminder` WHERE `fire_time`<NOW()');
Mysql::getInstance()->query('OPTIMIZE TABLE `tv_reminder`');

Mysql::getInstance()->query('DELETE FROM `storages_failure` WHERE `added`<FROM_UNIXTIME(UNIX_TIMESTAMP(NOW())-604800)');
Mysql::getInstance()->query('OPTIMIZE TABLE `storages_failure`');

Mysql::getInstance()->query('DELETE FROM `played_video` WHERE `playtime`<FROM_UNIXTIME(UNIX_TIMESTAMP(NOW())-2764800)');
Mysql::getInstance()->query('OPTIMIZE TABLE `played_video`');

Mysql::getInstance()->query('DELETE FROM `played_itv` WHERE `playtime`<FROM_UNIXTIME(UNIX_TIMESTAMP(NOW())-2764800)');
Mysql::getInstance()->query('OPTIMIZE TABLE `played_itv`');

Mysql::getInstance()->query('DELETE FROM `played_tv_archive` WHERE `playtime`<FROM_UNIXTIME(UNIX_TIMESTAMP(NOW())-2764800)');
Mysql::getInstance()->query('OPTIMIZE TABLE `played_tv_archive`');

Mysql::getInstance()->query('DELETE FROM `media_claims_log` WHERE `added`<FROM_UNIXTIME(UNIX_TIMESTAMP(NOW())-2592000)');
Mysql::getInstance()->query('OPTIMIZE TABLE `media_claims_log`');