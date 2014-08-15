--

ALTER TABLE `vclub_ad` ADD `status` int NOT NULL DEFAULT 1;

CREATE TABLE IF NOT EXISTS `vclub_ad_deny_category`(
  `id` int NOT NULL auto_increment,
  `ad_id` int NOT NULL default 0,
  `category_id` int NOT NULL default 0,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;

--//@UNDO

ALTER TABLE `vclub_ad` DROP `status`;

DROP TABLE `vclub_ad_deny_category`;

--