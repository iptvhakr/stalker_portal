--

ALTER TABLE `itv` ADD `modified` DATETIME;

CREATE TABLE IF NOT EXISTS `vclub_ad`(
  `id` int NOT NULL auto_increment,
  `title` varchar(128) NOT NULL default '',
  `url` varchar(255) NOT NULL default '',
  `must_watch` varchar(128) NOT NULL default 'all',
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `vclub_ads_log`(
  `id` int NOT NULL auto_increment,
  `title` varchar(128) NOT NULL default '',
  `vclub_ad_id` int NOT NULL default 0,
  `uid` int NOT NULL default 0,
  `watched_percent` int NOT NULL default 0,
  `watched_time` int NOT NULL default 0,
  `watch_complete` tinyint default 0,
  `added` datetime NOT NULL,
  INDEX `vclub_ad_id` (`vclub_ad_id`, `added`),
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;

DELETE FROM `cities` WHERE country_id=8;

--//@UNDO

ALTER TABLE `itv` DROP `modified`;

DROP TABLE `vclub_ad`;
DROP TABLE `vclub_ads_log`;

--