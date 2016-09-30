--

ALTER TABLE `vclub_not_ended` ADD COLUMN `season` int NOT NULL DEFAULT 0;
ALTER TABLE `vclub_not_ended` ADD COLUMN `episode_id` int NOT NULL DEFAULT 0;
ALTER TABLE `vclub_not_ended` ADD COLUMN `season_id` int NOT NULL DEFAULT 0;
ALTER TABLE `vclub_not_ended` ADD COLUMN `file_id` int NOT NULL DEFAULT 0;

-- //@UNDO

ALTER TABLE `vclub_not_ended` DROP COLUMN `season`;
ALTER TABLE `vclub_not_ended` DROP COLUMN `episode_id`;
ALTER TABLE `vclub_not_ended` DROP COLUMN `season_id`;
ALTER TABLE `vclub_not_ended` DROP COLUMN `file_id`;

--