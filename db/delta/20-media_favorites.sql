--
CREATE TABLE IF NOT EXISTS `media_favorites`(
    `id` int NOT NULL auto_increment,
    `uid` int NOT NULL default 0,
    `favorites` text not null,
    `modified` timestamp not null,
    PRIMARY KEY (`id`),
    UNIQUE KEY (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
--//@UNDO