CREATE TEMPORARY TABLE `tmp_fav_vclub` AS SELECT * FROM `fav_vclub` GROUP BY `uid`;
DELETE FROM `fav_vclub`;
ALTER TABLE `fav_vclub` ADD UNIQUE INDEX (`uid`);
INSERT INTO `fav_vclub` SELECT * FROM `tmp_fav_vclub`;
DROP TABLE `tmp_fav_vclub`;

ALTER TABLE `tv_reminder` ADD INDEX `tv_program_id` (`tv_program_id`);
