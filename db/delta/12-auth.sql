--
ALTER TABLE `users` ADD `login` varchar(64) NOT NULL default '';
ALTER TABLE `users` ADD `password` varchar(64) NOT NULL default '';
ALTER TABLE `users` DROP INDEX `mac`;
ALTER TABLE `users` ADD KEY `mac` (`mac`);

CREATE TABLE IF NOT EXISTS `user_modules`(
    `id` int NOT NULL auto_increment,
    `uid` int NOT NULL default 0,
    `restricted` text NOT NULL,
    `disabled` text NOT NULL,
    `changed` timestamp NOT NULL,
    UNIQUE KEY (`uid`),
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `video_on_tasks`(
    `id` int NOT NULL auto_increment,
    `video_id` int NOT NULL default 0,
    `date_on`  date,
    `added` timestamp NOT NULL,
    UNIQUE KEY (`video_id`),
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
--//@UNDO

ALTER TABLE `users` DROP `login`;
ALTER TABLE `users` DROP `password`;

--