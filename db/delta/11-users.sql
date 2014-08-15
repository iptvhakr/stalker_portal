--
ALTER TABLE `users` ADD `fname` varchar(64) NOT NULL default '';
ALTER TABLE `storages` ADD `wowza_server` tinyint default 0;
ALTER TABLE `storages` ADD `archive_stream_server` varchar(128) NOT NULL default '';
ALTER TABLE `itv` DROP `quality`;
ALTER TABLE `itv` ADD `cmd_1` varchar(128) NOT NULL default '';
ALTER TABLE `itv` ADD `cmd_2` varchar(128) NOT NULL default '';
ALTER TABLE `itv` ADD `cmd_3` varchar(128) NOT NULL default '';
ALTER TABLE `stream_error` ADD `event` tinyint unsigned default 0;
--//@UNDO

ALTER TABLE `users` DROP `fname`;
ALTER TABLE `storages` DROP `wowza_server`;
ALTER TABLE `storages` DROP `archive_stream_server`;
ALTER TABLE `itv` ADD `quality` varchar(16) default 'high';
ALTER TABLE `itv` DROP `cmd_1`;
ALTER TABLE `itv` DROP `cmd_2`;
ALTER TABLE `itv` DROP `cmd_3`;
ALTER TABLE `stream_error` DROP `event`;

--