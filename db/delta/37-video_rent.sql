--

ALTER TABLE `services_package` ADD `rent_duration` int NOT NULL default 0;

CREATE TABLE IF NOT EXISTS `video_rent`(
  `id` int NOT NULL auto_increment,
  `uid` int NOT NULL default 0,
  `video_id` int NOT NULL default 0,
  `price` varchar(32) NOT NULL default '',
  `rent_history_id` int NOT NULL default 0,
  `rent_date` timestamp default 0,
  `rent_end_date` timestamp default 0,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `video_rent_history`(
  `id` int NOT NULL auto_increment,
  `uid` int NOT NULL default 0,
  `video_id` int NOT NULL default 0,
  `price` varchar(32) NOT NULL default '',
  `rent_date` timestamp default 0,
  `rent_end_date` timestamp default 0,
  `start_watching_date` timestamp default 0,
  `watched` tinyint default 0,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `image_update_settings` ADD `stb_type` VARCHAR(64) NOT NULL DEFAULT '';

--//@UNDO

ALTER TABLE `services_package` DROP `rent_duration`;

DROP TABLE `video_rent`;
DROP TABLE `video_rent_history`;

ALTER TABLE `image_update_settings` DROP `stb_type`;

--