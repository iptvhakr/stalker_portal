--
ALTER TABLE `access_tokens` ADD `secret_key` varchar(128) NOT NULL default '';
ALTER TABLE `access_tokens` ADD `time_delta` varchar(128) NOT NULL default '';
ALTER TABLE `access_tokens` ADD `started` timestamp default 0;

CREATE TABLE IF NOT EXISTS `developer_api_key`(
    `id` int NOT NULL auto_increment,
    `uid` int NOT NULL default 0,
    `api_key` varchar(128) NOT NULL default '',
    `comment` text not null,
    `expires` timestamp default 0,
    PRIMARY KEY (`id`),
    UNIQUE KEY (`api_key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
--//@UNDO

ALTER TABLE `access_tokens` DROP `secret_key`;
ALTER TABLE `access_tokens` DROP `time_delta`;
ALTER TABLE `access_tokens` DROP `started`;

--