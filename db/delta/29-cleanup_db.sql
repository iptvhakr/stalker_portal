--
DELETE FROM `user_log` WHERE `time`<FROM_UNIXTIME(UNIX_TIMESTAMP(NOW())-86400);
OPTIMIZE TABLE `user_log`;

TRUNCATE `stream_error`;

DELETE FROM `events` WHERE `eventtime`<now();
OPTIMIZE TABLE `events`;

--//@UNDO