--
CREATE TABLE IF NOT EXISTS `storages_failure`(
    `id` int NOT NULL auto_increment,
    `storage_id` int NOT NULL default 0,
    `description` text not null,
    `added` timestamp not null,
    PRIMARY KEY (`id`),
    INDEX storage(`storage_id`, `added`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--//@UNDO