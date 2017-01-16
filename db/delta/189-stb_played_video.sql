--

RENAME TABLE `stb_played_video` TO `user_played_movies`;

ALTER TABLE `user_played_movies` ADD COLUMN `file_id` INT NOT NULL DEFAULT 0 AFTER `video_id`;
ALTER TABLE `user_played_movies` ADD COLUMN `episode_id` INT NOT NULL DEFAULT 0 AFTER `video_id`;
ALTER TABLE `user_played_movies` ADD COLUMN `season_id` INT NOT NULL DEFAULT 0 AFTER `video_id`;
ALTER TABLE `user_played_movies` ADD COLUMN `watched` TINYINT NOT NULL DEFAULT 0 AFTER `file_id`;
ALTER TABLE `user_played_movies` ADD COLUMN `watched_time` SMALLINT NOT NULL DEFAULT 0 AFTER `watched`;

--//@UNDO

RENAME TABLE `user_played_movies` TO `stb_played_video`;

ALTER TABLE `stb_played_video` DROP COLUMN `file_id`;
ALTER TABLE `stb_played_video` DROP COLUMN `episode_id`;
ALTER TABLE `stb_played_video` DROP COLUMN `season_id`;
ALTER TABLE `stb_played_video` DROP COLUMN `watched`;
ALTER TABLE `stb_played_video` DROP COLUMN `watched_time`;

--