--

ALTER TABLE `users` ADD COLUMN `client_type` VARCHAR(16) NOT NULL DEFAULT 'STB';

--//@UNDO

ALTER TABLE `users` DROP COLUMN `client_type`;

--