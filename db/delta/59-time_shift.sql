--

ALTER TABLE `users` ADD `ts_enabled` tinyint NOT NULL default 0;
ALTER TABLE `users` ADD `ts_enable_icon` tinyint NOT NULL default 1;
ALTER TABLE `users` ADD `ts_path` varchar(266) NOT NULL default '';
ALTER TABLE `users` ADD `ts_max_length` int NOT NULL default 3600;
ALTER TABLE `users` ADD `ts_buffer_use` varchar(128) NOT NULL default 'cyclic';
ALTER TABLE `users` ADD `ts_action_on_exit` varchar(64) NOT NULL default 'no_save';
ALTER TABLE `users` ADD `ts_delay` varchar(64) NOT NULL default 'on_pause';

ALTER TABLE `itv` ADD `allow_local_timeshift` tinyint NOT NULL default 0;
UPDATE `itv` SET `allow_local_timeshift`=`allow_local_pvr`;

--//@UNDO

ALTER TABLE `users` DROP `ts_enabled`;
ALTER TABLE `users` DROP `ts_enable_icon`;
ALTER TABLE `users` DROP `ts_path`;
ALTER TABLE `users` DROP `ts_max_length`;
ALTER TABLE `users` DROP `ts_buffer_use`;
ALTER TABLE `users` DROP `ts_action_on_exit`;
ALTER TABLE `users` DROP `ts_delay`;

ALTER TABLE `itv` DROP `allow_local_timeshift`;

--