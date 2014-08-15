--
ALTER TABLE `itv` ADD `enable_tv_archive` tinyint default 0;

CREATE TABLE IF NOT EXISTS `tv_archive`(
    `id` int NOT NULL auto_increment,
    `ch_id` int NOT NULL default 0,
    `storage_name` varchar(128) NOT NULL default '',
    `start_time` timestamp default 0,
    `end_time` timestamp default 0,
    PRIMARY KEY (`id`),
    UNIQUE KEY (`ch_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--//@UNDO

ALTER TABLE `itv` DROP `enable_tv_archive`;

--