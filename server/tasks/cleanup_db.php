<?php

include "./common.php";

Mysql::getInstance()->query('DELETE FROM `user_log` WHERE `time`<FROM_UNIXTIME(UNIX_TIMESTAMP(NOW())-86400)');
Mysql::getInstance()->query('OPTIMIZE TABLE `user_log`');

Mysql::getInstance()->query('DELETE FROM `events` WHERE `eventtime`<now()');

