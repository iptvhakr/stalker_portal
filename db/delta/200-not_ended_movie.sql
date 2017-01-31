--

ALTER TABLE `user_played_movies` ADD COLUMN `not_ended` TINYINT NOT NULL DEFAULT 0 AFTER `watched`;

--//@UNDO

ALTER TABLE `user_played_movies` DROP COLUMN `not_ended`;

--