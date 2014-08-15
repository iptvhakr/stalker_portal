--
ALTER TABLE `itv` ADD `logo` varchar(128) NOT NULL default '';

ALTER TABLE `administrators` ADD `debug_key` varchar(128) NOT NULL default '';

UPDATE `administrators` SET `debug_key`=md5(rand()) WHERE `access`=0;

CREATE TABLE IF NOT EXISTS `user_downloads`(
    `id` int NOT NULL auto_increment,
    `uid` int NOT NULL default 0,
    `downloads` text not null,
    `modified` timestamp not null,
    PRIMARY KEY (`id`),
    UNIQUE KEY (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
--//@UNDO

ALTER TABLE `itv` DROP `logo`;

ALTER TABLE `administrators` DROP `debug_key`;

--