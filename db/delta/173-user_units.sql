--

ALTER TABLE `users` ADD COLUMN `units` ENUM('metric', 'imperial') NOT NULL;

-- //@UNDO

ALTER TABLE `users` DROP COLUMN `units`;

--