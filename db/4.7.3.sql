ALTER TABLE `screenshots` DROP `path`;

CREATE TABLE IF NOT EXISTS `changelog` (
  `change_number` BIGINT NOT NULL,
  `delta_set` VARCHAR(10) NOT NULL,
  `start_dt` TIMESTAMP NOT NULL,
  `complete_dt` TIMESTAMP NULL,
  `applied_by` VARCHAR(100) NOT NULL,
  `description` VARCHAR(500) NOT NULL
);

INSERT INTO `changelog` VALUES (1,'Main','2011-04-19 10:41:11','2011-04-19 10:41:11','dbdeploy','1-initial_schema.sql'),
(2,'Main','2011-04-19 17:41:11','2011-04-19 17:41:11','dbdeploy','2-cities.sql');

ALTER TABLE `rec_files` ADD `storage_name` varchar(128) NOT NULL DEFAULT '';
ALTER TABLE `rec_files` ADD `file_name` varchar(128) NOT NULL DEFAULT '';
ALTER TABLE `storages` ADD `for_records` tinyint DEFAULT 0;

ALTER TABLE `itv` ADD `mc_cmd` varchar(128) NOT NULL default '';

CREATE TABLE IF NOT EXISTS `pvr`(
    `id` int NOT NULL auto_increment,
    `ch_id` int NOT NULL default 0,
    `t_start` timestamp not null,
    `t_stop`  timestamp default 0,
    `atrack`  varchar(32) NOT NULL default '',
    `vtrack`  varchar(32) NOT NULL default '',
    `length` int NOT NULL default 0,
    `ended`  tinyint default 0, /* 0-not ended, 1-ended */
    `uid` int NOT NULL default 0,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `rec_files` MODIFY `t_start` timestamp default 0;
ALTER TABLE `users_rec` MODIFY `t_start` timestamp default 0;
ALTER TABLE `pvr` MODIFY `t_start` timestamp default 0;

ALTER TABLE `users_rec` ADD `program` varchar(64) NOT NULL default '';
ALTER TABLE `users_rec` ADD `program_id` int NOT NULL default 0;

ALTER TABLE `users_rec` ADD `started` tinyint default 0;

ALTER TABLE `users` ADD `playback_buffer_bytes` int NOT NULL default 0;
ALTER TABLE `users` ADD `playback_buffer_size` int NOT NULL default 0;
ALTER TABLE `users` ADD `audio_out` int NOT NULL default 0;