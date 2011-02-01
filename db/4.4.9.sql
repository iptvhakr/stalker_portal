CREATE TEMPORARY TABLE `tmp_fav_vclub` AS SELECT * FROM `fav_vclub` GROUP BY `uid`;
DELETE FROM `fav_vclub`;
ALTER TABLE `fav_vclub` ADD UNIQUE INDEX (`uid`);
INSERT INTO `fav_vclub` SELECT * FROM `tmp_fav_vclub`;
DROP TABLE `tmp_fav_vclub`;

CREATE TABLE `tv_reminder`(
    `id` int NOT NULL auto_increment,
    `mac` varchar(64) NOT NULL default '',
    `ch_id` int NOT NULL default 0,
    `tv_program_id` int NOT NULL default 0,
    `fire_time` datetime,
    `added` datetime,
    PRIMARY KEY (`id`),
    INDEX `tv_program_id` (`tv_program_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;