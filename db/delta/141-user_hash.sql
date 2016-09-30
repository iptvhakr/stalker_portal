--

ALTER TABLE `users` ADD COLUMN `hw_version_2` VARCHAR(8) NOT NULL DEFAULT '';
ALTER TABLE `users` ADD COLUMN `blocked` TINYINT NOT NULL DEFAULT 0;

-- //@UNDO

ALTER TABLE `users` DROP COLUMN `hw_version_2`;
ALTER TABLE `users` DROP COLUMN `blocked`;
--