--

ALTER TABLE `users` ADD COLUMN `client_type` VARCHAR(16) NOT NULL DEFAULT '';

-- //@UNDO

ALTER TABLE `users` DROP COLUMN `client_type`;

--